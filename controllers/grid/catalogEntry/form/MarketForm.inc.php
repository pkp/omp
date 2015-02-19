<?php

/**
 * @file controllers/grid/catalogEntry/form/MarketForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	function MarketForm($monograph, $market) {
		parent::Form('controllers/grid/catalogEntry/form/marketForm.tpl');
		$this->setMonograph($monograph);
		$this->setMarket($market);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'publicationFormatId', 'required', 'grid.catalogEntry.publicationFormatRequired'));
		$this->addCheck(new FormValidator($this, 'date', 'required', 'grid.catalogEntry.dateRequired'));
		$this->addCheck(new FormValidator($this, 'price', 'required', 'grid.catalogEntry.priceRequired'));
		$this->addCheck(new FormValidatorPost($this));
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
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch($request) {

		$templateMgr = TemplateManager::getManager($request);
		$publicationFormatId = null;

		$monograph = $this->getMonograph();
		$templateMgr->assign('submissionId', $monograph->getId());
		$market = $this->getMarket();
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$templateMgr->assign('countryCodes', $onixCodelistItemDao->getCodes('List91')); // countries (CA, US, GB, etc)
		$templateMgr->assign('regionCodes', $onixCodelistItemDao->getCodes('List49')); // regions (British Columbia, England, etc)
		$templateMgr->assign('publicationDateFormats', $onixCodelistItemDao->getCodes('List55')); // YYYYMMDD, YYMMDD, etc
		$templateMgr->assign('publicationDateRoles', $onixCodelistItemDao->getCodes('List163'));
		$templateMgr->assign('currencyCodes', $onixCodelistItemDao->getCodes('List96')); // GBP, USD, CAD, etc
		$templateMgr->assign('priceTypeCodes', $onixCodelistItemDao->getCodes('List58')); // without tax, with tax, etc
		$templateMgr->assign_by_ref('extentTypeCodes',$onixCodelistItemDao->getCodes('List23')); // word count, FM page count, BM page count, main page count, etc
		$templateMgr->assign_by_ref('taxRateCodes', $onixCodelistItemDao->getCodes('List62')); // higher rate, standard rate, zero rate
		$templateMgr->assign_by_ref('taxTypeCodes', $onixCodelistItemDao->getCodes('List171')); // VAT, GST

		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph = $publishedMonographDao->getById($monograph->getId());
		$availableAgents = $publishedMonograph->getAgents();
		$agentOptions = array();
		while ($agent = $availableAgents->next()) {
			$agentOptions[$agent->getId()] = $agent->getName();
		}
		$templateMgr->assign('availableAgents', $agentOptions);

		$availableSuppliers = $publishedMonograph->getSuppliers();
		$supplierOptions = array();
		while ($supplier = $availableSuppliers->next()) {
			$supplierOptions[$supplier->getId()] = $supplier->getName();
		}
		$templateMgr->assign('availableSuppliers', $supplierOptions);

		if ($market) {
			$publicationFormatId = $market->getPublicationFormatId();
			$templateMgr->assign('marketId', $market->getId());
			$templateMgr->assign('countriesIncluded', $market->getCountriesIncluded());
			$templateMgr->assign('countriesExcluded', $market->getCountriesExcluded());
			$templateMgr->assign('regionsIncluded', $market->getRegionsIncluded());
			$templateMgr->assign('regionsExcluded', $market->getRegionsExcluded());
			$templateMgr->assign('date', $market->getDate());
			$templateMgr->assign('dateRole', $market->getDateRole());
			$templateMgr->assign('dateFormat', $market->getDateFormat());
			$templateMgr->assign('discount', $market->getDiscount());
			$templateMgr->assign('price', $market->getPrice());
			$templateMgr->assign('priceTypeCode', $market->getPriceTypeCode());
			$templateMgr->assign('currencyCode', $market->getCurrencyCode() != '' ? $market->getCurrencyCode() : 'CAD');
			$templateMgr->assign('taxRateCode', $market->getTaxRateCode());
			$templateMgr->assign('taxTypeCode', $market->getTaxTypeCode() != '' ? $market->getTaxTypeCode() : '02');
			$templateMgr->assign('agentId', $market->getAgentId());
			$templateMgr->assign('supplierId', $market->getSupplierId());

			$publicationFormatId = $market->getPublicationFormatId();
		} else { // loading a blank form
			$publicationFormatId = (int) $request->getUserVar('publicationFormatId');
			$templateMgr->assign('dateFormat', '20'); // YYYYMMDD Onix code as a default
			$templateMgr->assign('dateRole', '01'); // 'Date of Publication' as default
			$templateMgr->assign('currencyCode', 'CAD');
		}

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($publicationFormatId, $monograph->getId());

		if ($publicationFormat) { // the format exists for this monograph
			$templateMgr->assign('publicationFormatId', $publicationFormatId);
		} else {
			fatalError('Format not in authorized monograph');
		}

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'marketId',
			'publicationFormatId',
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
		$publicationFormat = $publicationFormatDao->getById($this->getData('publicationFormatId'), $monograph->getId());

		if (!$market) {
			// this is a new assigned format to this published monograph
			$market = $marketDao->newDataObject();
			if ($publicationFormat != null) { // ensure this assigned format is in this monograph
				$market->setPublicationFormatId($publicationFormat->getId());
				$existingFormat = false;
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

?>
