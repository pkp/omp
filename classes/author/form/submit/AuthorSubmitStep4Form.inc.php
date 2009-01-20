<?php

/**
 * @file classes/author/form/submit/AuthorSubmitStep4Form.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep4Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 4 of author article submission.
 */

// $Id$


import('author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep4Form extends AuthorSubmitForm {
	/**
	 * Constructor.
	 */
	function AuthorSubmitStep4Form() {
		parent::AuthorSubmitForm();
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		// Get supplementary files for this article
	//	$suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
//		$templateMgr->assign_by_ref('suppFiles', $suppFileDao->getSuppFilesByArticle($this->articleId));

		parent::display();
	}

	/**
	 * Save changes to article.
	 */
	function execute() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		// Update article
		$monograph =& $this->sequence->monograph;
		if ($monograph->getSubmissionProgress() <= $this->sequence->currentStep) {
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress($this->sequence->currentStep + 1);
		}
		$monographDao->updateMonograph($monograph);

		return $monograph->getMonographId();
	}
	function getHelpTopicId() {
		return 'submission.supplementaryFiles';
	}
	function getTemplateFile() {
		return 'author/submit/step4.tpl';
	}
}

?>
