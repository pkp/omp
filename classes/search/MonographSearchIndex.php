<?php

/**
 * @file classes/search/MonographSearchIndex.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographSearchIndex
 *
 * @ingroup search
 *
 * @brief Class to add content to the monograph search index.
 */

namespace APP\search;

use APP\core\Application;
use APP\facades\Repo;
use APP\press\Press;
use APP\press\PressDAO;
use APP\submission\Submission;
use Exception;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\jobs\submissions\UpdateSubmissionSearchJob;
use PKP\plugins\Hook;
use PKP\search\SearchFileParser;
use PKP\search\SubmissionSearch;
use PKP\search\SubmissionSearchIndex;
use PKP\submissionFile\SubmissionFile;
use Throwable;

class MonographSearchIndex extends SubmissionSearchIndex
{
    private const MINIMUM_DATA_LENGTH = 80 * 1024;

    /**
     * Index a block of text for an object.
     *
     * @param int $objectId
     * @param string|array $text
     */
    public function indexObjectKeywords($objectId, $text)
    {
        $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /** @var MonographSearchDAO $searchDao */
        $keywords = $this->filterKeywords($text);
        $searchDao->insertObjectKeywords($objectId, $keywords);
    }

    /**
     * Add a block of text to the search index.
     *
     * @param int $monographId
     * @param int $type
     * @param string|string[] $text
     * @param int $assocId optional
     */
    public function updateTextIndex($monographId, $type, $text, $assocId = null)
    {
        $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /** @var MonographSearchDAO $searchDao */
        $objectId = $searchDao->insertObject($monographId, $type, $assocId);
        $this->indexObjectKeywords($objectId, $text);
    }

    /**
     * Add a file to the search index.
     *
     * @param int $monographId
     * @param int $type
     * @param SubmissionFile $submissionFile
     *
     * @hook MonographSearchIndex::submissionFileChanged [[$monographId, $type, $submissionFile->getId()]]
     */
    public function submissionFileChanged($monographId, $type, $submissionFile)
    {
        if (Hook::ABORT === Hook::call('MonographSearchIndex::submissionFileChanged', [$monographId, $type, $submissionFile->getId()])) {
            return;
        }

        // If no search plug-in is activated then fall back to the default database search implementation.
        $parser = SearchFileParser::fromFile($submissionFile);
        if (!$parser) {
            error_log("Skipped indexation: No suitable parser for the submission file \"{$submissionFile->getData('path')}\"");
            return;
        }
        try {
            $parser->open();
            try {
                $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /** @var MonographSearchDAO $searchDao */
                $objectId = $searchDao->insertObject($monographId, $type, $submissionFile->getId());
                do {
                    for ($buffer = ''; ($chunk = $parser->read()) !== false && strlen($buffer .= $chunk) < static::MINIMUM_DATA_LENGTH;);
                    if (strlen($buffer)) {
                        $this->indexObjectKeywords($objectId, $buffer);
                    }
                } while ($chunk !== false);
            } finally {
                $parser->close();
            }
        } catch (Throwable $e) {
            throw new Exception("Indexation failed for the file: \"{$submissionFile->getData('path')}\"", 0, $e);
        }
    }

    /**
     * Delete keywords from the search index.
     *
     * @param int $monographId
     * @param int $type optional
     * @param int $assocId optional
     */
    public function deleteTextIndex($monographId, $type = null, $assocId = null)
    {
        $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /** @var MonographSearchDAO $searchDao */
        return $searchDao->deleteSubmissionKeywords($monographId, $type, $assocId);
    }

