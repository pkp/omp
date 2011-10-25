<?php

/**
 * @file controllers/modals/submissionMetadata/form/SubmissionMetadataViewForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	function SubmissionMetadataViewForm($monographId, $stageId = null, $formParams = null) {
		parent::Form('controllers/modals/submissionMetadata/form/submissionMetadataViewForm.tpl');

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph = $monographDao->getMonograph((int) $monographId);
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
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the Monograph
	 * @return Monograph
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
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_SUBMISSION));

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
		$templateMgr->assign('monographId', $this->getMonograph()->getId());
		$templateMgr->assign('stageId', $this->getStageId());
		$templateMgr->assign('formParams', $this->getFormParams());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->_metadataFormImplem->readInputData();
	}

	/**
	 * Save changes to monograph.
	 */
	function execute() {

		// Execute monograph metadata related operations.
		$this->_metadataFormImplem->execute($this->getMonograph());
	}
}

?>
