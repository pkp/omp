<?php

/**
 * @file controllers/modals/submissionMetadata/form/SubmissionMetadataViewForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionMetadataViewForm
 * @ingroup controllers_modals_submissionMetadata_form_SubmissionMetadataViewForm
 *
 * @brief Displays a submission's metadata view.
 */

import('lib.pkp.controllers.modals.submissionMetadata.form.PKPSubmissionMetadataViewForm');

class SubmissionMetadataViewForm extends PKPSubmissionMetadataViewForm {

	/**
	 * Constructor.
	 * @param $submissionId integer
	 * @param $stageId integer
	 * @param $formParams array
	 */
	function __construct($submissionId, $stageId = null, $formParams = null, $templateName = 'controllers/modals/submissionMetadata/form/submissionMetadataViewForm.tpl') {
		parent::__construct($submissionId, $stageId, $formParams, $templateName);
	}

	/**
	 * @copydoc PKPSubmissionMetadataViewForm::fetch
	 */
	function fetch($request, $template = null, $display = false) {
		$submission = $this->getSubmission();
		$templateMgr = TemplateManager::getManager($request);

		// Get series for this press
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$seriesOptions = array('' => __('submission.submit.selectSeries')) + $seriesDao->getTitlesByPressId($submission->getContextId());
		$templateMgr->assign(array(
			'seriesOptions' => $seriesOptions,
			'seriesId' => $submission->getSeriesId(),
			'seriesPosition' => $submission->getSeriesPosition(),
		));

		return parent::fetch($request, $template, $display);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		parent::readInputData();
		$this->readUserVars(array('seriesId', 'seriesPosition'));
	}

	/**
	 * Save changes to submission.
	 */
	function execute() {
		parent::execute();
		$submission = $this->getSubmission();
		$submissionDao = Application::getSubmissionDAO();

		// Clean any new release or feature object that may
		// exist associated with the current submission series.
		$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /* @var $newReleaseDao NewReleaseDAO */
		$newReleaseDao->deleteNewRelease($submission->getId(), ASSOC_TYPE_SERIES, $submission->getSeriesId());

		$featureDao = DAORegistry::getDAO('FeatureDAO'); /* @var $featureDao FeatureDAO */
		$featureDao->deleteFeature($submission->getId(), ASSOC_TYPE_SERIES, $submission->getSeriesId());

		$submission->setSeriesId($this->getData('seriesId'));
		$submission->setSeriesPosition($this->getData('seriesPosition'));
		$submissionDao->updateObject($submission);

		if ($submission->getDatePublished()) {
			import('classes.search.MonographSearchIndex');
			MonographSearchIndex::indexMonographMetadata($submission);
		}
	}
}
