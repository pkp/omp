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

	/** @var $arrangementId int The ID of the arrangement being edited */
	var $arrangementId;

	/** @var $includeSubmissionCategoryEditor object Additional acquisitions editor to
	 *       include in assigned list for this arrangement
	 */
	var $includeArrangementEditor;

	/** @var $omitSubmissionCategoryEditor object Assigned acquisitions editor to omit from
	 *       assigned list for this arrangement
	 */
	var $omitArrangementEditor;

	/** @var $arrangementEditors array List of user objects representing the
	 *       available acquisitions editors for this press.
	 */
	var $arrangementEditors;

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
	 * @param $arrangementEditorId int
	 */
	function includeAcquisitionsEditor($arrangementEditorId) {
		foreach ($this->arrangementEditors as $key => $junk) {
			if ($this->arrangementEditors[$key]->getId() == $arrangementEditorId) {
				$this->includeArrangementEditor =& $this->arrangementEditors[$key];
			}
		}
	}

	/**
	 * When displaying the form, omit the specified acquisition editor from
	 * the assigned list for this arrangement.
	 */
	function omitAcquisitionsEditor($arrangementEditorId) {
		foreach ($this->arrangementEditors as $key => $junk) {
			if ($this->arrangementEditors[$key]->getId() == $arrangementEditorId) {
				$this->omitArrangementEditor =& $this->arrangementEditors[$key];
			}
		}
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		$press =& Request::getPress();
		$arrangementEditorsDao =& DAORegistry::getDAO('AcquisitionsArrangementEditorsDAO');
		if (isset($this->arrangementId)) {
			$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
			$arrangement =& $arrangementDao->getById($this->arrangementId, $press->getId());

			if ($arrangement == null) {
				unset($this->arrangementId);
			} else {
				$this->_data = array(
					'arrangementId' => $this->arrangementId, 
					'title' => $arrangement->getTitle(null), // Localized
					'abbrev' => $arrangement->getAbbrev(null), // Localized
					'reviewFormId' => $arrangement->getReviewFormId(),
					'metaIndexed' => !$arrangement->getMetaIndexed(), // #2066: Inverted
					'editorRestriction' => $arrangement->getEditorRestricted(),
					'hideAbout' => $arrangement->getHideAbout(),
					'disableComments' => $arrangement->getDisableComments(),
					'policy' => $arrangement->getPolicy(null), // Localized
					'assignedEditors' => $arrangementEditorsDao->getEditorsByArrangementId($this->arrangementId, $press->getId()),
					'unassignedEditors' => $arrangementEditorsDao->getEditorsNotInArrangement($press->getId(), $this->arrangementId)
				);
			}
		} else {
			$this->_data = array(
				'unassignedEditors' => $arrangementEditorsDao->getEditorsNotInArrangement($press->getId(), null)
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

		foreach ($this->arrangementEditors as $key => $junk) {
			$arrangementEditor =& $this->arrangementEditors[$key]; // Ref
			$userId = $arrangementEditor->getId();

			$isIncludeEditor = $this->includeArrangementEditor && $this->includeArrangementEditor->getId() == $userId;
			$isOmitEditor = $this->omitArrangementEditor && $this->omitArrangementEditor->getId() == $userId;
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
		$press =& Request::getPress();
		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');

		$reviewForms =& $reviewFormDao->getActiveByPressId($press->getId());
		$reviewFormOptions = array();
		while ($reviewForm =& $reviewForms->next()) {
			$reviewFormOptions[$reviewForm->getReviewFormId()] = $reviewForm->getReviewFormTitle();
		}

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('arrangementId', $this->arrangementId);
		$templateMgr->assign('commentsEnabled', $press->getSetting('enableComments'));
		$templateMgr->assign_by_ref('reviewFormOptions', $reviewFormOptions);
		parent::display();
	}

	/**
	 * Save arrangement.
	 */
	function execute() {
		$press =& Request::getPress();
		$pressId = $press->getId();

		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');

		if (isset($this->arrangementId)) {
			$arrangement =& $arrangementDao->getById($this->arrangementId, $pressId);
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
		$arrangement->setType($this->getData('arrangementType'));

		if (isset($this->arrangementId)) {
			$arrangementDao->updateObject($arrangement);
			$arrangementId = $arrangement->getId();

		} else {
			$arrangementId = $arrangementDao->insertObject($arrangement);
			$arrangementDao->resequence($arrangement->getType());
		}

		$this->arrangementId = $arrangementId;
		// Save assigned editors
		$assignedEditorIds = Request::getUserVar('assignedEditorIds');
		if (empty($assignedEditorIds)) $assignedEditorIds = array();
		elseif (!is_array($assignedEditorIds)) $assignedEditorIds = array($assignedEditorIds);
		$arrangementsEditorsDao =& DAORegistry::getDAO('AcquisitionsArrangementEditorsDAO');
		$arrangementsEditorsDao->deleteEditorsByArrangementId($arrangementId, $pressId);
		foreach ($this->arrangementEditors as $key => $junk) {
			$arrangementEditor =& $this->arrangementEditors[$key];
			$userId = $arrangementEditor->getId();
			// We don't have to worry about omit- and include-
			// acquisitions editors because this function is only called
			// when the Save button is pressed and those are only
			// used in other cases.
			if (in_array($userId, $assignedEditorIds)) $arrangementsEditorsDao->insertEditor(
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