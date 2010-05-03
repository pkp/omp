<?php

/**
 * @file classes/manager/form/SeriesForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesForm
 * @ingroup manager_form
 *
 * @brief Form for creating and modifying series.
 */

// $Id$


import('lib.pkp.classes.form.Form');

class SeriesForm extends Form {
	/** @var $seriesId int The ID of the series being edited */
	var $seriesId;

	/** @var $includeSeriesEditor object Additional series editor to
	 *       include in assigned list for this series
	 */
	var $includeSeriesEditor;

	/** @var $omitSeriesEditor object Assigned series editor to omit from
	 *       assigned list for this series
	 */
	var $omitSeriesEditor;

	/** @var $seriesEditors array List of user objects representing the
	 *       available series editors for this press.
	 */
	var $seriesEditors;

	/**
	 * Get the names of fields for which localized data is allowed.
	 * @return array
	 */
	function getLocaleFieldNames() {
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		return $seriesDao->getLocaleFieldNames();
	}

	/**
	 * When displaying the form, include the specified series editor
	 * in the assigned list for this series.
	 * @param $seriesEditorId int
	 */
	function includeSeriesEditor($seriesEditorId) {
		foreach ($this->seriesEditors as $key => $junk) {
			if ($this->seriesEditors[$key]->getId() == $seriesEditorId) {
				$this->includeSeriesEditor =& $this->seriesEditors[$key];
			}
		}
	}

	/**
	 * When displaying the form, omit the specified series editor from
	 * the assigned list for this series.
	 */
	function omitSeriesEditor($seriesEditorId) {
		foreach ($this->seriesEditors as $key => $junk) {
			if ($this->seriesEditors[$key]->getId() == $seriesEditorId) {
				$this->omitSeriesEditor =& $this->seriesEditors[$key];
			}
		}
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		$press =& Request::getPress();
		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		if (isset($this->seriesId)) {
			$seriesDao =& DAORegistry::getDAO('SeriesDAO');
			$series =& $seriesDao->getById($this->seriesId, $press->getId());

			if ($series == null) {
				unset($this->seriesId);
			} else {
				$this->_data = array(
					'seriesId' => $this->seriesId,
					'title' => $series->getTitle(null), // Localized
					'abbrev' => $series->getAbbrev(null), // Localized
					'reviewFormId' => $series->getReviewFormId(),
					'metaIndexed' => !$series->getMetaIndexed(), // #2066: Inverted
					'editorRestriction' => $series->getEditorRestricted(),
					'hideAbout' => $series->getHideAbout(),
					'disableComments' => $series->getDisableComments(),
					'policy' => $series->getPolicy(null), // Localized
					'assignedEditors' => $seriesEditorsDao->getEditorsBySeriesId($this->seriesId, $press->getId()),
					'unassignedEditors' => $seriesEditorsDao->getEditorsNotInSeries($press->getId(), $this->seriesId)
				);
			}
		} else {
			$this->_data = array(
				'unassignedEditors' => $seriesEditorsDao->getEditorsNotInSeries($press->getId(), null)
			);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {

		$this->readUserVars(array('title', 'abbrev', 'policy', 'reviewFormId', 'metaIndexed', 'editorRestriction', 'hideAbout', 'disableComments', 'seriesType'));

		$assignedEditorIds = Request::getUserVar('assignedEditorIds');
		if (empty($assignedEditorIds)) $assignedEditorIds = array();
		elseif (!is_array($assignedEditorIds)) $assignedEditorIds = array($assignedEditorIds);

		$assignedEditors = $unassignedEditors = array();

		foreach ($this->seriesEditors as $key => $junk) {
			$seriesEditor =& $this->seriesEditors[$key]; // Ref
			$userId = $seriesEditor->getId();

			$isIncludeEditor = $this->includeSeriesEditor && $this->includeSeriesEditor->getId() == $userId;
			$isOmitEditor = $this->omitSeriesEditor && $this->omitSeriesEditor->getId() == $userId;
			if ((in_array($userId, $assignedEditorIds) || $isIncludeEditor) && !$isOmitEditor) {
				$assignedEditors[] = array(
					'user' => &$seriesEditor,
					'canReview' => (Request::getUserVar('canReview' . $userId)?1:0),
					'canEdit' => (Request::getUserVar('canEdit' . $userId)?1:0)
				);
			} else {
				$unassignedEditors[] =& $seriesEditor;
			}

			unset($seriesEditor);
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

		$reviewForms =& $reviewFormDao->getActiveByAssocId(ASSOC_TYPE_PRESS, $press->getId());
		$reviewFormOptions = array();
		while ($reviewForm =& $reviewForms->next()) {
			$reviewFormOptions[$reviewForm->getId()] = $reviewForm->getLocalizedTitle();
		}

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('seriesId', $this->seriesId);
		$templateMgr->assign('commentsEnabled', $press->getSetting('enableComments'));
	//	$templateMgr->assign_by_ref('reviewFormOptions', $reviewFormOptions);
		parent::display();
	}

	/**
	 * Save series.
	 */
	function execute() {
		$press =& Request::getPress();
		$pressId = $press->getId();

		$seriesDao =& DAORegistry::getDAO('SeriesDAO');

		if (isset($this->seriesId)) {
			$series =& $seriesDao->getById($this->seriesId, $pressId);
		}

		if (!isset($series)) {
			$series = new Series();
			$series->setPressId($pressId);
			$series->setSequence(REALLY_BIG_NUMBER);
		}

		$series->setTitle($this->getData('title'), null); // Localized
		$series->setAbbrev($this->getData('abbrev'), null); // Localized
		$reviewFormId = $this->getData('reviewFormId');
		if ($reviewFormId === '') $reviewFormId = null;
		$series->setReviewFormId($reviewFormId);
		$series->setMetaIndexed($this->getData('metaIndexed') ? 0 : 1);
		$series->setEditorRestricted($this->getData('editorRestriction') ? 1 : 0);
		$series->setHideAbout($this->getData('hideAbout') ? 1 : 0);
		$series->setDisableComments($this->getData('disableComments') ? 1 : 0);
		$series->setPolicy($this->getData('policy'), null); // Localized
		$series->setType($this->getData('seriesType'));

		if (isset($this->seriesId)) {
			$seriesDao->updateObject($series);
			$seriesId = $series->getId();

		} else {
			$seriesId = $seriesDao->insertObject($series);
			$seriesDao->resequence($series->getType());
		}

		$this->seriesId = $seriesId;
		// Save assigned editors
		$assignedEditorIds = Request::getUserVar('assignedEditorIds');
		if (empty($assignedEditorIds)) $assignedEditorIds = array();
		elseif (!is_array($assignedEditorIds)) $assignedEditorIds = array($assignedEditorIds);
		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		$seriesEditorsDao->deleteEditorsBySeriesId($seriesId, $pressId);
		foreach ($this->seriesEditors as $key => $junk) {
			$seriesEditor =& $this->seriesEditors[$key];
			$userId = $seriesEditor->getId();
			// We don't have to worry about omit- and include-
			// Series editors because this function is only called
			// when the Save button is pressed and those are only
			// used in other cases.
			if (in_array($userId, $assignedEditorIds)) $seriesEditorsDao->insertEditor(
				$pressId,
				$seriesId,
				$userId,
				Request::getUserVar('canReview' . $userId),
				Request::getUserVar('canEdit' . $userId)
			);
			unset($seriesEditor);
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