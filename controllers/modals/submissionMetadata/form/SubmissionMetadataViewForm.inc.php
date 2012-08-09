<?php

/**
 * @file controllers/modals/submissionMetadata/form/SubmissionMetadataViewForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionMetadataViewForm
 * @ingroup controllers_modals_submissionMetadata_form_SubmissionMetadataViewForm
 *
 * @brief Displays a submission's metadata view.
 */

import('lib.pkp.classes.form.Form');

// Use this class to handle the submission metadata.
import('classes.submission.SubmissionMetadataFormImplementation');

class SubmissionMetadataViewForm extends Form {

	/** The monograph used to show metadata information **/
	var $_monograph;

	/** The current stage id **/
	var $_stageId;

	/**
	 * Parameters to configure the form template.
	 */
	var $_formParams;

	/** @var SubmissionMetadataFormImplementation */
	var $_metadataFormImplem;

	/**
	 * Constructor.
	 * @param $monographId integer
	 * @param $stageId integer
	 * @param $formParams array
	 */
	function SubmissionMetadataViewForm($monographId, $stageId = null, $formParams = null, $templateName = 'controllers/modals/submissionMetadata/form/submissionMetadataViewForm.tpl') {
		parent::Form($templateName);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph = $monographDao->getById((int) $monographId);
		if ($monograph) {
			$this->_monograph = $monograph;
		}

		$this->_stageId = $stageId;

		$this->_formParams = $formParams;

		$this->_metadataFormImplem = new SubmissionMetadataFormImplementation($this);

		// Validation checks for this form
		$this->_metadataFormImplem->addChecks($monograph);
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the Stage Id
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the extra form parameters.
	 */
	function getFormParams() {
		return $this->_formParams;
	}


	//
	// Overridden template methods
	//
	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		$this->_metadataFormImplem->getLocaleFieldNames();
	}

	/**
	 * Initialize form data with the author name and the monograph id.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APPLICATION_COMMON,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_OMP_SUBMISSION
		);

		$this->_metadataFormImplem->initData($this->getMonograph());
	}

	/**
	 * Fetch the HTML contents of the form.
	 * @param $request PKPRequest
	 * return string
	 */
	function fetch(&$request) {
		$monograph =& $this->getMonograph();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monograph->getId());
		$templateMgr->assign('stageId', $this->getStageId());
		$templateMgr->assign('formParams', $this->getFormParams());
		$templateMgr->assign('isEditedVolume', $monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME);
		$templateMgr->assign('isPublished', $monograph->getDatePublished() != null ? true : false);

		// Get series for this press
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesOptions = array('0' => __('submission.submit.selectSeries')) + $seriesDao->getTitlesByPressId($monograph->getPressId());
		$templateMgr->assign('seriesOptions', $seriesOptions);
		$templateMgr->assign('seriesId', $monograph->getSeriesId());
		$templateMgr->assign('seriesPosition', $monograph->getSeriesPosition());

		// If categories are configured for the press, present the LB.
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$templateMgr->assign('categoriesExist', $categoryDao->getCountByPressId($monograph->getPressId()) > 0);

		// also include the categories (for read only form views)
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$assignedCategories =& $monographDao->getCategories($monograph->getId(), $monograph->getPressId());
		$templateMgr->assign('assignedCategories', $assignedCategories->toArray());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->_metadataFormImplem->readInputData();
		$this->readUserVars(array('categories', 'seriesId', 'seriesPosition'));
		ListbuilderHandler::unpack($request, $this->getData('categories'));
	}

	/**
	 * Save changes to monograph.
	 * @param $request PKPRequest
	 */
	function execute($request) {
		$monograph =& $this->getMonograph();
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		// Execute monograph metadata related operations.
		$this->_metadataFormImplem->execute($monograph, $request);
		$monograph->setSeriesId($this->getData('seriesId'));
		$monograph->setSeriesPosition($this->getData('seriesPosition'));
		$monographDao->updateMonograph($monograph);
		if ($monograph->getDatePublished()) {
			import('classes.search.MonographSearchIndex');
			MonographSearchIndex::indexMonographMetadata($monograph);
		}
	}

	/**
	 * Associate a category with a monograph.
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertEntry(&$request, $newRowId) {

		$application =& PKPApplication::getApplication();
		$request =& $application->getRequest();

		$categoryId = $newRowId['name'];
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$press =& $request->getPress();
		$monograph =& $this->getMonograph();

		$category =& $categoryDao->getById($categoryId, $press->getId());
		if (!$category) return true;

		// Associate the category with the monograph
		$monographDao->addCategory(
			$monograph->getId(),
			$categoryId
		);
	}

	/**
	 * Delete a category association.
	 * @see ListbuilderHandler::deleteEntry
	 */
	function deleteEntry(&$request, $rowId) {
		if ($rowId) {
			$categoryDao =& DAORegistry::getDAO('CategoryDAO');
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$category =& $categoryDao->getById($rowId);
			if (!is_a($category, 'Category')) {
				assert(false);
				return false;
			}
			$monograph =& $this->getMonograph();
			$monographDao->removeCategory($monograph->getId(), $rowId);
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
