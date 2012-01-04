<?php

/**
 * @file controllers/grid/users/reviewer/form/SearchByNameReviewerForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SearchByNameReviewerForm
 * @ingroup controllers_grid_users_reviewer_form
 *
 * @brief Form for searching (by name) and adding a reviewer to a submission
 */

import('controllers.grid.users.reviewer.form.ReviewerForm');

class SearchByNameReviewerForm extends ReviewerForm {
	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $reviewRound ReviewRound
	 */
	function SearchByNameReviewerForm(&$monograph, &$reviewRound) {
		parent::ReviewerForm($monograph, $reviewRound);
		$this->setTemplate('controllers/grid/users/reviewer/form/searchByNameReviewerForm.tpl');

		$this->addCheck(new FormValidator($this, 'reviewerId', 'required', 'editor.review.mustSelect'));
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		parent::readInputData();

		$this->readUserVars(array('reviewerId'));
	}

	/**
	 * Fetch the form
	 * @see Form::fetch
	 */
	function fetch(&$request) {
		// Pass along the request vars
		$actionArgs = $request->getUserVars();
		$reviewRound =& $this->getReviewRound();
		$actionArgs['reviewRoundId'] = $reviewRound->getId();
		$actionArgs['selectionType'] = REVIEWER_SELECT_ADVANCED_SEARCH;
		// but change the selectionType for each action
		$advancedSearchAction = new LinkAction(
									'advancedSearch',
									new AjaxAction($request->url(null, null, 'reloadReviewerForm', null, $actionArgs)),
									__('manager.reviewerSearch.advancedSearch.short'),
									'user_search'
								);
		$this->setReviewerFormAction($advancedSearchAction);
		$actionArgs['selectionType'] = REVIEWER_SELECT_CREATE;
		// but change the selectionType for each action
		$advancedSearchAction = new LinkAction(
									'selectCreate',
									new AjaxAction($request->url(null, null, 'reloadReviewerForm', null, $actionArgs)),
									__('editor.review.createReviewer'),
									'add_user'
								);
		$this->setReviewerFormAction($advancedSearchAction);
		$actionArgs['selectionType'] = REVIEWER_SELECT_ENROLL_EXISTING;
		// but change the selectionType for each action
		$advancedSearchAction = new LinkAction(
									'enrolExisting',
									new AjaxAction($request->url(null, null, 'reloadReviewerForm', null, $actionArgs)),
									__('editor.review.enrollReviewer.short'),
									'enroll_user'
								);
		$this->setReviewerFormAction($advancedSearchAction);

		return parent::fetch($request);
	}
}

?>
