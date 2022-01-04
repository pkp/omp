<?php

/**
 * @file controllers/grid/catalogEntry/form/PublicationFormatForm.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatForm
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing a format
 */

use APP\template\TemplateManager;
use PKP\log\SubmissionLog;
use APP\log\SubmissionEventLogEntry;
use PKP\form\Form;

class PublicationFormatForm extends Form
{
    /** The monograph associated with the format being edited **/
    public $_monograph;

    /** PublicationFormat the format being edited **/
    public $_publicationFormat;

    /** @var Publication $_publication */
    public $_publication = null;

    /**
     * Constructor.
     */
    public function __construct($monograph, $publicationFormat, $publication)
    {
        parent::__construct('controllers/grid/catalogEntry/form/formatForm.tpl');
        $this->setMonograph($monograph);
        $this->setPublicationFormat($publicationFormat);
        $this->setPublication($publication);

        // Validation checks for this form
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'name', 'required', 'grid.catalogEntry.nameRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'entryKey', 'required', 'grid.catalogEntry.publicationFormatRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidatorRegExp($this, 'urlPath', 'optional', 'validator.alpha_dash_period', '/^[a-zA-Z0-9]+([\\.\\-_][a-zA-Z0-9]+)*$/'));
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    //
    // Getters and Setters
    //
    /**
     * Get the format
     *
     * @return PublicationFormat
     */
    public function getPublicationFormat()
    {
        return $this->_publicationFormat;
    }

    /**
     * Set the publication format
     *
     * @param @format PublicationFormat
     */
    public function setPublicationFormat($publicationFormat)
    {
        $this->_publicationFormat = $publicationFormat;
    }

    /**
     * Get the Monograph
     *
     * @return Monograph
     */
    public function getMonograph()
    {
        return $this->_monograph;
    }

    /**
     * Set the MonographId
     *
     * @param Monograph
     */
    public function setMonograph($monograph)
    {
        $this->_monograph = $monograph;
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
     * Set the PublicationId
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
     * Initialize form data from the associated publication format.
     */
    public function initData()
    {
        $format = $this->getPublicationFormat();

        if ($format) {
            $isbn10 = '';
            $isbn13 = '';
            $identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO'); /* @var $identificationCodeDao IdentificationCodeDAO */
            $identificationCodes = $identificationCodeDao->getByPublicationFormatId($format->getId());
            while ($identificationCode = $identificationCodes->next()) {
                if ($identificationCode->getCode() == '02') {
                    $isbn10 = $identificationCode->getValue();
                }
                if ($identificationCode->getCode() == '15') {
                    $isbn13 = $identificationCode->getValue();
                }
            }

            $this->_data = [
                'entryKey' => $format->getEntryKey(),
                'name' => $format->getName(null),
                'isPhysicalFormat' => $format->getPhysicalFormat() ? true : false,
                'isbn10' => $isbn10,
                'isbn13' => $isbn13,
                'remoteURL' => $format->getRemoteURL(),
                'urlPath' => $format->getData('urlPath'),
            ];
        } else {
            $this->setData('entryKey', 'DA');
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
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /* @var $onixCodelistItemDao ONIXCodelistItemDAO */
        $templateMgr->assign('entryKeys', $onixCodelistItemDao->getCodes('List7')); // List7 is for object formats

        $monograph = $this->getMonograph();
        $templateMgr->assign('submissionId', $monograph->getId());
        $publicationFormat = $this->getPublicationFormat();
        if ($publicationFormat != null) {
            $templateMgr->assign('representationId', $publicationFormat->getId());
        }
        $templateMgr->assign('publicationId', $this->getPublication()->getId());
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
            'name',
            'entryKey',
            'isPhysicalFormat',
            'isbn10',
            'isbn13',
            'remoteURL',
            'urlPath',
        ]);
    }

    /**
     * Save the assigned format
     *
     * @return int Publication format ID
     *
     * @see Form::execute()
     */
    public function execute(...$functionParams)
    {
        parent::execute(...$functionParams);

        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /* @var $publicationFormatDao PublicationFormatDAO */
        $publicationFormat = $this->getPublicationFormat();
        if (!$publicationFormat) {
            // this is a new format to this published submission
            $publicationFormat = $publicationFormatDao->newDataObject();
            $publicationFormat->setData('publicationId', $this->getPublication()->getId());
            $existingFormat = false;
        } else {
            $existingFormat = true;
            if ($this->getPublication()->getId() != $publicationFormat->getData('publicationId')) {
                throw new Exception('Invalid publication format');
            }
        }

        $publicationFormat->setName($this->getData('name'));
        $publicationFormat->setEntryKey($this->getData('entryKey'));
        $publicationFormat->setPhysicalFormat($this->getData('isPhysicalFormat') ? true : false);
        $publicationFormat->setRemoteURL($this->getData('remoteURL'));
        $publicationFormat->setData('urlPath', $this->getData('urlPath'));

        if ($existingFormat) {
            $publicationFormatDao->updateObject($publicationFormat);
            $representationId = $publicationFormat->getId();
        } else {
            $representationId = $publicationFormatDao->insertObject($publicationFormat);
        }

        // Remove existing ISBN-10 or ISBN-13 code
        $identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO'); /* @var $identificationCodeDao IdentificationCodeDAO */
        $identificationCodes = $identificationCodeDao->getByPublicationFormatId($representationId);
        while ($identificationCode = $identificationCodes->next()) {
            if ($identificationCode->getCode() == '02' || $identificationCode->getCode() == '15') {
                $identificationCodeDao->deleteById($identificationCode->getId());
            }
        }

        // Add new ISBN-10 or ISBN-13 codes
        if ($this->getData('isbn10') || $this->getData('isbn13')) {
            $isbnValues = [];
            if ($this->getData('isbn10')) {
                $isbnValues['02'] = $this->getData('isbn10');
            }
            if ($this->getData('isbn13')) {
                $isbnValues['15'] = $this->getData('isbn13');
            }

            foreach ($isbnValues as $isbnCode => $isbnValue) {
                $identificationCode = $identificationCodeDao->newDataObject();
                $identificationCode->setPublicationFormatId($representationId);
                $identificationCode->setCode($isbnCode);
                $identificationCode->setValue($isbnValue);
                $identificationCodeDao->insertObject($identificationCode);
            }
        }

        if (!$existingFormat) {
            // log the creation of the format.
            SubmissionLog::logEvent(Application::get()->getRequest(), $this->getMonograph(), SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_CREATE, 'submission.event.publicationFormatCreated', ['formatName' => $publicationFormat->getLocalizedName()]);
        }

        return $representationId;
    }
}
