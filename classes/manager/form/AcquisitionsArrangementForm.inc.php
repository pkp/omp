<?php

/**
 * @file classes/manager/form/AcquisitionsArrangementForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AcquisitionsArrangementForm
 * @ingroup manager_form
 *
 * @brief Form for creating and modifying press arrangements.
 */

// $Id$


import('form.Form');

class AcquisitionsArrangementForm extends Form {

	/** @var $acquisitionsArrangementId int The ID of the arrangement being edited */
	var $acquisitionsArrangementId;

	/** @var $includeSubmissionCategoryEditor object Additional acquisitions editor to
	 *       include in assigned list for this arrangement
	 */
	var $includeAcquisitionsArrangementEditor;

	/** @var $omitSubmissionCategoryEditor object Assigned acquisitions editor to omit from
	 *       assigned list for this arrangement
	 */
	var $omitAcquisitionsArrangementEditor;

	/** @var $acquisitionsArrangementEditors array List of user objects representing the
	 *       available acquisitions editors for this press.
	 */
	var $acquisitionsArrangementEditors;

	/**
	 * Get the names of fields for which localized data is allowed.
	 * @return array
	 */
	function getLocaleFieldNames() {
		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
		return $arrangementDao->getLocaleFieldNames();
	}

	/**
	 * When displaying the form, include the specified acquisition editor
	 * in the assigned list for this arrangement.
	 * @param $acquisitionsArrangementEditorId int
	 */
	function includeAcquisitionsEditor($acquisitionsArrangementEditorId) {
		foreach ($this->acquisitionsArrangementEditors as $key => $junk) {
			if ($this->acquisitionsArrangementEditors[$key]->getId() == $acquisitionsArrangementEditorId) {
				$this->includeAcquisitionsArrangementEditor =& $this->acquisitionsArrangementEditors[$key];
			}
		}
	}