    /**
     * Index monograph metadata.
     *
     * @param Submission $submission
     *
     * @hook MonographSearchIndex::submissionMetadataChanged [[$submission]]
     * @hook MonographSearchIndex::monographMetadataChanged [[$submission]]
     */
    public function submissionMetadataChanged($submission)
    {
        // Check whether a search plug-in jumps in.
        if (Hook::ABORT === Hook::call('MonographSearchIndex::monographMetadataChanged', [$submission])) {
            return;
        }

        $publication = $submission->getCurrentPublication();

        // Build author keywords
        $authorText = [];
        foreach ($publication->getData('authors') as $author) {
            $authorText = array_merge(
                $authorText,
                array_values((array) $author->getData('givenName')),
                array_values((array) $author->getData('familyName')),
                array_values((array) $author->getData('preferredPublicName')),
                array_values(array_map(strip_tags(...), (array) $author->getData('affiliation'))),
                array_values(array_map(strip_tags(...), (array) $author->getData('biography')))
            );
        }

        // Update search index
        $submissionId = $submission->getId();
        $this->updateTextIndex($submissionId, SubmissionSearch::SUBMISSION_SEARCH_AUTHOR, $authorText);
        $this->updateTextIndex($submissionId, SubmissionSearch::SUBMISSION_SEARCH_TITLE, $publication->getData('title'));
        $this->updateTextIndex($submissionId, SubmissionSearch::SUBMISSION_SEARCH_ABSTRACT, $publication->getData('abstract'));

        $this->updateTextIndex($submissionId, SubmissionSearch::SUBMISSION_SEARCH_SUBJECT, (array) $this->_flattenLocalizedArray($publication->getData('subjects')));
        $this->updateTextIndex($submissionId, SubmissionSearch::SUBMISSION_SEARCH_KEYWORD, (array) $this->_flattenLocalizedArray($publication->getData('keywords')));
        $this->updateTextIndex($submissionId, SubmissionSearch::SUBMISSION_SEARCH_DISCIPLINE, (array) $this->_flattenLocalizedArray($publication->getData('disciplines')));
        $this->updateTextIndex($submissionId, SubmissionSearch::SUBMISSION_SEARCH_TYPE, (array) $publication->getData('type'));
        $this->updateTextIndex($submissionId, SubmissionSearch::SUBMISSION_SEARCH_COVERAGE, (array) $publication->getData('coverage'));
        // FIXME Index sponsors too?
    }

