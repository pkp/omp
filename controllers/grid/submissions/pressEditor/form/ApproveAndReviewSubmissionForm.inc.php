<?php

/**
 * @file controllers/grid/submissions/pressEditor/form/ApproveAndReviewSubmissionForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApproveAndReviewSubmissionForm
 * @ingroup controllers_grid_submissions_pressEditor
 *
 * @brief Form for approving a submission
 */

import('lib.pkp.classes.form.Form');

class ApproveAndReviewSubmissionForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function ApproveAndReviewSubmissionForm($monographId) {
		parent::Form('controllers/grid/submissions/pressEditor/approveAndReview.tpl');
		$this->_monographId = (int) $monographId;

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Template methods from Form
	//

	/**
	 * Display the form.
	 */
	function display(&$request, $fetch = true) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $this->_monographId);

		return parent::display($request, $fetch);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('selectedFiles'));
	}

	/**
	 * Save submissionContributor
	 */
	function execute() {
		// TODO:
		// 1. Accept review
		// 2. Get selected files and put in DB somehow
		// 3. Send Personal message to author
	}
}

?>
