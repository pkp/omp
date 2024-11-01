<?php

/**
 * @file plugins/importexport/native/filter/ChapterNativeXmlFilter.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterNativeXmlFilter
 *
 * @brief Base class that converts a set of authors to a Native XML document
 */

namespace APP\plugins\importexport\native\filter;

use APP\facades\Repo;
use DOMDocument;
use PKP\filter\FilterGroup;
use PKP\plugins\PluginRegistry;
use PKP\submissionFile\SubmissionFile;

class ChapterNativeXmlFilter extends \PKP\plugins\importexport\native\filter\NativeExportFilter
{
    /**
     * Constructor
     *
     * @param FilterGroup $filterGroup
     */
    public function __construct($filterGroup)
    {
        $this->setDisplayName('Native XML chapter export');
        parent::__construct($filterGroup);
    }

    //
    // Implement template methods from Filter
    //
    /**
     * @see Filter::process()
     *
     * @param \APP\monograph\Chapter[] $chapters
     *
     * @return \DOMDocument
     */
    public function &process(&$chapters)
    {
        // Create the XML document
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $deployment = $this->getDeployment();

        // Multiple authors; wrap in a <chapters> element
        $rootNode = $doc->createElementNS($deployment->getNamespace(), 'chapters');
        foreach ($chapters as $chapter) {
            $rootNode->appendChild($this->createChapterNode($doc, $chapter));
        }
        $doc->appendChild($rootNode);
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $rootNode->setAttribute('xsi:schemaLocation', $deployment->getNamespace() . ' ' . $deployment->getSchemaFilename());

        return $doc;
    }

    //
    // PKPAuthor conversion functions
    //
    /**
     * Create and return an author node.
     *
     * @param \DOMDocument $doc
     * @param \APP\monograph\Chapter $chapter
     *
     * @return \DOMElement
     */
    public function createChapterNode($doc, $chapter)
    {
        $deployment = $this->getDeployment();
        $context = $deployment->getContext();

        $publication = $deployment->getPublication();

        // Create the entity node
        $entityNode = $doc->createElementNS($deployment->getNamespace(), 'chapter');
        $entityNode->setAttribute('seq', $chapter->getSequence());
        $entityNode->setAttribute('id', $chapter->getId());

        $this->addIdentifiers($doc, $entityNode, $chapter);

        // Add metadata
        $this->createLocalizedNodes($doc, $entityNode, 'title', $chapter->getData('title'));
        $this->createLocalizedNodes($doc, $entityNode, 'abstract', $chapter->getData('abstract'));
        $this->createLocalizedNodes($doc, $entityNode, 'subtitle', $chapter->getData('subtitle'));

        $entityNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'pages', $chapter->getData('pages')));

        // Add authors
        $chapterAuthors = Repo::author()->getCollector()
            ->filterByChapterId($chapter->getId())
            ->filterByPublicationIds([$chapter->getData('publicationId')])
            ->getMany();

        foreach ($chapterAuthors as $chapterAuthor) {
            $entityNode->appendChild($this->createChapterAuthorNode($doc, $chapterAuthor));
        }

        $submissionFiles = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$publication->getData('submissionId')])
            ->getMany();

        foreach ($submissionFiles as $submissionFile) { /** @var SubmissionFile $submissionFile */
            if ($submissionFile->getData('chapterId') == $chapter->getId()) {
                $referenceFileNode = $doc->createElementNS($deployment->getNamespace(), 'submission_file_ref');
                $referenceFileNode->setAttribute('id', $submissionFile->getId());
                $entityNode->appendChild($referenceFileNode);
            }
        }

        return $entityNode;
    }

    /**
     * Create and return an author node.
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMElement
     */
    public function createChapterAuthorNode($doc, $chapterAuthor)
    {
        $deployment = $this->getDeployment();
        $context = $deployment->getContext();

        // Create the entity node
        $entityNode = $doc->createElementNS($deployment->getNamespace(), 'chapterAuthor');
        $entityNode->setAttribute('author_id', $chapterAuthor->getId());
        $entityNode->setAttribute('seq', $chapterAuthor->getData('seq'));

        return $entityNode;
    }

    /**
     * Add a single pub ID element for a given plugin to the document.
     *
     * @param \DOMDocument $doc
     * @param \DOMElement $entityNode
     * @param \APP\monograph\Chapter $entity
     *
     * @return \DOMElement|null
     */
    public function addPubIdentifier($doc, $entityNode, $entity, $pubIdType)
    {
        $pubId = $entity->getStoredPubId($pubIdType);
        if ($pubId) {
            $deployment = $this->getDeployment();
            $entityNode->appendChild($node = $doc->createElementNS($deployment->getNamespace(), 'id', htmlspecialchars($pubId, ENT_COMPAT, 'UTF-8')));
            $node->setAttribute('type', $pubIdType);
            $node->setAttribute('advice', 'update');
            return $node;
        }
        return null;
    }

    /**
     * Create and add identifier nodes to a submission node.
     *
     * @param \DOMDocument $doc
     * @param \DOMElement $entityNode
     * @param \APP\monograph\Chapter $entity
     */
    public function addIdentifiers($doc, $entityNode, $entity)
    {
        $deployment = $this->getDeployment();

        // Add internal ID
        $entityNode->appendChild($node = $doc->createElementNS($deployment->getNamespace(), 'id', $entity->getId()));
        $node->setAttribute('type', 'internal');
        $node->setAttribute('advice', 'ignore');

        // Add public ID
        if ($pubId = $entity->getStoredPubId('publisher-id')) {
            $entityNode->appendChild($node = $doc->createElementNS($deployment->getNamespace(), 'id', htmlspecialchars($pubId, ENT_COMPAT, 'UTF-8')));
            $node->setAttribute('type', 'public');
            $node->setAttribute('advice', 'update');
        }

        // Add pub IDs by plugin
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $deployment->getContext()->getId());
        foreach ($pubIdPlugins as $pubIdPlugin) {
            $this->addPubIdentifier($doc, $entityNode, $entity, $pubIdPlugin->getPubIdType());
        }
        // Also add DOIs
        $this->addPubIdentifier($doc, $entityNode, $entity, 'doi');
    }
}
