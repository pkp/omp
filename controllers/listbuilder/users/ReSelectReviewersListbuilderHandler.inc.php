<?php

/**
 * @file controllers/listbuilder/users/ReSelectReviewersListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReSelectReviewersListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for selecting which reviewers go to the next round of review (set to all reviewers by default)
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class ReSelectReviewersListbuilderHandler extends ListbuilderHandler {
	/**
	 * Constructor
	 */
	function ReSelectReviewersListbuilderHandler() {
		parent::ListbuilderHandler();
	}


	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$userDao =& DAORegistry::getDAO('UserDAO');

		// Retrieve and validate the monograph id
		$monographId =& $request->getUserVar('monographId');
		if (!is_numeric($monographId)) return false;

		// Retrieve the submission associated with this reviewers grid
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($monographId);

		// Get the review round currently being looked at
		$reviewType = $request->getUserVar('reviewType');
		$round = $request->getUserVar('round');

		// Get the existing review assignments for this monograph
		$reviewAssignments =& $seriesEditorSubmission->getReviewAssignments($reviewType, $round);

		$items = array();
		if(isset($reviewAssignments)) {
			foreach ($reviewAssignments as $reviewAssignment) {
				$id = $reviewAssignment->getReviewerId();
				$reviewer =& $userDao->getUser($id);

				$items[$id] = array('item' => $reviewer->getFullName());
				unset($item);
			}
		}
		$this->setData($items);
	}

	/* Get possible items to populate drop-down list with */
	function getPossibleItemList() {
		return $this->possibleItems;
	}

	/* Load possible items to populate drop-down list with
	 * List is null; All reviewers are already selected
	 */
	function loadPossibleItemList(&$request) {
		$this->possibleItems = null;
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic configuration
		$this->setSourceTitle('user.role.reviewers');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT); // Drop-down select
		$this->setListTitle('user.role.reviewers');

		$this->loadList($request);
		$this->loadPossibleItemList($request);

		$this->addColumn(new GridColumn('item', 'common.name'));
	}

	/**
	 * Need to add additional data to the template via the fetch method
	 */
	function fetch(&$args, &$request) {
		$router =& $request->getRouter();

		$monographId = $request->getUserVar('monographId');
		$reviewType = $request->getUserVar('reviewType');
		$round = $request->getUserVar('round');

		$additionalVars = array('itemId' => $monographId,
			'addUrl' => $router->url($request, array(), null, 'addItem', null, array('monographId' => $monographId, 'reviewType' => $reviewType, 'round' => $round)),
			'deleteUrl' => $router->url($request, array(), null, 'deleteItems', null, array('monographId' => $monographId, 'reviewType' => $reviewType, 'round' => $round))
		);

		return parent::fetch(&$args, &$request, $additionalVars);
    }

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		parent::setupTemplate();

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_AUTHOR, LOCALE_COMPONENT_PKP_SUBMISSION));
	}

	//
	// Public AJAX-accessible functions
	//

	/*
	 * Handle adding an item to the list
	 */
	function addItem(&$args, &$request) {
		$rowId = "selectList-" . $this->getId();
		$userId = (int) $args[$rowId];

		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $userDao->getUser($userId);
		// Return JSON with formatted HTML to insert into list
		$row =& $this->getRowInstance();
		$row->setGridId($this->getId());
		$row->setId($userId);
		$rowData = array('item' => $user->getFullName());
		$row->setData($rowData);
		$row->initialize($request);

		$json = new JSON('true', $this->_renderRowInternally($request, $row));
		return $json->getString();

	}


	/*
	 * Handle deleting items from the list
	 */
	function deleteItems(&$args, &$request) {
		$json = new JSON('true');
		return $json->getString();
	}
}
?>
