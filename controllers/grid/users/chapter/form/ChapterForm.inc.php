<?php

/**
 * @file controllers/grid/chapter/form/ChapterForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
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
	function ChapterForm($monograph, $chapterId) {
		parent::Form('controllers/grid/users/chapter/form/chapterForm.tpl');
		$this->setMonograph($monograph);

		if (is_numeric($chapterId)) {
			$chapterDao =& DAORegistry::getDAO('ChapterDAO');
			$this->setChapter($chapterDao->getChapter($chapterId));
		}

		// Validation checks for this form
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
		$this->_monograph =& $monograph;
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
		$this->_chapter =& $chapter;
	}

	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the associated chapter.
	 * @param $chapter Chapter
	 */
	function initData() {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS));

		$monograph =& $this->getMonograph();
		$this->setData('monographId', $monograph->getId());

		$chapter =& $this->getChapter();
		if ($chapter) {
			$this->setData('chapterId', $chapter->getId());
			$this->setData('title', $chapter->getLocalizedTitle());
		} else {
			$this->setData('title', null);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('title'));
	}

	/**
	 * Save chapter
	 * @see Form::execute()
	 */
	function execute() {
		$chapterDao =& DAORegistry::getDAO('ChapterDAO');
		$chapter =& $this->getChapter();

		if ($chapter) {
			$chapter->setTitle($this->getData('title'), null); //Localized
			$chapterDao->updateObject($chapter);
		} else {
			$monograph =& $this->getMonograph();

			$chapter =& new Chapter();
			$chapter->setMonographId($monograph->getId());
			$chapter->setTitle($this->getData('title'), null); //Localized
			$chapterDao->insertChapter($chapter);
		}

		$this->setChapter($chapter);

		return true;
	}
}

?>
