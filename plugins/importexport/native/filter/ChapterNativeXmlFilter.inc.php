<?php

/**
 * @file plugins/importexport/native/filter/ChapterNativeXmlFilter.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterNativeXmlFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Base class that converts a set of authors to a Native XML document
 */

import('lib.pkp.plugins.importexport.native.filter.NativeExportFilter');

class ChapterNativeXmlFilter extends NativeExportFilter
{
    /**
     * Constructor
     *
     * @param $filterGroup FilterGroup
     */
    public function __construct($filterGroup)
    {
        $this->setDisplayName('Native XML chapter export');
        parent::__construct($filterGroup);
    }


    //
    // Implement template methods from PersistableFilter
    //
    /**
     * @copydoc PersistableFilter::getClassName()
     */
    public function getClassName()
    {
        return 'plugins.importexport.native.filter.ChapterNativeXmlFilter';
    }


    //
    // Implement template methods from Filter
    //
    /**
     * @see Filter::process()
     *
     * @param $chapters Chapter[]
     *
     * @return DOMDocument
     */
    public function &process(&$chapters)
    {
        // Create the XML document
        $doc = new DOMDocument('1.0');
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
     * @param $doc DOMDocument
     * @param $chapter Chapter
     *
     * @return DOMElement
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
        $chapterAuthorDao = DAORegistry::getDAO('ChapterAuthorDAO'); /** @var ChapterAuthorDAO $chapterAuthorDao */
        $chapterAuthors = $chapterAuthorDao->getAuthors($chapter->getData('publicationId'), $chapter->getId())->toArray();

        foreach ($chapterAuthors as $chapterAuthor) {
            $entityNode->appendChild($this->createChapterAuthorNode($doc, $chapterAuthor));
        }

        $submissionFiles = Services::get('submissionFile')->getMany(['submissionIds' => [$publication->getData('submissionId')]]);
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
     * @param $doc DOMDocument
     *
     * @return DOMElement
     */
    public function createChapterAuthorNode($doc, $chapterAuthor)
    {
        $deployment = $this->getDeployment();
        $context = $deployment->getContext();

        // Create the entity node
        $entityNode = $doc->createElementNS($deployment->getNamespace(), 'chapterAuthor');
        $entityNode->setAttribute('author_id', $chapterAuthor->getId());
        $entityNode->setAttribute('primary_contact', $chapterAuthor->getData('primaryContact'));
        $entityNode->setAttribute('seq', $chapterAuthor->getData('seq'));

        return $entityNode;
    }

    /**
     * Add a single pub ID element for a given plugin to the document.
     *
     * @param $doc DOMDocument
     * @param $entityNode DOMElement
     * @param $entity Chapter
     * @param $pubIdPlugin PubIdPlugin
     *
     * @return DOMElement|null
     */
    public function addPubIdentifier($doc, $entityNode, $entity, $pubIdPlugin)
    {
        $pubId = $entity->getStoredPubId($pubIdPlugin->getPubIdType());
        if ($pubId) {
            $deployment = $this->getDeployment();
            $entityNode->appendChild($node = $doc->createElementNS($deployment->getNamespace(), 'id', htmlspecialchars($pubId, ENT_COMPAT, 'UTF-8')));
            $node->setAttribute('type', $pubIdPlugin->getPubIdType());
            $node->setAttribute('advice', 'update');
            return $node;
        }
        return null;
    }

    /**
     * Create and add identifier nodes to a submission node.
     *
     * @param $doc DOMDocument
     * @param $entityNode DOMElement
     * @param $entity Chapter
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
            $this->addPubIdentifier($doc, $entityNode, $entity, $pubIdPlugin);
        }
    }
}
