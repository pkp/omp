<?php

/**
 * @file controllers/grid/users/reviewerSelect/ReviewerSelectGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSelectGridHandler
 * @ingroup controllers_grid_reviewerSelect
 *
 * @brief Handle reviewer selector grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import submissionContributor grid specific classes
import('controllers.grid.users.reviewerSelect.ReviewerSelectGridCellProvider');
import('controllers.grid.users.reviewerSelect.ReviewerSelectGridRow');

class ReviewerSelectGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function ReviewerSelectGridHandler() {
		parent::GridHandler();
	}

	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::getRemoteOperations()
	 * @return array
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('updateReviewerSelect'));
	}
	
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		$monographId =& $request->getUserVar('monographId');

		$press =& $request->getPress();

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_PKP_SUBMISSION));
		
		// Retrieve the submissionContributors associated with this monograph to be displayed in the grid
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		
		$doneMin = $request->getUserVar('doneMin');
		$doneMax = $request->getUserVar('doneMax');
		$avgMin = $request->getUserVar('avgMin');
		$avgMax = $request->getUserVar('avgMax');
		$lastMin = $request->getUserVar('lastMin');
		$lastMax = $request->getUserVar('lastMax');
		$activeMin = $request->getUserVar('activeMin');
		$activeMax = $request->getUserVar('activeMax');
		$interests = null;
		
		$data =& $seriesEditorSubmissionDao->getFilteredReviewers($press->getId(), $doneMin, $doneMax, $avgMin, $avgMax, $lastMin, $lastMax, $activeMin, $activeMax, $interests);
		$this->setData($data);		
		
		// Columns
		$cellProvider = new ReviewerSelectGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'select',
				'',
				null,
				'controllers/grid/users/reviewerSelect/reviewerSelectRadioButton.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'name',
				'author.users.contributor.name',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'done',
				'common.done',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'avg',
				'editor.review.days',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'last',
				'editor.submissions.lastAssigned',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'active',
				'common.active',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'interests',
				'user.interests',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return ReviewerSelectGridRow
	 */
	function &getRowInstance() {
		// Return a submissionContributor row
		$row = new ReviewerSelectGridRow();
		return $row;
	}

	/**
	 * Get a filtered list of reviewers based on the editor's selections
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function updateReviewerSelect(&$args, &$request) {
		$press =& $request->getPress();
		// Retrieve the filtered list of reviewers
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		
		$doneMin = $request->getUserVar('doneMin');
		$doneMax = $request->getUserVar('doneMax');
		$avgMin = $request->getUserVar('avgMin');
		$avgMax = $request->getUserVar('avgMax');
		$lastMin = $request->getUserVar('lastMin');
		$lastMax = $request->getUserVar('lastMax');
		$activeMin = $request->getUserVar('activeMin');
		$activeMax = $request->getUserVar('activeMax');
		$interests = $request->getUserVar('interests');
		
		$data =& $seriesEditorSubmissionDao->getFilteredReviewers($press->getId(), $doneMin, $doneMax, $avgMin, $avgMax, $lastMin, $lastMax, $activeMin, $activeMax, $interests);
		$this->setData($data);	
		

		// Re-display the grid
		return $this->fetchGrid($args,$request);
	}

}