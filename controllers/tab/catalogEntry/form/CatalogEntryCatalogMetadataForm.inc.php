<?php

/**
 * @file controllers/tab/catalogEntry/form/CatalogEntryCatalogMetadataForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryCatalogMetadataForm
 * @ingroup controllers_tab_catalogEntry_form_CatalogEntryCatalogMetadataForm
 *
 * @brief Displays a submission's catalog metadata entry form.
 */

import('lib.pkp.classes.form.Form');

class CatalogEntryCatalogMetadataForm extends Form {

	/** The monograph used to show metadata information **/
	var $_monograph;

	/** The published monograph associated with this monograph **/
	var $_publishedMonograph;

	/** The current stage id **/
	var $_stageId;

	/**
	 * Parameters to configure the form template.
	 */
	var $_formParams;

	/**
	 * Constructor.
	 * @param $monographId integer
	 * @param $stageId integer
	 * @param $formParams array
	 */
	function CatalogEntryCatalogMetadataForm($monographId, $stageId = null, $formParams = null) {
		parent::Form('catalog/form/catalogMetadataFormFields.tpl');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph = $monographDao->getById((int) $monographId);
		if ($monograph) {
			$this->_monograph = $monograph;
		}

		$this->_stageId = $stageId;
		$this->_formParams = $formParams;
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

		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');

		// get the lists associated with the select elements on this form
		$audienceCodes =& $onixCodelistItemDao->getCodes('List28');
		$audienceRangeQualifiers =& $onixCodelistItemDao->getCodes('List30');
		$audienceRanges =& $onixCodelistItemDao->getCodes('List77');

		// assign these lists to the form for select options
		$templateMgr->assign('audienceCodes', $audienceCodes);
		$templateMgr->assign('audienceRangeQualifiers', $audienceRangeQualifiers);
		$templateMgr->assign('audienceRanges', $audienceRanges);

		$publishedMonograph =& $this->getPublishedMonograph();
		if ($publishedMonograph) {
			// pre-select the existing values on the form.
			$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
			foreach ($publishedMonographDao->getAdditionalFieldNames() as $fieldName) {
				$templateMgr->assign($fieldName, $publishedMonograph->getData($fieldName));
			}
		}

		return parent::fetch($request);
	}

	function initData() {
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APPLICATION_COMMON,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_OMP_SUBMISSION
		);

		$monograph =& $this->getMonograph();
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$this->_publishedMonograph =& $publishedMonographDao->getById($monograph->getId());
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
	 * Get the PublishedMonograph
	 * @return PublishedMonograph
	 */
	function getPublishedMonograph() {
		return $this->_publishedMonograph;
	}

	/**
	 * Get the stage id
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

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$vars = array(
			'audience', 'audienceRangeQualifier', 'audienceRangeFrom', 'audienceRangeTo', 'audienceRangeExact', 'defaultPublisher'
		);

		$this->readUserVars($vars);
	}

	/**
	 * Save the metadata and store the catalog data for this published monograph.
	 */
	function execute() {

		parent::execute();

		$monograph =& $this->getMonograph();
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph =& $publishedMonographDao->getById($monograph->getId());
		$isExistingEntry = $publishedMonograph?true:false;

		// populate the published monograph with the cataloging metadata
		if ($isExistingEntry) {
			foreach ($publishedMonographDao->getAdditionalFieldNames() as $fieldName) {
				$publishedMonograph->setData($fieldName, $this->getData($fieldName));
			}

			$publishedMonographDao->updateLocaleFields($publishedMonograph);
		} else {
			fatalError('Updating catalog metadata with no published monograph!');
		}
	}
}

?>
