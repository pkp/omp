<?php

/**
 * @defgroup submission_form
 */

/**
 * @file classes/submission/form/SubmissionSubmitForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitForm
 * @ingroup submission_form
 *
 * @brief Base class for author submit forms.
 */


import('lib.pkp.classes.form.Form');

class SubmissionSubmitForm extends Form {
	/** @var $press Press */
	var $press;

	/** @var int the ID of the monograph */
	var $monographId;

	/** @var Monograph current monograph */
	var $monograph;

	/** @var int the current step */
	var $step;

	/**
	 * Constructor.
	 * @param $monograph object
	 * @param $step int
	 */
	function SubmissionSubmitForm($press, $monograph, $step) {
		parent::Form(sprintf('submission/form/step%d.tpl', $step));
		$this->addCheck(new FormValidatorPost($this));
		$this->step = (int) $step;
		$this->monograph = $monograph;
		$this->monographId = $monograph ? $monograph->getId() : null;
		$this->press =& $press;
	}

	/**
	 * Display the form.
	 */
	function display($request = null) {
		$templateMgr =& TemplateManager::getManager($request);

		$templateMgr->assign('monographId', $this->monographId);
		$templateMgr->assign('submitStep', $this->step);

		if (isset($this->monograph)) {
			$submissionProgress = $this->monograph->getSubmissionProgress();
		} else {
			$submissionProgress = 1;
		}
		$templateMgr->assign('submissionProgress', $submissionProgress);

		switch($this->step) {
			case 3:
				$helpTopicId = 'submission.indexingAndMetadata';
				break;
			default:
				$helpTopicId = 'submission.index';
		}
		$templateMgr->assign('helpTopicId', $helpTopicId);

		parent::display($request);
	}
}

?>
