<?php

/**
 * @file controllers/grid/catalogEntry/form/MarketForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MarketForm
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing a market region entry
 */

import('lib.pkp.classes.form.Form');

class MarketForm extends Form {
	/** The monograph associated with the format being edited **/
	var $_monograph;

	/** Market the entry being edited **/
	var $_market;

	/**
	 * Constructor.
	 */
	function __construct($monograph, $market) {
		parent::__construct('controllers/grid/catalogEntry/form/marketForm.tpl');
		$this->setMonograph($monograph);
		$this->setMarket($market);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'representationId', 'required', 'grid.catalogEntry.publicationFormatRequired'));
		$this->addCheck(new FormValidator($this, 'date', 'required', 'grid.catalogEntry.dateRequired'));
		$this->addCheck(new FormValidator($this, 'price', 'required', 'grid.catalogEntry.priceRequired'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the entry
	 * @return Market
	 */
	function getMarket() {
		return $this->_market;
	}

	/**
	 * Set the entry
	 * @param @market Market
	 */
	function setMarket($market) {
		$this->_market = $market;
	}

	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the Monograph
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph = $monograph;
	}


	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the market entry.
	 */
	function initData() {
		$market = $this->getMarket();

		if ($market) {
			$this->_data = array(
				'marketId' => $market->getId(),
				'countriesIncluded' => $market->getCountriesIncluded(),
				'countriesExcluded' => $market->getCountriesExcluded(),
				'regionsIncluded' => $market->getRegionsIncluded(),
				'regionsExcluded' => $market->getRegionsExcluded(),
				'date' => $market->getDate(),
				'dateFormat' => $market->getDateFormat(),
				'discount' => $market->getDiscount(),
				'dateRole' => $market->getDateRole(),
				'agentId' => $market->getAgentId(),
				'supplierId' => $market->getSupplierId(),
			);
		}
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$monograph = $this->getMonograph();
		$templateMgr->assign('submissionId', $monograph->getId());
		$market = $this->getMarket();
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$templateMgr->assign(array(
			'countryCodes' => $onixCodelistItemDao->getCodes('List91'), // countries (CA, US, GB, etc)
			'regionCodes' => $onixCodelistItemDao->getCodes('List49'), // regions (British Columbia, England, etc)
			'publicationDateFormats' => $onixCodelistItemDao->getCodes('List55'), // YYYYMMDD, YYMMDD, etc
			'publicationDateRoles' => $onixCodelistItemDao->getCodes('List163'),
			'currencyCodes' => $onixCodelistItemDao->getCodes('List96'), // GBP, USD, CAD, etc
			'priceTypeCodes' => $onixCodelistItemDao->getCodes('List58'), // without tax, with tax, etc
			'extentTypeCodes' => $onixCodelistItemDao->getCodes('List23'), // word count, FM page count, BM page count, main page count, etc
			'taxRateCodes' => $onixCodelistItemDao->getCodes('List62'), // higher rate, standard rate, zero rate
			'taxTypeCodes' => $onixCodelistItemDao->getCodes('List171'), // VAT, GST
		));

		$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO');
		$publishedSubmission = $publishedSubmissionDao->getBySubmissionId($monograph->getId());
		$availableAgents = $publishedSubmission->getAgents();
		$agentOptions = array();
		while ($agent = $availableAgents->next()) {
			$agentOptions[$agent->getId()] = $agent->getName();
		}
		$templateMgr->assign('availableAgents', $agentOptions);

		$availableSuppliers = $publishedSubmission->getSuppliers();
		$supplierOptions = array();
		while ($supplier = $availableSuppliers->next()) {
			$supplierOptions[$supplier->getId()] = $supplier->getName();
		}
		$templateMgr->assign('availableSuppliers', $supplierOptions);

		if ($market) {
			$templateMgr->assign(array(
				'marketId' => $market->getId(),
				'countriesIncluded' => $market->getCountriesIncluded(),
				'countriesExcluded' => $market->getCountriesExcluded(),
				'regionsIncluded' => $market->getRegionsIncluded(),
				'regionsExcluded' => $market->getRegionsExcluded(),
				'date' => $market->getDate(),
				'dateRole' => $market->getDateRole(),
				'dateFormat' => $market->getDateFormat(),
				'discount' => $market->getDiscount(),
				'price' => $market->getPrice(),
				'priceTypeCode' => $market->getPriceTypeCode(),
				'currencyCode' => $market->getCurrencyCode() != '' ? $market->getCurrencyCode() : 'CAD',
				'taxRateCode' => $market->getTaxRateCode(),
				'taxTypeCode' => $market->getTaxTypeCode() != '' ? $market->getTaxTypeCode() : '02',
				'agentId' => $market->getAgentId(),
				'supplierId' => $market->getSupplierId(),
			));

			$representationId = $market->getPublicationFormatId();
		} else { // loading a blank form
			$representationId = (int) $request->getUserVar('representationId');
			$templateMgr->assign(array(
				'dateFormat' => '20', // YYYYMMDD Onix code as a default
				'dateRole' => '01', // 'Date of Publication' as default
				'currencyCode' => 'CAD',
			));
		}

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($representationId, $monograph->getId());

		if ($publicationFormat) { // the format exists for this monograph
			$templateMgr->assign('representationId', $representationId);
		} else {
			fatalError('Format not in authorized monograph');
		}

		return parent::fetch($request, $template, $display);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'marketId',
			'representationId',
			'countriesIncluded',
			'countriesExcluded',
			'regionsIncluded',
			'regionsExcluded',
			'date',
			'dateFormat',
			'dateRole',
			'discount',
			'price',
			'priceTypeCode',
			'currencyCode',
			'taxRateCode',
			'taxTypeCode',
			'agentId',
			'supplierId',
		));
	}

	/**
	 * Save the entry
	 * @see Form::execute()
	 */
	function execute() {
		$marketDao = DAORegistry::getDAO('MarketDAO');
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');

		$monograph = $this->getMonograph();
		$market = $this->getMarket();
		$publicationFormat = $publicationFormatDao->getById($this->getData('representationId'), $monograph->getId());

		if (!$market) {
			// this is a new assigned format to this published submission
			$market = $marketDao->newDataObject();
			$existingFormat = false;
			if ($publicationFormat != null) { // ensure this assigned format is in this monograph
				$market->setPublicationFormatId($publicationFormat->getId());
			} else {
				fatalError('This assigned format not in authorized monograph context!');
			}
		} else {
			$existingFormat = true;
			if ($publicationFormat->getId() !== $market->getPublicationFormatId()) fatalError('Invalid format!');
		}

		$market->setCountriesIncluded($this->getData('countriesIncluded') ? $this->getData('countriesIncluded') : array());
		$market->setCountriesExcluded($this->getData('countriesExcluded') ? $this->getData('countriesExcluded') : array());
		$market->setRegionsIncluded($this->getData('regionsIncluded') ? $this->getData('regionsIncluded') : array());
		$market->setRegionsExcluded($this->getData('regionsExcluded') ? $this->getData('regionsExcluded') : array());
		$market->setDate($this->getData('date'));
		$market->setDateFormat($this->getData('dateFormat'));
		$market->setDiscount($this->getData('discount'));
		$market->setDateRole($this->getData('dateRole'));
		$market->setPrice($this->getData('price'));
		$market->setPriceTypeCode($this->getData('priceTypeCode'));
		$market->setCurrencyCode($this->getData('currencyCode'));
		$market->setTaxRateCode($this->getData('taxRateCode'));
		$market->setTaxTypeCode($this->getData('taxTypeCode'));
		$market->setAgentId($this->getData('agentId'));
		$market->setSupplierId($this->getData('supplierId'));

		if ($existingFormat) {
			$marketDao->updateObject($market);
			$marketId = $market->getId();
		} else {
			$marketId = $marketDao->insertObject($market);
		}

		return $marketId;
	}
}


