<?php

/**
 * @file plugins/importexport/native/filter/ChapterNativeXmlFilter.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterNativeXmlFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Base class that converts a set of authors to a Native XML document
 */

import('lib.pkp.plugins.importexport.native.filter.NativeExportFilter');

class ChapterNativeXmlFilter extends NativeExportFilter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function __construct($filterGroup) {
		$this->setDisplayName('Native XML chapter export');
		parent::__construct($filterGroup);

		$this->getNoValidation(true);
	}


	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.native.filter.ChapterNativeXmlFilter';
	}


	//
	// Implement template methods from Filter
	//
	/**
	 * @see Filter::process()
	 * @param $chapters Chapter[]
	 * @return DOMDocument
	 */
	function &process(&$chapters) {
		// Create the XML document
		$doc = new DOMDocument('1.0');
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$deployment = $this->getDeployment();

		// Multiple authors; wrap in a <authors> element
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
	 * @param $doc DOMDocument
	 * @param $chapter Chapter
	 * @return DOMElement
	 */
	function createChapterNode($doc, $chapter) {
		$deployment = $this->getDeployment();
		$context = $deployment->getContext();

		$publication = $deployment->getPublication();

		// Create the entity node
		$entityNode = $doc->createElementNS($deployment->getNamespace(), 'chapter');
		$entityNode->setAttribute('seq', $chapter->getSequence());
		$entityNode->setAttribute('id', $chapter->getId());

		// Add metadata
		$this->createLocalizedNodes($doc, $entityNode, 'title', $chapter->getData('title'));
		$this->createLocalizedNodes($doc, $entityNode, 'abstract', $chapter->getData('abstract'));
		$this->createLocalizedNodes($doc, $entityNode, 'subtitle', $chapter->getData('subtitle'));

		$entityNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'pages', $chapter->getData('pages')));

		// Add authors
		$chapterAuthorDao = DAORegistry::getDAO('ChapterAuthorDAO'); /** @var $chapterAuthorDao ChapterAuthorDAO */
		$chapterAuthors = $chapterAuthorDao->getAuthors($chapter->getData('publicationId'), $chapter->getId())->toArray();

		foreach ($chapterAuthors as $chapterAuthor) {
			$entityNode->appendChild($this->createChapterAuthorNode($doc, $chapterAuthor));
		}

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /** @var $submissionFileDao SubmissionFileDAO */
		$submissionFiles = $submissionFileDao->getBySubmissionId($publication->getData('submissionId'));
		foreach ($submissionFiles as $submissionFile) { /** @var $submissionFile SubmissionFile */
			if ($submissionFile->getData('chapterId') == $chapter->getId()) {
				$referenceFileNode = $doc->createElementNS($deployment->getNamespace(), 'submission_file_ref');
				$referenceFileNode->setAttribute('id', $submissionFile->getId());
				$referenceFileNode->setAttribute('revision', $submissionFile->getRevision());
				$entityNode->appendChild($referenceFileNode);
			}
		}
		
		return $entityNode;
	}

	/**
	 * Create and return an author node.
	 * @param $doc DOMDocument
	 * @param $chapter ChapterAuthor
	 * @return DOMElement
	 */
	function createChapterAuthorNode($doc, $chapterAuthor) {
		$deployment = $this->getDeployment();
		$context = $deployment->getContext();

		// Create the entity node
		$entityNode = $doc->createElementNS($deployment->getNamespace(), 'chapterAuthor');
		$entityNode->setAttribute('author_id', $chapterAuthor->getId());
		$entityNode->setAttribute('primary_contact', $chapterAuthor->getData('primaryContact'));
		$entityNode->setAttribute('seq', $chapterAuthor->getData('seq'));

		return $entityNode;
	}
}


