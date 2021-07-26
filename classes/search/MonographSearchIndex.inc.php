<?php

/**
 * @file classes/search/MonographSearchIndex.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographSearchIndex
 * @ingroup search
 *
 * @brief Class to add content to the monograph search index.
 */

namespace APP\search;

use APP\core\Services;
use APP\facades\Repo;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\plugins\HookRegistry;
use PKP\search\SearchFileParser;
use PKP\search\SubmissionSearch;

use PKP\search\SubmissionSearchIndex;
use PKP\submissionFile\SubmissionFile;

class MonographSearchIndex extends SubmissionSearchIndex
{
    /**
     * Index a block of text for an object.
     *
     * @param $objectId int
     * @param $text string|array
     */
    public function indexObjectKeywords($objectId, $text)
    {
        $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
        $keywords = $this->filterKeywords($text);
        $searchDao->insertObjectKeywords($objectId, $keywords);
    }

    /**
     * Add a block of text to the search index.
     *
     * @param $monographId int
     * @param $type int
     * @param $text string
     * @param $assocId int optional
     */
    public function updateTextIndex($monographId, $type, $text, $assocId = null)
    {
        $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
        $objectId = $searchDao->insertObject($monographId, $type, $assocId);
        $this->indexObjectKeywords($objectId, $text);
    }

    /**
     * Add a file to the search index.
     *
     * @param $monographId int
     * @param $type int
     * @param $submissionFileId int
     */
    public function updateFileIndex($monographId, $type, $submissionFileId)
    {
        $submisssionFile = Services::get('submissionFile')->get($submissionFileId);

        if (isset($submisssionFile)) {
            $parser = SearchFileParser::fromFile($submisssionFile);
        }

        if (isset($parser)) {
            if ($parser->open()) {
                $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
                $objectId = $searchDao->insertObject($monographId, $type, $submissionFileId);

                while (($text = $parser->read()) !== false) {
                    $this->indexObjectKeywords($objectId, $text);
                }
                $parser->close();
            } else {
                // cannot open parser; unsupported format?
            }
        }
    }

    /**
     * Delete keywords from the search index.
     *
     * @param $monographId int
     * @param $type int optional
     * @param $assocId int optional
     */
    public function deleteTextIndex($monographId, $type = null, $assocId = null)
    {
        $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
        return $searchDao->deleteSubmissionKeywords($monographId, $type, $assocId);
    }

    /**
     * Index monograph metadata.
     *
     * @param Submission $submission
     */
    public function submissionMetadataChanged($submission)
    {
        $publication = $submission->getCurrentPublication();

        // Build author keywords
        $authorText = [];
        foreach ($publication->getData('authors') as $author) {
            $authorText = array_merge(
                $authorText,
                array_values((array) $author->getData('givenName')),
                array_values((array) $author->getData('familyName')),
                array_values((array) $author->getData('preferredPublicName')),
                array_values(array_map('strip_tags', (array) $author->getData('affiliation'))),
                array_values(array_map('strip_tags', (array) $author->getData('biography')))
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
     * @param $monograph Monograph
     */
    public function submissionFilesChanged($monograph)
    {
        // Index galley files
        import('lib.pkp.classes.submissionFile.SubmissionFile'); // Constants
        import('classes.search.MonographSearch'); // Constants
        $submissionFiles = Services::get('submissionFile')->getMany([
            'submissionIds' => [$monograph->getId()],
            'fileStages' => [SubmissionFile::SUBMISSION_FILE_PROOF],
        ]);

        foreach ($submissionFiles as $submissionFile) {
            $this->updateFileIndex($monograph->getId(), SubmissionSearch::SUBMISSION_SEARCH_GALLEY_FILE, $submissionFile->getId());
        }
    }

    /**
     * @copydoc SubmissionSearchIndex::clearSubmissionFiles()
     */
    public function clearSubmissionFiles($submission)
    {
        $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
        $searchDao->deleteSubmissionKeywords($submission->getId(), SubmissionSearch::SUBMISSION_SEARCH_GALLEY_FILE);
    }

    /**
     * @copydoc SubmissionSearchIndex::submissionChangesFinished()
     */
    public function submissionChangesFinished()
    {
        // Trigger a hook to let the indexing back-end know that
        // the index may be updated.
        HookRegistry::call(
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
     * @param $log boolean Whether or not to log progress to the console.
     */
    public function rebuildIndex($log = false)
    {
        // Clear index
        if ($log) {
            echo 'Clearing index ... ';
        }
        $searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
        $searchDao->clearIndex();
        if ($log) {
            echo "done\n";
        }

        // Build index
        $pressDao = DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */

        $presses = $pressDao->getAll();
        while ($press = $presses->next()) {
            $numIndexed = 0;

            if ($log) {
                echo 'Indexing "', $press->getLocalizedName(), '" ... ';
            }

            $monographs = Repo::submission()->getMany(
                Repo::submission()
                    ->getCollector()
                    ->filterByContextIds([$press->getId()])
            );
            while (!$monographs->eof()) {
                $monograph = $monographs->next();
                if ($monograph->getDatePublished()) {
                    $this->submissionMetadataChanged($monograph);
                    $this->submissionFilesChanged($monograph);
                    $numIndexed++;
                }
            }
            $this->submissionChangesFinished();

            if ($log) {
                echo $numIndexed, " monographs indexed\n";
            }
        }
    }

    /**
     * Flattens array of localized fields to a single, non-associative array of items
     *
     * @param $arrayWithLocales array Array of localized fields
     *
     * @return array
     */
    protected function _flattenLocalizedArray($arrayWithLocales)
    {
        $flattenedArray = [];
        foreach ($arrayWithLocales as $localeArray) {
            $flattenedArray = array_merge(
                $flattenedArray,
                $localeArray
            );
        }
        return $flattenedArray;
    }
}
