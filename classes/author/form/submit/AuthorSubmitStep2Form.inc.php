<?php

/**
 * @file classes/author/form/submit/AuthorSubmitStep2Form.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep2Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 2 of author manuscript submission.
 */

// $Id$


import('classes.author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep2Form extends AuthorSubmitForm {

	/**
	 * Constructor.
	 */
	function AuthorSubmitStep2Form($monograph) {
		parent::AuthorSubmitForm($monograph, 2);

		// Validation checks for this form
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		if (isset($this->monograph)) {
			$monograph =& $this->monograph;
			$this->_data = array('monographId' => $monograph->getId()
			);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array(
			)
		);
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $this->monograph->getId());
		// Get supplementary files for this monograph
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		if ($this->monograph->getSubmissionFileId() != null) {
			$templateMgr->assign_by_ref('submissionFile', $monographFileDao->getMonographFile($this->monograph->getSubmissionFileId()));
		}
		parent::display();
	}

	/**
	 * Save changes to monograph.
	 * @return int the monograph ID
	 */
	function execute() {
		// Update monograph
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $this->monograph;

		if ($monograph->getSubmissionProgress() <= $this->step) {
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress($this->step + 1);
			$monographDao->updateMonograph($monograph);
		}

		return $this->monographId;
	}

}

?>
