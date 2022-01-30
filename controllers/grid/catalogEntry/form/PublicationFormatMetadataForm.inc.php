<?php

/**
 * @file controllers/grid/catalogEntry/form/PublicationFormatMetadataForm.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatMetadataForm
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form to edit a publication format's metadata
 */

use APP\template\TemplateManager;
use PKP\form\Form;

use PKP\plugins\PKPPubIdPluginHelper;

class PublicationFormatMetadataForm extends Form
{
    /** @var Submission The submission this publication format is related to */
    public $_submission;

    /** @var Publication The publication this publication format is related to */
    public $_publication;

    /** @var int The current stage id */
    public $_stageId;

    /** @var PublicationFormat The publication format */
    public $_publicationFormat;

    /** @var PKPPubIdPluginHelper The pub id plugin helper */
    public $_pubIdPluginHelper;

    /** @var bool is this a physical, non-digital format? */
    public $_isPhysicalFormat;

    /** @var string a remote URL to retrieve the contents in this format */
    public $_remoteURL;

    /** @var array Parameters to configure the form template */
    public $_formParams;

    /**
     * Constructor.
     *
     * @param Submission $submission
     * @param Publication $publication
     * @param Representation $representation
     * @param int $isPhysicalFormat
     * @param string $remoteURL
     * @param int $stageId
     * @param array $formParams
     */
    public function __construct($submission, $publication, $representation, $isPhysicalFormat = true, $remoteURL = null, $stageId = null, $formParams = null)
    {
        parent::__construct('controllers/tab/catalogEntry/form/publicationMetadataFormFields.tpl');
        $this->_submission = $submission;
        $this->_publication = $publication;
        $this->_publicationFormat = $representation;

        if (!$this->_submission || !$this->_publication || !$this->_publicationFormat) {
            throw new Exception('PublicationFormatMetadataForm was instantiated without required dependencies.');
        }

        $this->_pubIdPluginHelper = new PKPPubIdPluginHelper();

        $this->_stageId = $stageId;
        $this->_isPhysicalFormat = $isPhysicalFormat;
        $this->_remoteURL = $remoteURL;
        $this->_formParams = $formParams;

        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'productAvailabilityCode', 'required', 'grid.catalogEntry.productAvailabilityRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidatorRegExp($this, 'directSalesPrice', 'optional', 'grid.catalogEntry.validPriceRequired', '/^[0-9]*(\.[0-9]+)?$/'));
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'productCompositionCode', 'required', 'grid.catalogEntry.productCompositionRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    /**
     * @copydoc Form::fetch
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false)
    {
        $publicationFormat = $this->getPublicationFormat();
        $context = $request->getContext();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('submissionId', $this->getSubmission()->getId());
        $templateMgr->assign('publicationId', $this->getPublication()->getId());
        $templateMgr->assign('representationId', (int) $publicationFormat->getId());

        // included to load format-specific templates
        $templateMgr->assign('isPhysicalFormat', (bool) $this->getPhysicalFormat());
        $templateMgr->assign('remoteURL', $this->getRemoteURL());

        $templateMgr->assign('stageId', $this->getStageId());
        $templateMgr->assign('formParams', $this->getFormParams());

        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */

        // Check if e-commerce is available
        $paymentManager = Application::getPaymentManager($context);
        if ($paymentManager->isConfigured()) {
            $templateMgr->assign('paymentConfigured', true);
            $templateMgr->assign('currency', $context->getSetting('currency'));
        }

        // get the lists associated with the select elements on these publication format forms.
        $codes = [
            'productCompositionCodes' => 'List2', // single item, multiple item, trade-only, etc
            'measurementUnitCodes' => 'List50', // grams, inches, millimeters
            'weightUnitCodes' => 'List95', // pounds, grams, ounces
            'measurementTypeCodes' => 'List48', // height, width, depth
            'productFormDetailCodes' => 'List175', // refinement of product form (SACD, Mass market (rack) paperback, etc)
            'productAvailabilityCodes' => 'List65', // Available, In Stock, Print On Demand, Not Yet Available, etc
            'technicalProtectionCodes' => 'List144', // None, DRM, Apple DRM, etc
            'returnableIndicatorCodes' => 'List66', // No, not returnable, Yes, full copies only, (required for physical items only)
            'countriesIncludedCodes' => 'List91', // country region codes
        ];

        foreach ($codes as $templateVarName => $list) {
            $templateMgr->assign($templateVarName, $onixCodelistItemDao->getCodes($list));
        }

        // consider public identifiers
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        $templateMgr->assign('pubIdPlugins', $pubIdPlugins);
        $templateMgr->assign('pubObject', $publicationFormat);

        $templateMgr->assign('notificationRequestOptions', [
            NOTIFICATION_LEVEL_NORMAL => [
                NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD => [ASSOC_TYPE_PRESS, $context->getId()],
            ],
            NOTIFICATION_LEVEL_TRIVIAL => []
        ]);

        return parent::fetch($request, $template, $display);
    }

