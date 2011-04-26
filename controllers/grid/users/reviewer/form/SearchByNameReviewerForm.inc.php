<?php

/**
 * @file controllers/grid/users/reviewer/form/SearchByNameReviewerForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	 */
	function SearchByNameReviewerForm($monograph, $reviewAssignmentId) {
		parent::ReviewerForm($monograph, $reviewAssignmentId);
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
}

?>
