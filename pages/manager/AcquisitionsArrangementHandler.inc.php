<?php

/**
 * @file SeriesHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for series management functions. 
 */

// $Id$

define('SERIES_ARRANGEMENT', 1);
define('CATEGORY_ARRANGEMENT', 2);

import('pages.manager.ManagerHandler');

class AcquisitionsArrangementHandler extends ManagerHandler {
	var $type; 

	/**
	 * Constructor
	 */	
	function AcquisitionsArrangementHandler() {
		parent::ManagerHandler();
		
		//FIXME: how is this set elsewhere?
		$this->type = CATEGORY_ARRANGEMENT;
	}
	
	/**
	 * Display a list of the series within the current press.
	 */
	function listItems() {
		$this->validate();
		$this->setupTemplate(false);

		$press =& Request::getPress();
		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');

		$type = $this->type;
		switch ($type) {
			case CATEGORY_ARRANGEMENT: $arrangement = 'submissionCategory'; break;
			default: $type = SERIES_ARRANGEMENT; $arrangement = 'series';
		}

		$rangeInfo =& Handler::getRangeInfo($arrangement);
		$arrangements =& $arrangementDao->getPressAcquisitionsArrangements($press->getId(), $rangeInfo, $type);

		$templateMgr =& TemplateManager::getManager();
//		$templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'manager'), 'manager.pressManagement')));
		$templateMgr->assign_by_ref($arrangement, $arrangements);
		$templateMgr->assign('helpTopicId','press.managementPages.'.$arrangement);

		switch ($type) {
			case CATEGORY_ARRANGEMENT: $templateMgr->display('manager/submissionCategory/submissionCategory.tpl'); break;
			default: $templateMgr->display('manager/series/series.tpl');
		}
	}

	/**
	 * Display form to create a new series.
	 */
	function createItem() {
		$this->editItem(null);
	}

	/**
	 * Display form to create/edit a series.
	 * @param $args array optional, if set the first parameter is the ID of the series to edit
	 */
	function editItem($args = array()) {
		$this->validate();
		$this->setupTemplate(true);

		$type = $this->type;
		switch ($type) {
			case CATEGORY_ARRANGEMENT: {
				import('manager.form.SubmissionCategoryForm');
				$form =& new SubmissionCategoryForm(!isset($args) || empty($args) ? null : ((int) $args[0]));
				break;
			}
			default: {
				import('manager.form.SeriesForm');
				$form =& new SeriesForm(!isset($args) || empty($args) ? null : ((int) $args[0]));
				break;
			}
		}

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		if ($form->isLocaleResubmit()) {
			$form->readInputData();
		} else {
			$form->initData();
		}
		$form->display();
	}

	/**
	 * Save changes to a series.
	 */
	function updateItem($args) {
		$this->validate();
		
		$type = $this->type;
		switch ($type) {
			case CATEGORY_ARRANGEMENT: {
				import('manager.form.SubmissionCategoryForm');
				// FIXME: Need construction by reference or validation always fails on PHP 4.x
				$form =& new SubmissionCategoryForm(!isset($args) || empty($args) ? null : ((int) $args[0]));
				$formRedirect = 'submissionCategory';
				break;
			}
			default: {
				import('manager.form.SeriesForm');
				// FIXME: Need construction by reference or validation always fails on PHP 4.x
				$form =& new SeriesForm(!isset($args) || empty($args) ? null : ((int) $args[0]));
				$formRedirect = 'series';
			}
		}

		$canExecute = $form->processEvents();

		if ($canExecute)
			switch (Request::getUserVar('editorAction')) {
				case 'addEditor':
					$form->includeAcquisitionsEditor((int) Request::getUserVar('userId'));
					$canExecute = false;
					break;
				case 'removeEditor':
					$form->omitAcquisitionsEditor((int) Request::getUserVar('userId'));
					$canExecute = false;
					break;
			}

		$form->readInputData();
		if ($canExecute && $form->validate()) {
			$form->execute();
			Request::redirect(null, null, $formRedirect);
		} else {
			$this->setupTemplate(true);
			$form->display();
		}
	}

	/**
	 * Delete a series.
	 * @param $args array first parameter is the ID of the series to delete
	 */
	function deleteItem($args) {
		$this->validate();

		if (isset($args) && !empty($args)) {
			$press =& Request::getPress();

			$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
			$arrangementDao->deleteAcquisitionsArrangementById($args[0], $press->getId());
		}
		
		$type = $this->type;
		switch ($type) {
			case CATEGORY_ARRANGEMENT: Request::redirect(null, null, 'submissionCategory'); break;
			default: Request::redirect(null, null, 'series');
		}


	}

	/**
	 * Change the sequence of a series.
	 */
	function moveItem() {
		$this->validate();

		$press =& Request::getPress();

		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
		$arrangement =& $arrangementDao->getAcquisitionsArrangement(Request::getUserVar('arrangementId'), $press->getId());

		if ($arrangement != null) {
			$arrangement->setSequence($arrangement->getSequence() + (Request::getUserVar('d') == 'u' ? -1.5 : 1.5));
			$arrangementDao->updateAcquisitionsArrangement($arrangement);
			$arrangementDao->resequenceAcquisitionsArrangements($arrangement->getArrangementType());
		}
		
		$type = $this->type;
		switch ($type) {
			case CATEGORY_ARRANGEMENT: Request::redirect(null, null, 'submissionCategory'); break;
			default: Request::redirect(null, null, 'series');
		}
	}

	function setupTemplate($subclass = false) {
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_READER));
		parent::setupTemplate(true);
		if ($subclass) {
			$templateMgr =& TemplateManager::getManager();
			$type = $this->type;			
			switch ($type) {
				case CATEGORY_ARRANGEMENT:
					$arrangement = 'submissionCategory';
					$localeKey  = 'submissionCategory.submissionCategories';
					break;
				default:
					$type = SERIES_ARRANGEMENT;
					$arrangement = 'series';
					$localeKey = 'series.series';
			}
			$templateMgr->append('pageHierarchy', array(Request::url(null, 'manager', $arrangement), $localeKey));
		}
	}
}
?>
