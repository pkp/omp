<?php

/**
 * @file controllers/grid/users/chapter/form/ChapterForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	 */
	function ChapterForm($monograph, &$chapter) {
		parent::Form('controllers/grid/users/chapter/form/chapterForm.tpl');
		$this->setMonograph($monograph);

		if ($chapter) {
			$this->setChapter($chapter);
		}

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'title', 'required', 'metadata.property.validationMessage.title'));
		$this->addCheck(new FormValidatorPost($this));
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
		$this->readUserVars(array('title', 'subtitle', 'authors'));
	}

	/**
	 * Save chapter
	 * @see Form::execute()
	 */
	function execute() {
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$chapter = $this->getChapter();

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
			$chapterDao->insertChapter($chapter);
		}

		$this->setChapter($chapter);

		// Save the author associations. (See insert/deleteEntry.)
		import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
		ListbuilderHandler::unpack(null, $this->getData('authors'));

		return true;
	}

	/**
	 * Persist a new author entry insert.
	 * @param $request PKPRequest ALWAYS NULL IN THIS INSTANCE
	 * @param $newRowId mixed New entry with data to persist
	 * @return boolean True iff successful
	 */
	function insertEntry($request, $newRowId) {
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
	 * FIXME: duplicated function from Listbuilder base class.
	 * The updateEntry callback was not getting called because
	 * the this on Listbuilder unpack function was set to this
	 * form.
	 * @param $request PKPRequest ALWAYS NULL IN THIS INSTANCE
	 * @param $rowId string Row ID
	 * @param $newRowId array Array
	 * @return boolean True iff successful
	 */
	function updateEntry($request, $rowId, $newRowId) {
		if (!$this->deleteEntry($request, $rowId)) return false;
		return $this->insertEntry($request, $newRowId);
	}

	/**
	 * Delete an author entry.
	 * @param $request PKPRequest ALWAYS NULL IN THIS INSTANCE
	 * @param $rowId mixed ID of row to modify
	 * @return boolean True iff successful
	 */
	function deleteEntry($request, $rowId) {
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
}

?>
