<?php

/**
 * @file classes/author/form/submit/AuthorSubmitStep3Form.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep3Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 3 of author article submission.
 */

// $Id$


import('author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep3Form extends AuthorSubmitForm {

	/**
	 * Constructor.
	 */
	function AuthorSubmitStep3Form($article) {
		parent::AuthorSubmitForm($article);
	}

	/**
	 * Initialize form data from current article.
	 */
	function initData() {
		if (isset($this->monograph)) {
			$monograph =& $this->monograph;
			$this->_data = array(
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

	function getHelpTopicId() {
		return 'submission.indexingAndMetadata';
	}
	function getTemplateFile() {
		return 'author/submit/step3.tpl';
	}
	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		// Get supplementary files for this article
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		if ($this->monograph->getSubmissionFileId() != null) {
			$templateMgr->assign_by_ref('submissionFile', $monographFileDao->getMonographFile($this->monograph->getSubmissionFileId()));
		}

		if ($this->monograph->getCompletedProspectusFileId() != null) {
			$templateMgr->assign_by_ref('completedProspectusFile', $monographFileDao->getMonographFile($this->monograph->getCompletedProspectusFileId()));
		}

		parent::display();
	}

	/**
	 * Upload the submission file.
	 * @param $fileName string
	 * @return boolean
	 */
	function uploadSubmissionFile($fileName) {
		import('file.MonographFileManager');

		$manuscriptFileManager =& new MonographFileManager($this->monograph->getMonographId());
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		if ($manuscriptFileManager->uploadedFileExists($fileName)) {
			// upload new submission file, overwriting previous if necessary
			$submissionFileId = $manuscriptFileManager->uploadSubmissionFile($fileName, $this->monograph->getSubmissionFileId(), true);
		}

		if (isset($submissionFileId)) {
			$this->monograph->setSubmissionFileId($submissionFileId);
			return $monographDao->updateMonograph($this->monograph);

		} else {
			return false;
		}
	}

	/**
	 * Upload the completed prospectus file.
	 * @param $fileName string
	 * @return boolean
	 */
	function uploadCompletedProspectusFile($fileName) {
		import('file.MonographFileManager');

		$manuscriptFileManager =& new MonographFileManager($this->monograph->getMonographId());
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		if ($manuscriptFileManager->uploadedFileExists($fileName)) {
			// upload new submission file, overwriting previous if necessary
			$prospectusFileId = $manuscriptFileManager->uploadCompletedProspectusFile($fileName, $this->monograph->getCompletedProspectusFileId(), true);
		}

		if (isset($prospectusFileId)) {
			$this->monograph->setCompletedProspectusFileId($prospectusFileId);
			return $monographDao->updateMonograph($this->monograph);

		} else {
			return false;
		}
	}

	/**
	 * Save changes to article.
	 * @return int the article ID
	 */
	function execute() {
		// Update article
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $this->monograph;

		if ($monograph->getSubmissionProgress() <= $this->sequence->currentStep) {
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress($this->sequence->currentStep + 1);
			$monographDao->updateMonograph($monograph);
		}

		return $this->monograph->getMonographId();
	}

}

?>
