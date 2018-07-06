<?php

/**
 * @file controllers/tab/catalogEntry/form/CatalogEntryFormatMetadataForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryFormatMetadataForm
 * @ingroup controllers_tab_catalogEntry_form
 *
 * @brief Parent class for forms used by the various publication formats.
 */

import('lib.pkp.classes.form.Form');
import('lib.pkp.classes.plugins.PKPPubIdPluginHelper');

class CatalogEntryFormatMetadataForm extends Form {

	/** @var Monograph The monograph used to show metadata information */
	var $_monograph;

	/** @var int The current stage id */
	var $_stageId;

	/** @var PublicationFormat The publication format */
	var $_publicationFormat;

	/** @var PKPPubIdPluginHelper The pub id plugin helper */
	var $_pubIdPluginHelper;

	/** @var boolean is this a physical, non-digital format? */
	var $_isPhysicalFormat;

	/** @var string a remote URL to retrieve the contents in this format */
	var $_remoteURL;

	/** @var array Parameters to configure the form template */
	var $_formParams;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $representationId integer
	 * @param $isPhysicalFormat integer
	 * @param $remoteURL string
	 * @param $stageId integer
	 * @param $formParams array
	 */
	function __construct($monographId, $representationId, $isPhysicalFormat = true, $remoteURL = null, $stageId = null, $formParams = null) {
		parent::__construct('controllers/tab/catalogEntry/form/publicationMetadataFormFields.tpl');
		$monographDao = DAORegistry::getDAO('MonographDAO');
		$this->_monograph = $monographDao->getById($monographId);

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$this->_publicationFormat = $publicationFormatDao->getById($representationId, $monographId);
		assert($this->_publicationFormat);

		$this->_pubIdPluginHelper = new PKPPubIdPluginHelper();

		$this->_stageId = $stageId;
		$this->_isPhysicalFormat = $isPhysicalFormat;
		$this->_remoteURL = $remoteURL;
		$this->_formParams = $formParams;

		$this->addCheck(new FormValidator($this, 'productAvailabilityCode', 'required', 'grid.catalogEntry.productAvailabilityRequired'));
		$this->addCheck(new FormValidatorRegExp($this, 'directSalesPrice', 'optional', 'grid.catalogEntry.validPriceRequired', '/^[0-9]*(\.[0-9]+)?$/'));
		$this->addCheck(new FormValidator($this, 'productCompositionCode', 'required', 'grid.catalogEntry.productCompositionRequired'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * @copydoc Form::fetch
	 */
	function fetch($request, $template = null, $display = false) {
		$monograph = $this->getMonograph();
		$publicationFormat = $this->getPublicationFormat();
		$press = $request->getPress();

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('submissionId', $monograph->getId());
		$templateMgr->assign('representationId', (int) $publicationFormat->getId());

		// included to load format-specific templates
		$templateMgr->assign('isPhysicalFormat', (bool) $this->getPhysicalFormat());
		$templateMgr->assign('remoteURL', $this->getRemoteURL());

		$templateMgr->assign('stageId', $this->getStageId());
		$templateMgr->assign('formParams', $this->getFormParams());

		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');

		// Check if e-commerce is available
		$paymentManager = Application::getPaymentManager($press);
		if ($paymentManager->isConfigured()) {
			$templateMgr->assign('paymentConfigured', true);
			$templateMgr->assign('currency', $press->getSetting('currency'));
		}

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

		// consider public identifiers
		$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
		$templateMgr->assign('pubIdPlugins', $pubIdPlugins);
		$templateMgr->assign('pubObject', $publicationFormat);

		$templateMgr->assign('notificationRequestOptions', array(
			NOTIFICATION_LEVEL_NORMAL => array(
				NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD => array(ASSOC_TYPE_PRESS, $press->getId()),
			),
			NOTIFICATION_LEVEL_TRIVIAL => array()
		));

		return parent::fetch($request, $template, $display);
	}

	/**
	 * Initialize form data for an instance of this form.
	 */
	function initData() {
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APP_COMMON,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_APP_SUBMISSION
		);

		$monograph = $this->getMonograph();
		$publicationFormat = $this->getPublicationFormat();

		$this->_data = array(
			'fileSize' => (boolean) $publicationFormat->getFileSize() ? $publicationFormat->getFileSize() : $publicationFormat->getCalculatedFileSize(),
			'override' => (boolean) $publicationFormat->getData('fileSize') ? true : false,
			'frontMatter' => $publicationFormat->getFrontMatter(),
			'backMatter' => $publicationFormat->getBackMatter(),
			'height' => $publicationFormat->getHeight(),
			'heightUnitCode' => $publicationFormat->getHeightUnitCode() != '' ? $publicationFormat->getHeightUnitCode() : 'mm',
			'width' => $publicationFormat->getWidth(),
			'widthUnitCode' => $publicationFormat->getWidthUnitCode() != '' ? $publicationFormat->getWidthUnitCode() : 'mm',
			'thickness' => $publicationFormat->getThickness(),
			'thicknessUnitCode' => $publicationFormat->getThicknessUnitCode() != '' ? $publicationFormat->getThicknessUnitCode() : 'mm',
			'weight' => $publicationFormat->getWeight(),
			'weightUnitCode' => $publicationFormat->getWeightUnitCode() != '' ? $publicationFormat->getWeightUnitCode() : 'gr',
			'productCompositionCode' => $publicationFormat->getProductCompositionCode(),
			'productFormDetailCode' => $publicationFormat->getProductFormDetailCode(),
			'countryManufactureCode' => $publicationFormat->getCountryManufactureCode() != '' ? $publicationFormat->getCountryManufactureCode() : 'CA',
			'imprint' => $publicationFormat->getImprint(),
			'productAvailabilityCode' => $publicationFormat->getProductAvailabilityCode() != '' ? $publicationFormat->getProductAvailabilityCode() : '20',
			'technicalProtectionCode' => $publicationFormat->getTechnicalProtectionCode() != '' ? $publicationFormat->getTechnicalProtectionCode() : '00',
			'returnableIndicatorCode' => $publicationFormat->getReturnableIndicatorCode() != '' ? $publicationFormat->getReturnableIndicatorCode() : 'Y',
			// the pubId plugin needs the format object.
			'publicationFormat' => $publicationFormat
		);

		// initialize the pubId fields.
		$pubIdPluginHelper = $this->_getPubIdPluginHelper();
		$pubIdPluginHelper->init($monograph->getContextId(), $this, $publicationFormat);
		$pubIdPluginHelper->setLinkActions($monograph->getContextId(), $this, $publicationFormat);

	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$monograph = $this->getMonograph();
		$this->readUserVars(array(
			'directSalesPrice',
			'fileSize',
			'override',
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
			'returnableIndicatorCode',
		));

		// consider the additional field names from the public identifer plugins
		$pubIdPluginHelper = $this->_getPubIdPluginHelper();
		$pubIdPluginHelper->readInputData($monograph->getContextId(), $this);

	}

	/**
	 * @copydoc Form::validate()
	 */
	function validate($callHooks = true) {
		$monograph = $this->getMonograph();
		$publicationFormat = $this->getPublicationFormat();
		$pubIdPluginHelper = $this->_getPubIdPluginHelper();
		$pubIdPluginHelper->validate($monograph->getContextId(), $this, $publicationFormat);
		return parent::validate($callHooks);
	}

	/**
	 * Save the metadata and store the catalog data for this specific publication format.
	 */
	function execute($request) {
		parent::execute();

		$monograph = $this->getMonograph();
		$publicationFormat = $this->getPublicationFormat();

		// populate the published monograph with the cataloging metadata
		$publicationFormat->setFileSize($this->getData('override') ? $this->getData('fileSize'):null);
		$publicationFormat->setFrontMatter($this->getData('frontMatter'));
		$publicationFormat->setBackMatter($this->getData('backMatter'));
		$publicationFormat->setHeight($this->getData('height'));
		$publicationFormat->setHeightUnitCode($this->getData('heightUnitCode'));
		$publicationFormat->setWidth($this->getData('width'));
		$publicationFormat->setWidthUnitCode($this->getData('widthUnitCode'));
		$publicationFormat->setThickness($this->getData('thickness'));
		$publicationFormat->setThicknessUnitCode($this->getData('thicknessUnitCode'));
		$publicationFormat->setWeight($this->getData('weight'));
		$publicationFormat->setWeightUnitCode($this->getData('weightUnitCode'));
		$publicationFormat->setProductCompositionCode($this->getData('productCompositionCode'));
		$publicationFormat->setProductFormDetailCode($this->getData('productFormDetailCode'));
		$publicationFormat->setCountryManufactureCode($this->getData('countryManufactureCode'));
		$publicationFormat->setImprint($this->getData('imprint'));
		$publicationFormat->setProductAvailabilityCode($this->getData('productAvailabilityCode'));
		$publicationFormat->setTechnicalProtectionCode($this->getData('technicalProtectionCode'));
		$publicationFormat->setReturnableIndicatorCode($this->getData('returnableIndicatorCode'));

		// consider the additional field names from the public identifer plugins
		$pubIdPluginHelper = $this->_getPubIdPluginHelper();
		$pubIdPluginHelper->execute($monograph->getContextId(), $this, $publicationFormat);

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormatDao->updateObject($publicationFormat);
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
	 * Get the stage id
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get physical format setting
	 * @return bool
	 */
	function getPhysicalFormat() {
		return $this->_isPhysicalFormat;
	}

	/**
	 * Get the remote URL
	 * @return string
	 */
	function getRemoteURL() {
		return $this->_remoteURL;
	}

	/**
	 * Get the publication format
	 * @return PublicationFormat
	 */
	function getPublicationFormat() {
		return $this->_publicationFormat;
	}

	/**
	 * Get the extra form parameters.
	 */
	function getFormParams() {
		return $this->_formParams;
	}

	/**
	 * returns the PKPPubIdPluginHelper associated with this form.
	 * @return PKPPubIdPluginHelper
	 */
	function _getPubIdPluginHelper() {
		return $this->_pubIdPluginHelper;
	}

}

?>
