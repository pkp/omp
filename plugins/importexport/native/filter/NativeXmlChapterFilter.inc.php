<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlChapterFilter.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlChapterFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Base class that converts a Native XML document to a set of authors
 */

import('lib.pkp.plugins.importexport.native.filter.NativeImportFilter');

class NativeXmlChapterFilter extends NativeImportFilter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function __construct($filterGroup) {
		$this->setDisplayName('Native XML Chapter import');
		parent::__construct($filterGroup);
	}

	//
	// Implement template methods from NativeImportFilter
	//
	/**
	 * Return the plural element name
	 * @return string
	 */
	function getPluralElementName() {
		return 'chapters';
	}

	/**
	 * Get the singular element name
	 * @return string
	 */
	function getSingularElementName() {
		return 'chapter';
	}

	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.native.filter.NativeXmlChapterFilter';
	}


	/**
	 * Handle a chapter element
	 * @param $node DOMElement
	 * @return Chapter
	 */
	function handleElement($node) {
		$deployment = $this->getDeployment();
		$context = $deployment->getContext();

		$publication = $deployment->getPublication();
		assert(is_a($publication, 'Publication'));

		// Create the data object
		$chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var $chapterDao ChapterDAO */

		$chapter = $chapterDao->newDataObject();

		$chapter->setData('publicationId', $publication->getId());
		$chapter->setSequence($node->getAttribute('seq'));

		$chapterId = $chapterDao->insertChapter($chapter);
		$chapter->setData('id', $chapterId);

		// Handle metadata in subelements
		for ($n = $node->firstChild; $n !== null; $n=$n->nextSibling) if (is_a($n, 'DOMElement')) switch($n->tagName) {
			case 'title':
				$locale = $n->getAttribute('locale');
				if (empty($locale)) $locale = $context->getLocale();
				$chapter->setData('title', $n->textContent, $locale);
				break;
			case 'abstract':
				$locale = $n->getAttribute('locale');
				if (empty($locale)) $locale = $context->getLocale();
				$chapter->setData('abstract', $n->textContent, $locale);
				break;
			case 'subtitle':
				$locale = $n->getAttribute('locale');
				if (empty($locale)) $locale = $context->getLocale();
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

		$chapterDao->updateObject($chapter);
		
		return $chapter;
	}

	/**
	 * Parse an author and add it to the chapter.
	 * @param $n DOMElement
	 * @param $chapter Chapter
	 */
	function parseAuthor($n, $chapter) {
		$deployment = $this->getDeployment();

		$chapterAuthorDao = DAORegistry::getDAO('ChapterAuthorDAO'); /** @var $chapterAuthorDao ChapterAuthorDAO */

		$authorId = $deployment->getAuthorDBId($n->getAttribute('author_id'));
		$primaryContact = $n->getAttribute('primary_contact');
		$seq = $n->getAttribute('seq');
			
		$chapterAuthorDao->insertChapterAuthor($authorId, $chapter->getId(), $primaryContact, $seq);
	}

	/**
	 * Parse an author and add it to the chapter.
	 * @param $n DOMElement
	 * @param $chapter Chapter
	 */
	function parseSubmissionFileRef($n, $chapter) {
		$deployment = $this->getDeployment();
		$context = $deployment->getContext();

		$publication = $deployment->getPublication();

		$fileId = $n->getAttribute('id');
		$fileRevision = $n->getAttribute('revision');
			
		$sourceFileId = $deployment->getFileDBId($fileId, $fileRevision);
		if ($sourceFileId) {
			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /** @var $submissionFileDao SubmissionFileDAO */
			$submissionFile = $submissionFileDao->getRevision($sourceFileId, $fileRevision);

			if ($submissionFile) {
				$submissionFile->setData('chapterId', $chapter->getId());

				$submissionFileDao->updateObject($submissionFile);
			}
		}
	}
}


