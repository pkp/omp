<?php

/**
 * @file controllers/grid/catalogEntry/form/IdentificationCodeForm.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IdentificationCodeForm
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing an identification code
 */

use APP\template\TemplateManager;

use PKP\form\Form;

class IdentificationCodeForm extends Form
{
    /** @var Submission The submission associated with the format being edited */
    public $_submission;

    /** @var Publication The publication associated with the format being edited */
    public $_publication;

    /** @var IdentificationCode Identification Code the code being edited */
    public $_identificationCode;

    /**
     * Constructor.
     */
    public function __construct($submission, $publication, $identificationCode)
    {
        parent::__construct('controllers/grid/catalogEntry/form/codeForm.tpl');
        $this->setSubmission($submission);
        $this->setPublication($publication);
        $this->setIdentificationCode($identificationCode);

        // Validation checks for this form
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'code', 'required', 'grid.catalogEntry.codeRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'value', 'required', 'grid.catalogEntry.valueRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'representationId', 'required', 'grid.catalogEntry.publicationFormatRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    //
    // Getters and Setters
    //
    /**
     * Get the code
     *
     * @return IdentificationCode
     */
    public function getIdentificationCode()
    {
        return $this->_identificationCode;
    }

    /**
     * Set the code
     *
     * @param IdentificationCode $identificationCode
     */
    public function setIdentificationCode($identificationCode)
    {
        $this->_identificationCode = $identificationCode;
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
     * Initialize form data from the identification code.
     */
    public function initData()
    {
        $code = $this->getIdentificationCode();

        if ($code) {
            $this->_data = [
                'identificationCodeId' => $code->getId(),
                'code' => $code->getCode(),
                'value' => $code->getValue(),
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
        $templateMgr->assign([
            'submissionId' => $submission->getId(),
            'publicationId' => $this->getPublication()->getId()
        ]);

        if ($identificationCode = $this->getIdentificationCode()) {
            $templateMgr->assign([
                'identificationCodeId' => $identificationCode->getId(),
                'code' => $identificationCode->getCode(),
                'value' => $identificationCode->getValue()
            ]);
            $representationId = $identificationCode->getPublicationFormatId();
        } else { // loading a blank form
            $representationId = (int) $request->getUserVar('representationId');
        }

        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */
        $publicationFormat = $publicationFormatDao->getById($representationId, $this->getPublication()->getId());

        if ($publicationFormat) { // the format exists for this submission
            $templateMgr->assign('representationId', $representationId);
            $identificationCodes = $publicationFormat->getIdentificationCodes();
            $assignedCodes = array_keys($identificationCodes->toAssociativeArray('code')); // currently assigned codes
            if ($identificationCode) {
                $assignedCodes = array_diff($assignedCodes, [$identificationCode->getCode()]);
            } // allow existing codes to keep their value
            $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */

            // Since DOIs may be separately enabled, we give that precedence and remove DOI from here if that is the case.
            $context = $request->getContext();
            if ($context->areDoisEnabled()) {
                $assignedCodes[] = '06'; // 06 is DOI in ONIX-speak.
            }
            $codes = $onixCodelistItemDao->getCodes('List5', $assignedCodes); // ONIX list for these
            $templateMgr->assign('identificationCodes', $codes);
        } else {
            fatalError('Format not in authorized submission');
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
            'identificationCodeId',
            'representationId',
            'code',
            'value',
        ]);
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        parent::execute(...$functionArgs);
        $identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO'); /** @var IdentificationCodeDAO $identificationCodeDao */
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */

        $submission = $this->getSubmission();
        $identificationCode = $this->getIdentificationCode();
        $publicationFormat = $publicationFormatDao->getById($this->getData('representationId', $this->getPublication()->getId()));

        if (!$identificationCode) {
            // this is a new code to this published submission
            $identificationCode = $identificationCodeDao->newDataObject();
            $existingFormat = false;
            if ($publicationFormat != null) { // ensure this format is in this submission
                $identificationCode->setPublicationFormatId($publicationFormat->getId());
            } else {
                fatalError('This format not in authorized submission context!');
            }
        } else {
            $existingFormat = true;
            if ($publicationFormat->getId() != $identificationCode->getPublicationFormatId()) {
                throw new Exception('Invalid format!');
            }
        }

        $identificationCode->setCode($this->getData('code'));
        $identificationCode->setValue($this->getData('value'));

        if ($existingFormat) {
            $identificationCodeDao->updateObject($identificationCode);
            $identificationCodeId = $identificationCode->getId();
        } else {
            $identificationCodeId = $identificationCodeDao->insertObject($identificationCode);
        }

        return $identificationCodeId;
    }
}
