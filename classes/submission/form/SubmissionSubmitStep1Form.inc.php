<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep1Form.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep1Form
 * @ingroup submission_form
 *
 * @brief Form for Step 1 of author submission.
 */

import('lib.pkp.classes.submission.form.PKPSubmissionSubmitStep1Form');
import('classes.monograph.Monograph'); // WORK_TYPE_... constants for form

class SubmissionSubmitStep1Form extends PKPSubmissionSubmitStep1Form {
	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep1Form($context, $submission = null) {
		parent::PKPSubmissionSubmitStep1Form($context, $submission);
	}

	/**
	 * Fetch the form.
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);

		// Get series for this context
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$seriesOptions = array('0' => __('submission.submit.selectSeries')) + $seriesDao->getTitlesByPressId($this->context->getId(), true);
		$templateMgr->assign('seriesOptions', $seriesOptions);

		return parent::fetch($request);
	}

	/**
	 * Initialize form data from current submission.
	 */
	function initData() {
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
	 * Perform additional validation checks
	 * @copydoc Form::validate
	 */
	function validate() {
		if (!parent::validate()) return false;

		// Validate that the series ID is attached to this press.
		if ($seriesId = $this->getData('seriesId')) {
			$request = Application::getRequest();
			$context = $request->getContext();
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$series = $seriesDao->getById($seriesId, $context->getId());
			if (!$series) return false;
		}

		return true;
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

?>
