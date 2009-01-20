<?php
/**
 * @file AuthorSubmissionSequence.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmissionSequence
 * @ingroup submission
 *
 * @brief Represents a group of author submission forms.
 */

// $Id: 

import('submission.common.SubmissionFormSequence');

class AuthorSubmissionSequence extends SubmissionFormSequence
{
	function AuthorSubmissionSequence($monographId = null) {
		parent::SubmissionFormSequence($monographId);
		$this->prepareFormSequences();
	}
	function display() {
		$templateMgr =& TemplateManager::getManager();

		if (isset($this->monograph)) {
			$templateMgr->assign('submissionProgress', $this->monograph->getSubmissionProgress());
		}
		parent::display();
	}

	function prepareFormSequences() {
		$this->addForm('author.form.submit.AuthorSubmitStep1Form','AuthorSubmitStep1Form','author.submit.start','author.submit.step1','1');
		$this->addForm('author.form.submit.AuthorSubmitStep2aForm','AuthorSubmitStep2aForm','author.submit.metadata','author.submit.step2','2');
		//$this->addForm('author.form.submit.AuthorSubmitStep2bForm','AuthorSubmitStep2bForm','author.submit.metadata','author.submit.step2b','2b','2');
		$this->addForm('author.form.submit.AuthorSubmitStep3Form','AuthorSubmitStep3Form','author.submit.upload','author.submit.step3','3');
		$this->addForm('author.form.submit.AuthorSubmitStep4Form','AuthorSubmitStep4Form','author.submit.supplementaryFiles','author.submit.step4','4');
		$this->addForm('author.form.submit.AuthorSubmitStep5Form','AuthorSubmitStep5Form','author.submit.confirmation','author.submit.step5','5');
	}

	/**
	 * Validation check for submission.
	 * @param $step int
	 */
	function validate($step = false) {

		$user =& Request::getUser();
		$press =& Request::getPress();

		if (!parent::isValidStep($step))
			Request::redirect(null, null, 'submit', array('1'));//intention: replace '1' with something like $this->getFirstForm()->alias

		// Check that monograph exists for this press and user and that submission is incomplete
		if (isset($this->monograph)) {
			if ($this->monograph->getUserId() !== $user->getUserId() || ($step !== false && $step > $this->monograph->getSubmissionProgress())) {
				Request::redirect(null, null, 'submit');
			}
		}
		return array(&$press, &$this->monograph);
		
	}
	
}
?>