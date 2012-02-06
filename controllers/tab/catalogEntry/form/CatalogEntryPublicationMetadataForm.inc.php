<?php

/**
 * @file controllers/tab/catalogEntry/form/CatalogEntryPublicationMetadataForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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

	/** The current stage id **/
	var $_stageId;

	/** The assigned publication format id **/
	var $_assignedPublicationFormatId;

	/** is this a physical, non-digital format? **/
	var $_isPhysicalFormat;

	/**
	 * Parameters to configure the form template.
	 */
	var $_formParams;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $assignedPublicationFormat integer
	 * @param $isPhysicalFormat integer
	 * @param $stageId integer
	 * @param $formParams array
	 */
	function CatalogEntryPublicationMetadataForm($monographId, $assignedPublicationFormatId, $isPhysicalFormat = true, $stageId = null, $formParams = null) {
		parent::Form('catalog/form/publicationMetadataFormFields.tpl');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph = $monographDao->getById((int) $monographId);
		if ($monograph) {
			$this->_monograph = $monograph;
		}

		$this->_stageId = $stageId;
		$this->_assignedPublicationFormatId = $assignedPublicationFormatId;
		$this->_isPhysicalFormat = $isPhysicalFormat;
		$this->_formParams = $formParams;

		$this->addCheck(new FormValidator($this, 'productAvailabilityCode', 'required', 'grid.catalogEntry.productAvailabilityRequired'));
		$this->addCheck(new FormValidator($this, 'productCompositionCode', 'required', 'grid.catalogEntry.productCompositionRequired'));
		$this->addCheck(new FormValidatorPost($this));
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
		$templateMgr->assign('assignedPublicationFormatId', (int) $this->getAssignedPublicationFormatId());
		$templateMgr->assign('isPhysicalFormat', (int) $this->getPhysicalFormat()); // included to load format-specific template
		$templateMgr->assign('stageId', $this->getStageId());
		$templateMgr->assign('formParams', $this->getFormParams());

		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');

		// get the lists associated with the select elements on these publication format forms.

		$codes = array(
				'productCompositionCodes' => 'List2', // single item, multiple item, trade-only, etc
				'measurementUnitCodes' => 'List50', // grams, inches, millimeters
				'weightUnitCodes' => 'List95', // pounds, grams, ounces
				'measurementTypeCodes' => 'List48', // height, width, depth
				'productFormDetailCodes' => 'List175', // refinement of product form (SACD, Mass market (rack) paperback, etc)
				'productAvailabilityCodes' => 'List65', // Available, In Stock, Print On Demand, Not Yet Available, etc
				'technicalProtectionCodes' => 'List144', // None, DRM, Apple DRM, etc
				'returnableIndicatorCodes' => 'List66', // No, not returnable, Yes, full copies only, (required for physical items only)
				'countriesIncludedCodes' => 'List91', // country region codes
				);

		foreach ($codes as $templateVarName => $list) {
			$templateMgr->assign_by_ref($templateVarName, $onixCodelistItemDao->getCodes($list));
		}

		$assignedPublicationFormatId =& $this->getAssignedPublicationFormatId();
		$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
		$assignedPublicationFormat =& $assignedPublicationFormatDao->getById($assignedPublicationFormatId);

		if ($assignedPublicationFormat) {
			// assign template variables, provide defaults for new formats
			$templateMgr->assign('fileSize', $assignedPublicationFormat->getFileSize());
			$templateMgr->assign('frontMatter', $assignedPublicationFormat->getFrontMatter());
			$templateMgr->assign('backMatter', $assignedPublicationFormat->getBackMatter());
			$templateMgr->assign('height', $assignedPublicationFormat->getHeight());
			$templateMgr->assign('heightUnitCode', $assignedPublicationFormat->getHeightUnitCode() != '' ? $assignedPublicationFormat->getHeightUnitCode() : 'mm');
			$templateMgr->assign('width', $assignedPublicationFormat->getWidth());
			$templateMgr->assign('widthUnitCode', $assignedPublicationFormat->getWidthUnitCode() != '' ? $assignedPublicationFormat->getWidthUnitCode() : 'mm');
			$templateMgr->assign('thickness', $assignedPublicationFormat->getThickness());
			$templateMgr->assign('thicknesUnitCode', $assignedPublicationFormat->getThicknessUnitCode() != '' ? $assignedPublicationFormat->getThicknessUnitCode() : 'mm');
			$templateMgr->assign('weight', $assignedPublicationFormat->getWeight());
			$templateMgr->assign('weightUnitCode', $assignedPublicationFormat->getWeightUnitCode() != '' ? $assignedPublicationFormat->getWeightUnitCode() : 'gr');
			$templateMgr->assign('productCompositionCode', $assignedPublicationFormat->getProductCompositionCode());
			$templateMgr->assign('productFormDetailCode', $assignedPublicationFormat->getProductFormDetailCode());
			$templateMgr->assign('countryManufactureCode', $assignedPublicationFormat->getCountryManufactureCode() != '' ? $assignedPublicationFormat->getCountryManufactureCode() : 'CA');
			$templateMgr->assign('imprint', $assignedPublicationFormat->getImprint());
			$templateMgr->assign('productAvailabilityCode', $assignedPublicationFormat->getProductAvailabilityCode() != '' ? $assignedPublicationFormat->getProductAvailabilityCode() : '20');
			$templateMgr->assign('technicalProtectionCode', $assignedPublicationFormat->getTechnicalProtectionCode() != '' ? $assignedPublicationFormat->getTechnicalProtectionCode() : '00');
			$templateMgr->assign('returnableIndicatorCode', $assignedPublicationFormat->getReturnableIndicatorCode() != '' ? $assignedPublicationFormat->getReturnableIndicatorCode() : 'Y');
		}

		return parent::fetch($request);
	}

	/**
	 * Initialize form data for an instance of this form.
	 */
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
		$vars = array(
					'fileSize',
					'frontMatter',
					'backMatter',
					'height',
					'heightUnitCode',
					'width',
					'widthUnitCode',
					'thickness',
					'thicknessUnitCode',
					'weight',
					'weightUnitCode',
					'productCompositionCode',
					'productFormDetailCode',
					'countryManufactureCode',
					'imprint',
					'productAvailabilityCode',
					'technicalProtectionCode',
					'returnableIndicatorCode'
				);
		$this->readUserVars($vars);
	}

	/**
	 * Save the metadata and store the catalog data for this specific publication format.
	 */
	function execute() {
		parent::execute();

		$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
		$assignedPublicationFormat =& $assignedPublicationFormatDao->getById($this->getAssignedPublicationFormatId());

		// populate the published monograph with the cataloging metadata
		if (isset($assignedPublicationFormat)) {
			$assignedPublicationFormat->setFileSize($this->getData('fileSize'));
			$assignedPublicationFormat->setFrontMatter($this->getData('frontMatter'));
			$assignedPublicationFormat->setBackMatter($this->getData('backMatter'));
			$assignedPublicationFormat->setHeight($this->getData('height'));
			$assignedPublicationFormat->setHeightUnitCode($this->getData('heightUnitCode'));
			$assignedPublicationFormat->setWidth($this->getData('width'));
			$assignedPublicationFormat->setWidthUnitCode($this->getData('widthUnitCode'));
			$assignedPublicationFormat->setThickness($this->getData('thickness'));
			$assignedPublicationFormat->setThicknessUnitCode($this->getData('thicknessUnitCode'));
			$assignedPublicationFormat->setWeight($this->getData('weight'));
			$assignedPublicationFormat->setWeightUnitCode($this->getData('weightUnitCode'));
			$assignedPublicationFormat->setProductCompositionCode($this->getData('productCompositionCode'));
			$assignedPublicationFormat->setProductFormDetailCode($this->getData('productFormDetailCode'));
			$assignedPublicationFormat->setCountryManufactureCode($this->getData('countryManufactureCode'));
			$assignedPublicationFormat->setImprint($this->getData('imprint'));
			$assignedPublicationFormat->setProductAvailabilityCode($this->getData('productAvailabilityCode'));
			$assignedPublicationFormat->setTechnicalProtectionCode($this->getData('technicalProtectionCode'));
			$assignedPublicationFormat->setReturnableIndicatorCode($this->getData('returnableIndicatorCode'));

			$assignedPublicationFormatDao->updateObject($assignedPublicationFormat);
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
	 * Get the stage id
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get physical format setting
	 * @return int
	 */
	function getPhysicalFormat() {
		return $this->_isPhysicalFormat;
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
