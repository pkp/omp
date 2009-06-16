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

	/**
	 * Constructor
	 */
	function AcquisitionsArrangementHandler() {
		parent::ManagerHandler();
	}

	/**
	 * Display a list of the submission categories for the current press.
	 */
	function submissionCategory() {
		$this->listItems(CATEGORY_ARRANGEMENT);
	}

	/**
	 * Display a list of the arrangements within the current press.
	 */
	function createSubmissionCategory() {
		$this->createItem(CATEGORY_ARRANGEMENT);
	}

	/**
	 * Display a list of the arrangements within the current press.
	 * @param $args array optional, if set the first parameter is the ID of the arrangement to edit
	 */
	function editSubmissionCategory($args) {
		$this->editItem($args, CATEGORY_ARRANGEMENT);
	}

	/**
	 * Display a list of the arrangements within the current press.
	 * @param $args array optional, if set the first parameter is the ID of the arrangement to edit
	 */
	function updateSubmissionCategory($args) {
		$this->updateItem($args, CATEGORY_ARRANGEMENT);
	}

	/**
	 * Delete a submission category.
	 * @param $args array optional, if set the first parameter is the ID of the arrangement to edit
	 */
	function deleteSubmissionCategory($args) {
		$this->deleteItem($args, CATEGORY_ARRANGEMENT);
	}

	/**
	 * Move a submission category.
	 */
	function moveSubmissionCategory() {
		$this->moveItem(CATEGORY_ARRANGEMENT);
	}

	/**
	 * Display a list of the series for the current press.
	 */
	function series() {
		$this->listItems(SERIES_ARRANGEMENT);
	}

	/**
	 * Create a series.
	 */
	function createSeries() {
		$this->createItem(SERIES_ARRANGEMENT);
	}

	/**
	 * Edit a series.
	 * @param $args array optional, if set the first parameter is the ID of the arrangement to edit
	 */
	function editSeries($args) {
		$this->editItem($args, SERIES_ARRANGEMENT);
	}

	/**
	 * Update a series.
	 * @param $args array optional, if set the first parameter is the ID of the arrangement to edit
	 */
	function updateSeries($args) {
		$this->updateItem($args, SERIES_ARRANGEMENT);
	}

	/**
	 * Delete a series.
	 * @param $args array optional, if set the first parameter is the ID of the arrangement to edit
	 */
	function deleteSeries($args) {
		$this->deleteItem($args, SERIES_ARRANGEMENT);
	}

	/**
	 * Move series.
	 */
	function moveSeries() {
		$this->moveItem(SERIES_ARRANGEMENT);
	}
	
	/**
	 * Display a list of the current press's arrangements for the specified arrangement type.
	 */
	function listItems($type = SERIES_ARRANGEMENT) {
		$this->validate();
		$this->setupTemplate(false, $type);

		$press =& Request::getPress();
		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');

		switch ($type) {
			case CATEGORY_ARRANGEMENT: $arrangement = 'submissionCategory'; break;
			default: $arrangement = 'series';
		}

		$rangeInfo =& Handler::getRangeInfo($arrangement);
		$arrangements =& $arrangementDao->getByPressId($press->getId(), $rangeInfo, $type);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref($arrangement, $arrangements);
		$templateMgr->assign('helpTopicId','press.managementPages.'.$arrangement);

		switch ($type) {
			case CATEGORY_ARRANGEMENT: $templateMgr->display('manager/submissionCategory/submissionCategory.tpl'); break;
			default: $templateMgr->display('manager/series/series.tpl');
		}
	}

	/**
	 * Display form to create a new arrangement for the specified arrangement type.
	 * @param $type int
	 */
	function createItem($type = SERIES_ARRANGEMENT) {
		$this->editItem(null, $type);
	}

	/**
	 * Display form to create/edit an arrangement for the specified arrangement type.
	 * @param $args array optional, if set the first parameter is the ID of the arrangement to edit
	 * @param $type int
	 */
	function editItem($args = array(), $type = SERIES_ARRANGEMENT) {
		$this->validate();
		$this->setupTemplate(true, $type);

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

		if ($form->isLocaleResubmit()) {
			$form->readInputData();
		} else {
			$form->initData();
		}
		$form->display();
	}

	/**
	 * Save changes to an arrangement for the specified arrangement type.
	 * @param $args array first parameter is the ID of the arrangement to delete
	 * @param $type int
	 */
	function updateItem($args, $type = SERIES_ARRANGEMENT) {
		$this->validate();

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
			$this->setupTemplate(true, $type);
			$form->display();
		}
	}

	/**
	 * Delete an arrangement for the specified arrangement type.
	 * @param $args array first parameter is the ID of the arrangement to delete
	 * @param $type int
	 */
	function deleteItem($args, $type = SERIES_ARRANGEMENT) {
		$this->validate();

		if (isset($args) && !empty($args)) {
			$press =& Request::getPress();

			$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
			$arrangementDao->deleteById($args[0], $press->getId());
		}

		switch ($type) {
			case CATEGORY_ARRANGEMENT: Request::redirect(null, null, 'submissionCategory'); break;
			default: Request::redirect(null, null, 'series');
		}


	}

	/**
	 * Change the sequence of an arrangement for the specified arrangement type.
	 */
	function moveItem($type = SERIES_ARRANGEMENT) {
		$this->validate();

		$press =& Request::getPress();

		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
		$arrangement =& $arrangementDao->getById(Request::getUserVar('arrangementId'), $press->getId());

		if ($arrangement != null) {
			$arrangement->setSequence($arrangement->getSequence() + (Request::getUserVar('d') == 'u' ? -1.5 : 1.5));
			$arrangementDao->updateObject($arrangement);
			$arrangementDao->resequence($arrangement->getType());
		}

		switch ($type) {
			case CATEGORY_ARRANGEMENT: Request::redirect(null, null, 'submissionCategory'); break;
			default: Request::redirect(null, null, 'series');
		}
	}

	/**
	 * Setup common template variables for the specified arrangement.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 * @param $type int
	 */
	function setupTemplate($subclass = false, $type = SERIES_ARRANGEMENT) {
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_READER));
		parent::setupTemplate(true);
		if ($subclass) {
			$templateMgr =& TemplateManager::getManager();
		
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