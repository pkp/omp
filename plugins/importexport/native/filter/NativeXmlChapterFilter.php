<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlChapterFilter.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlChapterFilter
 * @brief Base class that converts a Native XML document to a set of authors
 */

namespace APP\plugins\importexport\native\filter;

use APP\core\Application;
use APP\facades\Repo;
use PKP\db\DAORegistry;
use PKP\plugins\PluginRegistry;

class NativeXmlChapterFilter extends \PKP\plugins\importexport\native\filter\NativeImportFilter
{
    /**
     * Constructor
     *
     * @param FilterGroup $filterGroup
     */
    public function __construct($filterGroup)
    {
        $this->setDisplayName('Native XML Chapter import');
        parent::__construct($filterGroup);
    }

    //
    // Implement template methods from NativeImportFilter
    //
    /**
     * Return the plural element name
     *
     * @return string
     */
    public function getPluralElementName()
    {
        return 'chapters';
    }

    /**
     * Get the singular element name
     *
     * @return string
     */
    public function getSingularElementName()
    {
        return 'chapter';
    }

    //
    // Implement template methods from PersistableFilter
    //
    /**
     * @copydoc PersistableFilter::getClassName()
     */
    public function getClassName()
    {
        return (string) self::class;
    }


    /**
     * Handle a chapter element
     *
     * @param DOMElement $node
     *
     * @return Chapter
     */
    public function handleElement($node)
    {
        $deployment = $this->getDeployment();
        $context = $deployment->getContext();

        $publication = $deployment->getPublication();
        assert($publication instanceof \APP\publication\Publication);

        // Create the data object
        $chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var ChapterDAO $chapterDao */

        $chapter = $chapterDao->newDataObject();

        $chapter->setData('publicationId', $publication->getId());
        $chapter->setSequence($node->getAttribute('seq'));

        $chapterId = $chapterDao->insertChapter($chapter);
        $chapter->setData('id', $chapterId);

        // Handle metadata in subelements
        for ($n = $node->firstChild; $n !== null; $n = $n->nextSibling) {
            if ($n instanceof \DOMElement) {
                switch ($n->tagName) {
                    case 'id':
                        $this->parseIdentifier($n, $chapter);
                        break;
                    case 'title':
                        $locale = $n->getAttribute('locale');
                        if (empty($locale)) {
                            $locale = $context->getLocale();
                        }
                        $chapter->setData('title', $n->textContent, $locale);
                        break;
                    case 'abstract':
                        $locale = $n->getAttribute('locale');
                        if (empty($locale)) {
                            $locale = $context->getLocale();
                        }
                        $chapter->setData('abstract', $n->textContent, $locale);
                        break;
                    case 'subtitle':
                        $locale = $n->getAttribute('locale');
                        if (empty($locale)) {
                            $locale = $context->getLocale();
                        }
                        $chapter->setData('subtitle', $n->textContent, $locale);
                        break;
                    case 'pages':
                        $chapter->setData('pages', $n->textContent);
                        break;
                    case 'chapterAuthor':
                        $this->parseAuthor($n, $chapter);
                        break;
                    case 'submission_file_ref':
                        $this->parseSubmissionFileRef($n, $chapter);
                        break;
                }
            }
        }

        $chapterDao->updateObject($chapter);

        return $chapter;
    }

    /**
     * Parse an author and add it to the chapter.
     *
     * @param \DOMElement $n
     * @param \APP\monograph\Chapter $chapter
     */
    public function parseAuthor($n, $chapter)
    {
        $deployment = $this->getDeployment();

        $authorId = $deployment->getAuthorDBId($n->getAttribute('author_id'));
        if (!$authorId) {
            $deployment->addError(Application::ASSOC_TYPE_CHAPTER, $chapter->getId(), 'Author with ID "' . $n->getAttribute('author_id') . '" was not found');
        }
        $seq = $n->getAttribute('seq');

        Repo::author()->addToChapter($authorId, $chapter->getId(), false, $seq);
    }

    /**
     * Parse an author and add it to the chapter.
     *
     * @param \DOMElement $n
     * @param \APP\monograph\Chapter $chapter
     */
    public function parseSubmissionFileRef($n, $chapter)
    {
        $deployment = $this->getDeployment();

        $fileId = $n->getAttribute('id');

        $sourceFileId = $deployment->getSubmissionFileDBId($fileId);
        if (!$sourceFileId) {
            return;
        }

        $submissionFile = Repo::submissionFile()->get($sourceFileId);

        if (!$submissionFile) {
            return;
        }

        Repo::submissionFile()
            ->dao
            ->updateChapterFiles(
                [$submissionFile->getId()],
                $chapter->getId()
            );
    }

    /**
     * Parse an identifier node and set up the chapter object accordingly
     *
     * @param \DOMElement $element
     */
    public function parseIdentifier($element, $chapter)
    {
        $deployment = $this->getDeployment();
        $context = $deployment->getContext();
        $advice = $element->getAttribute('advice');
        switch ($element->getAttribute('type')) {
            case 'internal':
                break;
            case 'public':
                if ($advice == 'update') {
                    $chapter->setData('pub-id::publisher-id', $element->textContent);
                }
                break;
            default:
                if ($advice == 'update') {
                    if ($element->getAttribute('type') == 'doi') {
                        $doiFound = Repo::doi()->getCollector()->filterByIdentifier($element->textContent)->getMany()->first();
                        if ($doiFound) {
                            $chapter->setData('doiId', $doiFound->getId());
                        } else {
                            $newDoiObject = Repo::doi()->newDataObject(
                                [
                                    'doi' => $element->textContent,
                                    'contextId' => $context->getId()
                                ]
                            );
                            $doiId = Repo::doi()->add($newDoiObject);
                            $chapter->setData('doiId', $doiId);
                        }
                    } else {
                        PluginRegistry::loadCategory('pubIds', true, $context->getId());
                        $chapter->setData('pub-id::' . $element->getAttribute('type'), $element->textContent);
                    }
                }
        }
    }
}
