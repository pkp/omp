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
	function AuthorSubmitStep3Form() {
		parent::AuthorSubmitForm();

	}

	/**
	 * Initialize form data from current article.
	 */
	function initData() {
		if (isset($this->sequence->monograph)) {
			$monograph =& $this->sequence->monograph;
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

	/*	// Get supplementary files for this article
		$articleFileDao =& DAORegistry::getDAO('ArticleFileDAO');
		if ($this->article->getSubmissionFileId() != null) {
			$templateMgr->assign_by_ref('submissionFile', $articleFileDao->getArticleFile($this->article->getSubmissionFileId()));
		}*/
		parent::display();
	}

	/**
	 * Upload the submission file.
	 * @param $fileName string
	 * @return boolean
	 */
	function uploadSubmissionFile($fileName) {
		import('file.ManuscriptFileManager');

		$manuscriptFileManager =& new ManuscriptFileManager($this->sequence->monograph->getMonographId());
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		if ($manuscriptFileManager->uploadedFileExists($fileName)) {
			// upload new submission file, overwriting previous if necessary
			$submissionFileId = $manuscriptFileManager->uploadSubmissionFile($fileName, $this->sequence->monograph->getSubmissionFileId(), true);
		}

		if (isset($submissionFileId)) {
			$this->sequence->monograph->setSubmissionFileId($submissionFileId);
			return $monographDao->updateMonograph($this->sequence->monograph);

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
		$monograph =& $this->sequence->monograph;

		if ($monograph->getSubmissionProgress() <= $this->sequence->currentStep) {
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress($this->sequence->currentStep + 1);
			$monographDao->updateMonograph($monograph);
		}

		return $this->sequence->monograph->getMonographId();
	}

}

?>