    /**
     * Index all monograph files (galley files).
     *
     * @param Submission $monograph
     *
     * @hook MonographSearchIndex::submissionFilesChanged [[$monograph]]
     */
    public function submissionFilesChanged($monograph)
    {
        // If a search plug-in is activated then skip the default database search implementation.
        if (Hook::ABORT === Hook::call('MonographSearchIndex::submissionFilesChanged', [$monograph])) {
            return;
        }

        // Index galley files
        $submissionFiles = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$monograph->getId()])
            ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_PROOF])
            ->getMany();

        $exceptions = [];
        foreach ($submissionFiles as $submissionFile) {
            try {
                $this->submissionFileChanged($monograph->getId(), SubmissionSearch::SUBMISSION_SEARCH_GALLEY_FILE, $submissionFile);
            } catch (Throwable $e) {
                $exceptions[] = $e;
            }
            $dependentFiles = Repo::submissionFile()->getCollector()
                ->filterByAssoc(
                    Application::ASSOC_TYPE_SUBMISSION_FILE,
                    [$submissionFile->getId()]
                )
                ->filterBySubmissionIds([$monograph->getId()])
                ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_DEPENDENT])
                ->includeDependentFiles()
                ->getMany();

            foreach ($dependentFiles as $dependentFile) {
                try {
                    $this->submissionFileChanged($monograph->getId(), SubmissionSearch::SUBMISSION_SEARCH_SUPPLEMENTARY_FILE, $dependentFile);
                } catch (Throwable $e) {
                    $exceptions[] = $e;
                }
            }
        }
        if (count($exceptions)) {
            $errorMessage = implode("\n\n", $exceptions);
            throw new Exception("The following errors happened while indexing the submission ID {$monograph->getId()}:\n{$errorMessage}");
        }
    }

    /**
     * @copydoc SubmissionSearchIndex::clearSubmissionFiles()
     */
    public function clearSubmissionFiles($submission)
    {
        $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /** @var MonographSearchDAO $searchDao */
        $searchDao->deleteSubmissionKeywords($submission->getId(), SubmissionSearch::SUBMISSION_SEARCH_GALLEY_FILE);
    }

    /**
     * Signal to the indexing back-end that a file was deleted.
     *
     * @see MonographSearchIndex::submissionMetadataChanged() above for more
     * comments.
     *
     * @param int $type optional
     * @param int $assocId optional
     *
     * @hook MonographSearchIndex::submissionFileDeleted [[$monographId, $type, $assocId]]
     */
    public function submissionFileDeleted($monographId, $type = null, $assocId = null)
    {
        // If a search plug-in is activated then skip the default database search implementation.
        if (Hook::ABORT === Hook::call('MonographSearchIndex::submissionFileDeleted', [$monographId, $type, $assocId])) {
            return;
        }

        $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /** @var MonographSearchDAO $searchDao */
        return $searchDao->deleteSubmissionKeywords($monographId, $type, $assocId);
    }

    /**
     * @copydoc SubmissionSearchIndex::submissionChangesFinished()
     *
     * @hook MonographSearchIndex::monographChangesFinished []
     */
    public function submissionChangesFinished()
    {
        // Trigger a hook to let the indexing back-end know that
        // the index may be updated.
        Hook::call(
            'MonographSearchIndex::monographChangesFinished'
        );

        // The default indexing back-end works completely synchronously
        // and will therefore not do anything here.
    }

    /**
     * @copydoc SubmissionSearchIndex::submissionChangesFinished()
     */
    public function monographChangesFinished()
    {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated call to monographChangesFinished. Use submissionChangesFinished instead.');
        }
        $this->submissionChangesFinished();
    }


    /**
     * Rebuild the search index for all presses.
     *
     * @param bool $log Whether or not to log progress to the console.
     * @param \APP\press\Press $press If given the user wishes to
     *  re-index only one press. Not all search implementations
     *  may be able to do so. Most notably: The default SQL
     *  implementation does not support press-specific re-indexing
     *  as index data is not partitioned by press.
     * @param array $switches Optional index administration switches.
     *
     * @hook MonographSearchIndex::rebuildIndex [[$log]]
     */
    public function rebuildIndex($log = false, $press = null, $switches = [])
    {
        // If a search plug-in is activated then skip the default database search implementation.
        if (Hook::ABORT === Hook::call('MonographSearchIndex::rebuildIndex', [$log, $press, $switches])) {
            return;
        }

        // Check that no press was given as we do not support press-specific re-indexing.
        if ($press instanceof Press) {
            exit(__('search.cli.rebuildIndex.indexingByPressNotSupported') . "\n");
        }

        // Clear index
        if ($log) {
            echo __('search.cli.rebuildIndex.clearingIndex') . ' ... ';
        }
        $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /** @var MonographSearchDAO $searchDao */
        $searchDao->clearIndex();
        if ($log) {
            echo __('search.cli.rebuildIndex.done') . "\n";
        }

        // Build index
        $pressDao = DAORegistry::getDAO('PressDAO'); /** @var PressDAO $pressDao */

        $presses = $pressDao->getAll()->toIterator();
        foreach ($presses as $press) {
            $numIndexed = 0;

            if ($log) {
                echo __('search.cli.rebuildIndex.indexing', ['pressName' => $press->getLocalizedName()]) . ' ... ';
            }

            $submissions = Repo::submission()
                ->getCollector()
                ->filterByContextIds([$press->getId()])
                ->getMany();

            foreach ($submissions as $submission) {
                dispatch(new UpdateSubmissionSearchJob($submission->getId()));
                ++$numIndexed;
            }

            if ($log) {
                echo __('search.cli.rebuildIndex.result', ['numIndexed' => $numIndexed]) . "\n";
            }
        }
    }

    /**
     * Flattens array of localized fields to a single, non-associative array of items
     *
     * @param array $arrayWithLocales Array of localized fields
     *
     * @return array
     */
    protected function _flattenLocalizedArray($arrayWithLocales)
    {
        $flattenedArray = [];

        foreach ($arrayWithLocales as $localeArray) {
            $names = array_map(
                static fn ($item) => $item['name'],
                $localeArray
            );

            $flattenedArray = array_merge($flattenedArray, $names);
        }

        return $flattenedArray;
    }
}
