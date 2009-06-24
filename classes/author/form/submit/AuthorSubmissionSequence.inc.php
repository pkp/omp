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

// $Id$

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

	/**
	 * Add individual forms to the sequence.
	 */
	function prepareFormSequences() {
		$this->addForm('author.form.submit.AuthorSubmitStep1Form','AuthorSubmitStep1Form','author.submit.start','author.submit.stepHeading.start','1');
		$this->addForm('author.form.submit.AuthorSubmitStep3Form','AuthorSubmitStep3Form','author.submit.upload','author.submit.stepHeading.upload','2');
		$this->addForm('author.form.submit.AuthorSubmitStep2Form','AuthorSubmitStep2Form','author.submit.metadata','author.submit.stepHeading.metadata','3');
		$this->addForm('author.form.submit.AuthorSubmitStep5Form','AuthorSubmitStep5Form','author.submit.confirmation','author.submit.stepHeading.confirmation','4');
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
			if ($this->monograph->getUserId() !== $user->getId() || ($step !== false && $step > $this->monograph->getSubmissionProgress())) {
				Request::redirect(null, null, 'submit');
			}
		}
		return array(&$press, &$this->monograph);
	}
}

?>
