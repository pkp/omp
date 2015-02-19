<?php

/**
 * @file controllers/modals/submissionMetadata/form/SubmissionMetadataViewForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	function SubmissionMetadataViewForm($submissionId, $stageId = null, $formParams = null, $templateName = 'controllers/modals/submissionMetadata/form/submissionMetadataViewForm.tpl') {
		parent::PKPSubmissionMetadataViewForm($submissionId, $stageId, $formParams, $templateName);
	}

	/**
	 * Fetch the HTML contents of the form.
	 * @param $request PKPRequest
	 * return string
	 */
	function fetch($request) {
		$submission = $this->getSubmission();
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('isEditedVolume', $submission->getWorkType() == WORK_TYPE_EDITED_VOLUME);

		// Get series for this press
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$seriesOptions = array('0' => __('submission.submit.selectSeries')) + $seriesDao->getTitlesByPressId($submission->getContextId());
		$templateMgr->assign('seriesOptions', $seriesOptions);
		$templateMgr->assign('seriesId', $submission->getSeriesId());
		$templateMgr->assign('seriesPosition', $submission->getSeriesPosition());

		// If categories are configured for the press, present the LB.
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$templateMgr->assign('categoriesExist', $categoryDao->getCountByPressId($submission->getContextId()) > 0);

		// also include the categories (for read only form views)
		$submissionDao = Application::getSubmissionDAO();
		$assignedCategories = $submissionDao->getCategories($submission->getId(), $submission->getContextId());
		$templateMgr->assign('assignedCategories', $assignedCategories->toArray());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		parent::readInputData();
		$this->readUserVars(array('categories', 'seriesId', 'seriesPosition'));
		$application = PKPApplication::getApplication();
		$request = $application->getRequest();
		ListbuilderHandler::unpack($request, $this->getData('categories'));
	}

	/**
	 * Save changes to submission.
	 * @param $request PKPRequest
	 */
	function execute($request) {
		parent::execute($request);
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

	/**
	 * Associate a category with a submission.
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertEntry($request, $newRowId) {

		$application = PKPApplication::getApplication();
		$request = $application->getRequest();

		$categoryId = $newRowId['name'];
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$submissionDao = Application::getSubmissionDAO();
		$context = $request->getContext();
		$submission = $this->getSubmission();

		$category = $categoryDao->getById($categoryId, $context->getId());
		if (!$category) return true;

		// Associate the category with the submission
		$submissionDao->addCategory(
			$submission->getId(),
			$categoryId
		);
	}

	/**
	 * Delete a category association.
	 * @see ListbuilderHandler::deleteEntry
	 */
	function deleteEntry($request, $rowId) {
		if ($rowId) {
			$categoryDao = DAORegistry::getDAO('CategoryDAO');
			$submissionDao = Application::getSubmissionDAO();
			$category = $categoryDao->getById($rowId);
			if (!is_a($category, 'Category')) {
				assert(false);
				return false;
			}
			$submission = $this->getSubmission();
			$submissionDao->removeCategory($submission->getId(), $rowId);
		}

		return true;
	}

	/**
	 * Update a category association.
	 * @see ListbuilderHandler::updateEntry
	 */
	function updateEntry($request, $rowId, $newRowId) {

		$this->deleteEntry($request, $rowId);
		$this->insertEntry($request, $newRowId);
	}
}

?>
