<?php

/**
 * @file controllers/grid/chapter/form/ChapterForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterForm
 * @ingroup controllers_grid_chapterId_form
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
	function ChapterForm($monographId, $chapterId) {
		parent::Form('controllers/grid/users/chapter/form/chapterForm.tpl');
		$this->_monographId = $monographId;

		if ( is_numeric($chapterId) ) {
			$chapterDao =& DAORegistry::getDAO('ChapterDAO');
			$this->_chapter =& $chapterDao->getChapter($chapterId);
		}

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Get method
	//
	/**
	 * Get the Chapter associated with this form
	 */
	function &getChapter() {
		return $this->_chapter;
	}

	//
	// Template methods from Form
	//
	/**
	 * Initialize form data from the associated chapter.
	 * @param $chapter Chapter
	 */
	function initData() {
		$this->setData('monographId', $this->_monographId);
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS));
		$chapter =& $this->getChapter();
		if ( $chapter ) {
			$this->setData('chapterId', $chapter->getId());
			$this->setData('title', $chapter->getLocalizedTitle());
		} else {
			$this->setData('title', null);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('title'));
	}

	/**
	 * Save chapter
	 */
	function execute() {
		$chapterDao =& DAORegistry::getDAO('ChapterDAO');
		$chapter =& $this->getChapter();

		if ( $chapter ) {
			$chapter->setTitle($this->getData('title'), null); //Localized
			$chapterDao->updateObject($chapter);
		} else {
			$chapter =& new Chapter();
			$chapter->setMonographId($this->_monographId);
			$chapter->setTitle($this->getData('title'), null); //Localized
			$chapterDao->insertChapter($chapter);
		}

		$this->_chapter =& $chapter;

		return true;
	}
}

?>