    /**
     * Initialize form data for an instance of this form.
     */
    public function initData()
    {
        AppLocale::requireComponents(
            LOCALE_COMPONENT_APP_COMMON,
            LOCALE_COMPONENT_PKP_SUBMISSION,
            LOCALE_COMPONENT_APP_SUBMISSION
        );

        $submission = $this->getSubmission();
        $publicationFormat = $this->getPublicationFormat();

        $this->_data = [
            'fileSize' => (bool) $publicationFormat->getFileSize() ? $publicationFormat->getFileSize() : $publicationFormat->getCalculatedFileSize(),
            'override' => (bool) $publicationFormat->getData('fileSize') ? true : false,
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
        ];

        // initialize the pubId fields.
        $pubIdPluginHelper = $this->_getPubIdPluginHelper();
        $pubIdPluginHelper->init($submission->getContextId(), $this, $publicationFormat);
        $pubIdPluginHelper->setLinkActions($submission->getContextId(), $this, $publicationFormat);
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData()
    {
        $submission = $this->getSubmission();
        $this->readUserVars([
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
        ]);

        // consider the additional field names from the public identifer plugins
        $pubIdPluginHelper = $this->_getPubIdPluginHelper();
        $pubIdPluginHelper->readInputData($submission->getContextId(), $this);
    }

    /**
     * @copydoc Form::validate()
     */
    public function validate($callHooks = true)
    {
        $submission = $this->getSubmission();
        $publicationFormat = $this->getPublicationFormat();
        $pubIdPluginHelper = $this->_getPubIdPluginHelper();
        $pubIdPluginHelper->validate($submission->getContextId(), $this, $publicationFormat);
        return parent::validate($callHooks);
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        parent::execute(...$functionArgs);

        $submission = $this->getSubmission();
        $publicationFormat = $this->getPublicationFormat();

        // populate the published submission with the cataloging metadata
        $publicationFormat->setFileSize($this->getData('override') ? $this->getData('fileSize') : null);
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
        $pubIdPluginHelper->execute($submission->getContextId(), $this, $publicationFormat);

        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */
        $publicationFormatDao->updateObject($publicationFormat);
    }

    //
    // Getters and Setters
    //
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
     * Get the Publication
     *
     * @return Publication
     */
    public function getPublication()
    {
        return $this->_publication;
    }

    /**
     * Get the stage id
     *
     * @return int
     */
    public function getStageId()
    {
        return $this->_stageId;
    }

    /**
     * Get physical format setting
     *
     * @return bool
     */
    public function getPhysicalFormat()
    {
        return $this->_isPhysicalFormat;
    }

    /**
     * Get the remote URL
     *
     * @return string
     */
    public function getRemoteURL()
    {
        return $this->_remoteURL;
    }

    /**
     * Get the publication format
     *
     * @return PublicationFormat
     */
    public function getPublicationFormat()
    {
        return $this->_publicationFormat;
    }

    /**
     * Get the extra form parameters.
     */
    public function getFormParams()
    {
        return $this->_formParams;
    }

    /**
     * returns the PKPPubIdPluginHelper associated with this form.
     *
     * @return PKPPubIdPluginHelper
     */
    public function _getPubIdPluginHelper()
    {
        return $this->_pubIdPluginHelper;
    }
}
