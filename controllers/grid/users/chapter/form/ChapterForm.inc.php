<?php

/**
 * @file controllers/grid/users/chapter/form/ChapterForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterForm
 * @ingroup controllers_grid_users_chapter_form
 *
 * @brief Form for adding/editing a chapter
 * stores/retrieves from an associative array
 */

import('lib.pkp.classes.form.Form');

class ChapterForm extends Form {
	/** The monograph associated with the submission chapter being edited **/
	var $_monographId;

	/** Chapter the chapter being edited **/
	var $_chapter;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $chapter Chapter
	 */
	function __construct($monograph, $chapter) {
		parent::__construct('controllers/grid/users/chapter/form/chapterForm.tpl');
		$this->setMonograph($monograph);

		if ($chapter) {
			$this->setChapter($chapter);
		}

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'title', 'required', 'metadata.property.validationMessage.title'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this chapter grid.
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the monograph associated with this chapter grid.
	 * @param $monograph Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph = $monograph;
	}

	/**
	 * Get the Chapter associated with this form
	 * @return Chapter
	 */
	function getChapter() {
		return $this->_chapter;
	}

	/**
	 * Set the Chapter associated with this form
	 * @param $chapter Chapter
	 */
	function setChapter($chapter) {
		$this->_chapter = $chapter;
	}

	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the associated chapter.
	 * @param $chapter Chapter
	 */
	function initData() {
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_DEFAULT, LOCALE_COMPONENT_PKP_SUBMISSION);

		$monograph = $this->getMonograph();
		$this->setData('submissionId', $monograph->getId());

		$chapter = $this->getChapter();
		if ($chapter) {
			$this->setData('chapterId', $chapter->getId());
			$this->setData('title', $chapter->getTitle());
			$this->setData('subtitle', $chapter->getSubtitle());
		} else {
			$this->setData('title', null);
			$this->setData('subtitle', null);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('title', 'subtitle', 'authors', 'files'));
	}

	/**
	 * Save chapter
	 * @see Form::execute()
	 */
	function execute() {
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$chapter = $this->getChapter();
		$request = Application::get()->getRequest();

		if ($chapter) {
			$chapter->setTitle($this->getData('title'), null); //Localized
			$chapter->setSubtitle($this->getData('subtitle'), null); //Localized
			$chapterDao->updateObject($chapter);
		} else {
			$monograph = $this->getMonograph();

			$chapter = $chapterDao->newDataObject();
			$chapter->setMonographId($monograph->getId());
			$chapter->setTitle($this->getData('title'), null); //Localized
			$chapter->setSubtitle($this->getData('subtitle'), null); //Localized
			$chapter->setSequence(REALLY_BIG_NUMBER);
			$chapterDao->insertChapter($chapter);
			$chapterDao->resequenceChapters($monograph->getId());
		}

		$this->setChapter($chapter);

		// Save the author associations. (See insert/deleteEntry.)
		import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
		ListbuilderHandler::unpack(
			$request, $this->getData('authors'),
			array($this, 'deleteAuthorsEntry'),
			array($this, 'insertAuthorsEntry'),
			array($this, 'updateAuthorsEntry')
		);

		ListbuilderHandler::unpack(
			$request, $this->getData('files'),
			array($this, 'deleteFilesEntry'),
			array($this, 'insertFilesEntry'),
			array($this, 'updateFilesEntry')
		);

		return true;
	}

	/**
	 * Persist a new author entry insert.
	 * @param $request Request
	 * @param $newRowId mixed New entry with data to persist
	 * @return boolean
	 */
	function insertAuthorsEntry($request, $newRowId) {
		$monograph = $this->getMonograph();
		$chapter = $this->getChapter();
		$authorId = (int) $newRowId['name'];
		$sequence = (int) $newRowId['sequence'];

		// Create a new chapter author.
		$chapterAuthorDao = DAORegistry::getDAO('ChapterAuthorDAO');
		// FIXME: primary authors not set for chapter authors.
		$chapterAuthorDao->insertChapterAuthor($authorId, $chapter->getId(), $monograph->getId(), false, $sequence);
		return true;
	}

	/**
	 * @copydoc ListbuilderHandler::updateEntry()
	 */
	function updateAuthorsEntry($request, $rowId, $newRowId) {
		if (!$this->deleteAuthorsEntry($request, $rowId)) return false;
		return $this->insertAuthorsEntry($request, $newRowId);
	}

	/**
	 * Delete an author entry.
	 * @param $request Request
	 * @param $rowId mixed ID of row to modify
	 * @return boolean
	 */
	function deleteAuthorsEntry($request, $rowId) {
		$chapter = $this->getChapter();
		$authorId = (int) $rowId; // this is the authorId to remove and is already an integer
		if ($authorId) {
			// remove the chapter author.
			$chapterAuthorDao = DAORegistry::getDAO('ChapterAuthorDAO');
			$chapterAuthorDao->deleteChapterAuthorById($authorId, $chapter->getId());
			return true;
		}
		return false;
	}

	/**
	 * Persist a new files entry insert.
	 * @param $request Request
	 * @param $newRowId mixed New entry with data to persist
	 * @return boolean
	 */
	function insertFilesEntry($request, $newRowId) {
		$monograph = $this->getMonograph();
		$chapter = $this->getChapter();
		$fileId = (int) $newRowId['name'];

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /** @var $submissionFileDao SubmissionFileDAO */
		$submissionFiles = $submissionFileDao->getAllRevisions($fileId, null, $monograph->getId());
		foreach ($submissionFiles as $submissionFile) {
			$submissionFile->setData('chapterId', $chapter->getId());
			$submissionFileDao->updateObject($submissionFile);
		}
		return true;
	}

	/**
	 * @copydoc ListbuilderHandler::updateEntry()
	 */
	function updateFilesEntry($request, $rowId, $newRowId) {
		if (!$this->deleteFilesEntry($request, $rowId)) return false;
		return $this->insertFilesEntry($request, $newRowId);
	}

	/**
	 * Delete a file association with a chapter.
	 * @param $request Request
	 * @param $rowId mixed ID of row to modify
	 * @return boolean
	 */
	function deleteFilesEntry($request, $rowId) {
		$chapter = $this->getChapter();
		$fileId = (int) $rowId; // this is the fileId to remove and is already an integer
		if ($fileId) {
			// Remove the chapter/file association.
			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$monograph = $this->getMonograph();
			$submissionFiles = $submissionFileDao->getAllRevisions($fileId, null, $monograph->getId());
			foreach ($submissionFiles as $submissionFile) {
				$submissionFile->setData('chapterId', null);
				$submissionFileDao->updateObject($submissionFile);
			}
			return true;
		}
		return false;
	}
}