	/**
	 * When displaying the form, omit the specified acquisition editor from
	 * the assigned list for this arrangement.
	 */
	function omitAcquisitionsEditor($acquisitionsArrangementEditorId) {
		foreach ($this->acquisitionsArrangementEditors as $key => $junk) {
			if ($this->acquisitionsArrangementEditors[$key]->getId() == $acquisitionsArrangementEditorId) {
				$this->omitAcquisitionsArrangementEditor =& $this->acquisitionsArrangementEditors[$key];
			}
		}
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		$press =& Request::getPress();
		$acquisitionsArrangementEditorsDao =& DAORegistry::getDAO('AcquisitionsArrangementEditorsDAO');
		if (isset($this->acquisitionsArrangementId)) {
			$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
			$arrangement =& $arrangementDao->getById($this->acquisitionsArrangementId, $press->getId());

			if ($arrangement == null) {
				unset($this->acquisitionsArrangementId);
			} else {
				$this->_data = array(
					'arrangementId' => $this->acquisitionsArrangementId, 
					'title' => $arrangement->getTitle(null), // Localized
					'abbrev' => $arrangement->getAbbrev(null), // Localized
					'reviewFormId' => $arrangement->getReviewFormId(),
					'metaIndexed' => !$arrangement->getMetaIndexed(), // #2066: Inverted
					'editorRestriction' => $arrangement->getEditorRestricted(),
					'hideAbout' => $arrangement->getHideAbout(),
					'disableComments' => $arrangement->getDisableComments(),
					'policy' => $arrangement->getPolicy(null), // Localized
					'assignedEditors' => $acquisitionsArrangementEditorsDao->getEditorsByAcquisitionsArrangementId($press->getId(), $this->acquisitionsArrangementId),
					'unassignedEditors' => $acquisitionsArrangementEditorsDao->getEditorsNotInArrangement($press->getId(), $this->acquisitionsArrangementId)
				);
			}
		} else {
			$this->_data = array(
				'unassignedEditors' => $acquisitionsArrangementEditorsDao->getEditorsNotInArrangement($press->getId(), null)
			);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {

		$this->readUserVars(array('title', 'abbrev', 'policy', 'reviewFormId', 'metaIndexed', 'editorRestriction', 'hideAbout', 'disableComments', 'arrangementType'));

		$assignedEditorIds = Request::getUserVar('assignedEditorIds');
		if (empty($assignedEditorIds)) $assignedEditorIds = array();
		elseif (!is_array($assignedEditorIds)) $assignedEditorIds = array($assignedEditorIds);

		$assignedEditors = $unassignedEditors = array();

		foreach ($this->acquisitionsArrangementEditors as $key => $junk) {
			$arrangementEditor =& $this->acquisitionsArrangementEditors[$key]; // Ref
			$userId = $arrangementEditor->getId();

			$isIncludeEditor = $this->includeAcquisitionsArrangementEditor && $this->includeAcquisitionsArrangementEditor->getId() == $userId;
			$isOmitEditor = $this->omitAcquisitionsArrangementEditor && $this->omitAcquisitionsArrangementEditor->getId() == $userId;
			if ((in_array($userId, $assignedEditorIds) || $isIncludeEditor) && !$isOmitEditor) {
				$assignedEditors[] = array(
					'user' => &$arrangementEditor,
					'canReview' => (Request::getUserVar('canReview' . $userId)?1:0),
					'canEdit' => (Request::getUserVar('canEdit' . $userId)?1:0)
				);
			} else {
				$unassignedEditors[] =& $arrangementEditor;
			}

			unset($arrangementEditor);
		}

		$this->setData('assignedEditors', $assignedEditors);
		$this->setData('unassignedEditors', $unassignedEditors);
	}

	/**
	 * Display the form.
	 */
	function display() {
		parent::display();
		$press =& Request::getPress();
		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');

		$reviewForms =& $reviewFormDao->getActiveByPressId($press->getId());
		$reviewFormOptions = array();
		while ($reviewForm =& $reviewForms->next()) {
			$reviewFormOptions[$reviewForm->getReviewFormId()] = $reviewForm->getReviewFormTitle();
		}

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('arrangementId', $this->acquisitionsArrangementId);
		$templateMgr->assign('commentsEnabled', $press->getSetting('enableComments'));
		$templateMgr->assign_by_ref('reviewFormOptions', $reviewFormOptions);
	}

	/**
	 * Save arrangement.
	 */
	function execute() {
		$press =& Request::getPress();
		$pressId = $press->getId();

		$acquisitionsArrangementsDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');

		if (isset($this->acquisitionsArrangementId)) {
			$arrangement =& $acquisitionsArrangementsDao->getById($this->acquisitionsArrangementId, $pressId);
		}

		if (!isset($arrangement)) {
			$arrangement = new AcquisitionsArrangement();
			$arrangement->setPressId($pressId);
			$arrangement->setSequence(REALLY_BIG_NUMBER);
		}

		$arrangement->setTitle($this->getData('title'), null); // Localized
		$arrangement->setAbbrev($this->getData('abbrev'), null); // Localized
		$reviewFormId = $this->getData('reviewFormId');
		if ($reviewFormId === '') $reviewFormId = null;
		$arrangement->setReviewFormId($reviewFormId);
		$arrangement->setMetaIndexed($this->getData('metaIndexed') ? 0 : 1);
		$arrangement->setEditorRestricted($this->getData('editorRestriction') ? 1 : 0);
		$arrangement->setHideAbout($this->getData('hideAbout') ? 1 : 0);
		$arrangement->setDisableComments($this->getData('disableComments') ? 1 : 0);
		$arrangement->setPolicy($this->getData('policy'), null); // Localized
		$arrangement->setArrangementType($this->getData('arrangementType'));

		if (isset($this->acquisitionsArrangementId)) {
			$acquisitionsArrangementsDao->updateObject($arrangement);
			$arrangementId = $arrangement->getId();

		} else {
			$arrangementId = $acquisitionsArrangementsDao->insertObject($arrangement);
			$acquisitionsArrangementsDao->resequence($arrangement->getArrangementType());
		}

		$this->acquisitionsArrangementId = $arrangementId;
		// Save assigned editors
		$assignedEditorIds = Request::getUserVar('assignedEditorIds');
		if (empty($assignedEditorIds)) $assignedEditorIds = array();
		elseif (!is_array($assignedEditorIds)) $assignedEditorIds = array($assignedEditorIds);
		$acquisitionsArrangementsEditorsDao =& DAORegistry::getDAO('AcquisitionsArrangementEditorsDAO');
		$acquisitionsArrangementsEditorsDao->deleteEditorsByAcquisitionsArrangementId($arrangementId, $pressId);
		foreach ($this->acquisitionsArrangementEditors as $key => $junk) {
			$arrangementEditor =& $this->acquisitionsArrangementEditors[$key];
			$userId = $arrangementEditor->getId();
			// We don't have to worry about omit- and include-
			// acquisitions editors because this function is only called
			// when the Save button is pressed and those are only
			// used in other cases.
			if (in_array($userId, $assignedEditorIds)) $acquisitionsArrangementsEditorsDao->insertEditor(
				$pressId,
				$arrangementId,
				$userId,
				Request::getUserVar('canReview' . $userId),
				Request::getUserVar('canEdit' . $userId)
			);
			unset($arrangementEditor);
		}
	}

	/**
	 * Forms can respond to special events by implementing this method.
	 * @return true if no events were handled and the form can execute
	 */
	function processEvents() {
		return true;
	}
}

?>