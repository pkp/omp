<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep1Form.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep1Form
 * @ingroup submission_form
 *
 * @brief Form for Step 1 of author submission.
 */

import('lib.pkp.classes.submission.form.PKPSubmissionSubmitStep1Form');
import('classes.submission.Submission'); // WORK_TYPE_... constants for form

class SubmissionSubmitStep1Form extends PKPSubmissionSubmitStep1Form {
	/**
	 * Constructor.
	 */
	function __construct($context, $submission = null) {
		parent::__construct($context, $submission);
		$this->addCheck(new FormValidatorCustom($this, 'seriesId', 'optional', 'author.submit.seriesRequired', array(DAORegistry::getDAO('SeriesDAO'), 'getById'), array($context->getId())));
	}

	/**
	 * @copydoc PKPSubmissionSubmitStep1Form::fetch
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);

		// Get series for this context
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$seriesOptions = array('' => __('submission.submit.selectSeries')) + $seriesDao->getTitlesByPressId($this->context->getId(), true);
		$templateMgr->assign('seriesOptions', $seriesOptions);

		return parent::fetch($request, $template, $display);
	}

	/**
	 * @copydoc PKPSubmissionSubmitStep1Form::initData
	 */
	function initData($data = array()) {
		if (isset($this->submission)) {
			parent::initData(array(
				'seriesId' => $this->submission->getSeriesId(),
				'seriesPosition' => $this->submission->getSeriesPosition(),
				'workType' => $this->submission->getWorkType(),
			));
		} else {
			parent::initData();
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'workType', 'seriesId', 'seriesPosition',
		));
		parent::readInputData();
	}

	/**
	 * Set the submission data from the form.
	 * @param $submission Submission
	 */
	function setSubmissionData($submission) {
		$submission->setWorkType($this->getData('workType'));
		$submission->setSeriesId($this->getData('seriesId'));
		$submission->setSeriesPosition($this->getData('seriesPosition'));
		parent::setSubmissionData($submission);
	}
}


