<?php

/**
 * @file controllers/tab/catalogEntry/form/CatalogEntryPublicationMetadataForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryPublicationMetadataForm
 * @ingroup controllers_tab_catalogEntry_form_CatalogEntryPublicationMetadataForm
 *
 * @brief Parent class for forms used by the various publication formats.
 */

import('lib.pkp.classes.form.Form');

class CatalogEntryPublicationMetadataForm extends Form {

	/** The monograph used to show metadata information **/
	var $_monograph;

	/** The published monograph associated with this monograph **/
	var $_publishedMonograph;

	/** The current stage id **/
	var $_stageId;

	/** The assigned publication format id **/
	var $_assignedPublicationFormatId;

	/** the parent class id for the assigned format this form is showing **/
	var $_formatId;

	/**
	 * Parameters to configure the form template.
	 */
	var $_formParams;

	/**
	 * Constructor.
	 * @param $monographId integer
	 * @param $assignedPublicationFormat integer
	 * @param $formatId integer
	 * @param $stageId integer
	 * @param $formParams array
	 */
	function CatalogEntryPublicationMetadataForm($monographId, $assignedPublicationFormatId, $formatId, $stageId = null, $formParams = null) {
		parent::Form('catalog/form/publicationMetadataFormFields.tpl');

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph = $monographDao->getById((int) $monographId);
		if ($monograph) {
			$this->_monograph = $monograph;
		}

		$this->_stageId = $stageId;
		$this->_assignedPublicationFormatId = $assignedPublicationFormatId;
		$this->_formatId = $formatId;
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
		$templateMgr->assign('assignedPublicationFormatId', (int) $this->getAssignedPublicationFormatId());
		$templateMgr->assign('formatId', (int) $this->getFormatId()); // included to load format-specific template
		$templateMgr->assign('stageId', $this->getStageId());
		$templateMgr->assign('formParams', $this->getFormParams());

		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');

		// get the lists associated with the select elements on these publication format forms.

		$codes = array(
				'productCompositionCodes' => 'List2', // single item, multiple item, trade-only, etc
				'measurementUnitCodes' => 'List50', // grams, inches, millimeters
				'measurementTypeCodes' => 'List48', // height, width, depth
				'productIdentifierTypeCodes' => 'List5', // GTIN-13, UPC, ISBN-10, etc
				'currencyCodes' => 'List96', // GBP, USD, CAD, etc
				'priceTypeCodes' => 'List58', // without tax, with tax, etc
				'extentTypeCodes' => 'List23', // word count, FM page count, BM page count, main page count, etc
				'taxRateCodes' => 'List62', // higher rate, standard rate, zero rate
				'taxTypeCodes' => 'List171', // VAT, GST
				'countriesIncludedCodes' => 'List91', // country region codes
				);

		foreach ($codes as $templateVarName => $list) {
			$templateMgr->assign($templateVarName, $onixCodelistItemDao->getCodes($list));
		}

		// assign sensible defaults to some of these.  They will be overridden below by specific settings in the format
		$templateMgr->assign('currencyCode', 'CAD');
		$templateMgr->assign('taxTypeCode', '02'); // GST
		$templateMgr->assign('countriesIncludedCode', array('CA'));

		$assignedPublicationFormatId =& $this->getAssignedPublicationFormatId();
		$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
		$assignedPublicationFormat =& $assignedPublicationFormatDao->getById($assignedPublicationFormatId);
		if ($assignedPublicationFormat) {
			// pre-select the existing values on the form.
			foreach ($assignedPublicationFormatDao->getAdditionalFieldNames() as $fieldName) {
				$templateMgr->assign($fieldName, $assignedPublicationFormat->getData($fieldName));
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
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$vars = array('productIdentifier', 'productIdentifierTypeCode', 'productCompositionCode', 'price', 'priceTypeCode', 'taxRateCode', 'taxTypeCode', 'countriesIncludedCode');
		$this->readUserVars($vars);
	}

	/**
	 * Save the metadata and store the catalog data for this specific publication format.
	 */
	function execute() {

		parent::execute();

		$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
		$assignedPublicationFormat =& $assignedPublicationFormatDao->getById($this->getAssignedPublicationFormatId());
		$isExistingEntry = $assignedPublicationFormat?true:false;

		// populate the published monograph with the cataloging metadata
		if ($isExistingEntry) {
			foreach ($assignedPublicationFormatDao->getAdditionalFieldNames() as $fieldName) {
				$assignedPublicationFormat->setData($fieldName, $this->getData($fieldName));
			}

			$assignedPublicationFormatDao->updateLocaleFields($assignedPublicationFormat);
		} else {
			fatalError('No valid assigned publication format!');
		}
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
	 * Get the format id
	 * @return int
	 */
	function getFormatId() {
		return $this->_formatId;
	}
	/**
	 * Get the assigned publication format id
	 * @return int
	 */
	function getAssignedPublicationFormatId() {
		return $this->_assignedPublicationFormatId;
	}

	/**
	 * Get the extra form parameters.
	 */
	function getFormParams() {
		return $this->_formParams;
	}
}

?>
