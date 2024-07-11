<?php

/**
 * @file controllers/grid/catalogEntry/form/MarketForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MarketForm
 *
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing a market region entry
 */

namespace APP\controllers\grid\catalogEntry\form;

use APP\codelist\ONIXCodelistItemDAO;
use APP\monograph\RepresentativeDAO;
use APP\publication\Publication;
use APP\publicationFormat\Market;
use APP\publicationFormat\MarketDAO;
use APP\publicationFormat\PublicationFormatDAO;
use APP\submission\Submission;
use APP\template\TemplateManager;
use Exception;
use PKP\db\DAORegistry;
use PKP\form\Form;

class MarketForm extends Form
{
    /** @var Submission The submission associated with the format being edited */
    public $_submission;

    /** @var Market Market the entry being edited */
    public $_market;

    /** @var Publication */
    public $_publication;

    /**
     * Constructor.
     */
    public function __construct($submission, $publication, $market)
    {
        parent::__construct('controllers/grid/catalogEntry/form/marketForm.tpl');
        $this->setSubmission($submission);
        $this->setPublication($publication);
        $this->setMarket($market);

        // Validation checks for this form
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'representationId', 'required', 'grid.catalogEntry.publicationFormatRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'date', 'required', 'grid.catalogEntry.dateRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'price', 'required', 'grid.catalogEntry.priceRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    //
    // Getters and Setters
    //
    /**
     * Get the entry
     *
     * @return Market
     */
    public function getMarket()
    {
        return $this->_market;
    }

    /**
     * Set the entry
     *
     * @param Market $market
     */
    public function setMarket($market)
    {
        $this->_market = $market;
    }

    /**
     * Get the Submission
     *
     * @return Submission
     */
    public function getSubmission()
    {
        return $this->_submission;
    }

    /**
     * Set the Submission
     *
     * @param Submission
     */
    public function setSubmission($submission)
    {
        $this->_submission = $submission;
    }

    /**
     * Get the Publication
     *
     * @return Publication
     */
    public function getPublication()
    {
        return $this->_publication;
    }

    /**
     * Set the Publication
     *
     * @param Publication
     */
    public function setPublication($publication)
    {
        $this->_publication = $publication;
    }


    //
    // Overridden template methods
    //
    /**
     * Initialize form data from the market entry.
     */
    public function initData()
    {
        $market = $this->getMarket();

        if ($market) {
            $this->_data = [
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
            ];
        }
    }

    /**
     * @copydoc Form::fetch()
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $submission = $this->getSubmission();
        $templateMgr->assign('submissionId', $submission->getId());
        $templateMgr->assign('publicationId', $this->getPublication()->getId());
        $market = $this->getMarket();
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
        $templateMgr->assign([
            'countryCodes' => $onixCodelistItemDao->getCodes('List91'), // countries (CA, US, GB, etc)
            'regionCodes' => $onixCodelistItemDao->getCodes('List49'), // regions (British Columbia, England, etc)
            'publicationDateFormats' => $onixCodelistItemDao->getCodes('List55'), // YYYYMMDD, YYMMDD, etc
            'publicationDateRoles' => $onixCodelistItemDao->getCodes('List163'),
            'currencyCodes' => $onixCodelistItemDao->getCodes('List96'), // GBP, USD, CAD, etc
            'priceTypeCodes' => $onixCodelistItemDao->getCodes('List58'), // without tax, with tax, etc
            'extentTypeCodes' => $onixCodelistItemDao->getCodes('List23'), // word count, FM page count, BM page count, main page count, etc
            'taxRateCodes' => $onixCodelistItemDao->getCodes('List62'), // higher rate, standard rate, zero rate
            'taxTypeCodes' => $onixCodelistItemDao->getCodes('List171'), // VAT, GST
        ]);

        /** @var RepresentativeDAO */
        $representativeDao = DAORegistry::getDAO('RepresentativeDAO');
        $availableAgents = $representativeDao->getAgentsByMonographId($submission->getId());
        $agentOptions = [];
        while ($agent = $availableAgents->next()) {
            $agentOptions[$agent->getId()] = $agent->getName();
        }
        $templateMgr->assign('availableAgents', $agentOptions);

        $availableSuppliers = $representativeDao->getSuppliersByMonographId($submission->getId());
        $supplierOptions = [];
        while ($supplier = $availableSuppliers->next()) {
            $supplierOptions[$supplier->getId()] = $supplier->getName();
        }
        $templateMgr->assign('availableSuppliers', $supplierOptions);

        if ($market) {
            $templateMgr->assign([
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
            ]);

            $representationId = $market->getPublicationFormatId();
        } else { // loading a blank form
            $representationId = (int) $request->getUserVar('representationId');
            $templateMgr->assign([
                'dateFormat' => '20', // YYYYMMDD Onix code as a default
                'dateRole' => '01', // 'Date of Publication' as default
                'currencyCode' => 'CAD',
            ]);
        }

        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */
        $publicationFormat = $publicationFormatDao->getById($representationId, $this->getPublication()->getId());

        if ($publicationFormat) { // the format exists for this submission
            $templateMgr->assign('representationId', $representationId);
        } else {
            throw new Exception('Format not in authorized submission');
        }

        return parent::fetch($request, $template, $display);
    }

    /**
     * Assign form data to user-submitted data.
     *
     * @see Form::readInputData()
     */
    public function readInputData()
    {
        $this->readUserVars([
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
        ]);
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        parent::execute(...$functionArgs);
        $marketDao = DAORegistry::getDAO('MarketDAO'); /** @var MarketDAO $marketDao */
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */

        $submission = $this->getSubmission();
        $market = $this->getMarket();
        $publicationFormat = $publicationFormatDao->getById($this->getData('representationId'), $this->getPublication()->getId());

        if (!$market) {
            // this is a new assigned format to this published submission
            $market = $marketDao->newDataObject();
            $existingFormat = false;
            if ($publicationFormat != null) { // ensure this assigned format is in this submission
                $market->setPublicationFormatId($publicationFormat->getId());
            } else {
                throw new Exception('This assigned format not in authorized submission context!');
            }
        } else {
            $existingFormat = true;
            if ($publicationFormat->getId() != $market->getPublicationFormatId()) {
                throw new Exception('Invalid format!');
            }
        }

        $market->setCountriesIncluded($this->getData('countriesIncluded') ? $this->getData('countriesIncluded') : []);
        $market->setCountriesExcluded($this->getData('countriesExcluded') ? $this->getData('countriesExcluded') : []);
        $market->setRegionsIncluded($this->getData('regionsIncluded') ? $this->getData('regionsIncluded') : []);
        $market->setRegionsExcluded($this->getData('regionsExcluded') ? $this->getData('regionsExcluded') : []);
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
