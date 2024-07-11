<?php

/**
 * @file controllers/grid/catalogEntry/form/PublicationDateForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationDateForm
 *
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing a publication date
 */

namespace APP\controllers\grid\catalogEntry\form;

use APP\codelist\ONIXCodelistItemDAO;
use APP\publication\Publication;
use APP\publicationFormat\PublicationDate;
use APP\publicationFormat\PublicationDateDAO;
use APP\publicationFormat\PublicationFormatDAO;
use APP\submission\Submission;
use APP\template\TemplateManager;
use Exception;
use PKP\db\DAORegistry;
use PKP\form\Form;

class PublicationDateForm extends Form
{
    /** @var Submission The submission associated with the format being edited */
    public $_submission;

    /** @var Publication The publication associated with the format being edited */
    public $_publication;

    /** @var PublicationDate the code being edited */
    public $_publicationDate;

    /**
     * Constructor.
     */
    public function __construct($submission, $publication, $publicationDate)
    {
        parent::__construct('controllers/grid/catalogEntry/form/pubDateForm.tpl');
        $this->setSubmission($submission);
        $this->setPublication($publication);
        $this->setPublicationDate($publicationDate);

        // Validation checks for this form
        $form = $this;
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'role', 'required', 'grid.catalogEntry.roleRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'dateFormat', 'required', 'grid.catalogEntry.dateFormatRequired'));

        $this->addCheck(new \PKP\form\validation\FormValidatorCustom(
            $this,
            'date',
            'required',
            'grid.catalogEntry.dateRequired',
            function ($date) use ($form) {
                $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
                $dateFormat = $form->getData('dateFormat');
                if (!$dateFormat) {
                    return false;
                }
                $dateFormats = $onixCodelistItemDao->getCodes('List55');
                $format = $dateFormats[$dateFormat];
                if (stristr($format, 'string') && $date != '') {
                    return true;
                }
                $format = trim(preg_replace('/\s*\(.*?\)/i', '', $format));
                if (count(str_split($date)) == count(str_split($format))) {
                    return true;
                }
                return false;
            }
        ));

        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'representationId', 'required', 'grid.catalogEntry.publicationFormatRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    //
    // Getters and Setters
    //
    /**
     * Get the date
     *
     * @return PublicationDate
     */
    public function getPublicationDate()
    {
        return $this->_publicationDate;
    }

    /**
     * Set the date
     *
     * @param PublicationDate $publicationDate
     */
    public function setPublicationDate($publicationDate)
    {
        $this->_publicationDate = $publicationDate;
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
     * Initialize form data from the publication date.
     */
    public function initData()
    {
        $date = $this->getPublicationDate();

        if ($date) {
            $this->_data = [
                'publicationDateId' => $date->getId(),
                'role' => $date->getRole(),
                'dateFormat' => $date->getDateFormat(),
                'date' => $date->getDate(),
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
        $publicationDate = $this->getPublicationDate();

        if ($publicationDate) {
            $templateMgr->assign('publicationDateId', $publicationDate->getId());
            $templateMgr->assign('role', $publicationDate->getRole());
            $templateMgr->assign('dateFormat', $publicationDate->getDateFormat());
            $templateMgr->assign('date', $publicationDate->getDate());
            $representationId = $publicationDate->getPublicationFormatId();
        } else { // loading a blank form
            $representationId = (int) $request->getUserVar('representationId');
            $templateMgr->assign('dateFormat', '20'); // YYYYMMDD Onix code as a default
        }

        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */
        $publicationFormat = $publicationFormatDao->getById($representationId, $this->getPublication()->getId());

        if ($publicationFormat) { // the format exists for this submission
            $templateMgr->assign('representationId', $representationId);
            $publicationDates = $publicationFormat->getPublicationDates();
            $assignedRoles = array_keys($publicationDates->toAssociativeArray('role')); // currently assigned roles
            if ($publicationDate) {
                $assignedRoles = array_diff($assignedRoles, [$publicationDate->getRole()]);
            } // allow existing roles to keep their value
            $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
            $roles = $onixCodelistItemDao->getCodes('List163', $assignedRoles); // ONIX list for these
            $templateMgr->assign('publicationDateRoles', $roles);

            //load our date formats
            $dateFormats = $onixCodelistItemDao->getCodes('List55');
            $templateMgr->assign('publicationDateFormats', $dateFormats);
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
            'publicationDateId',
            'representationId',
            'role',
            'dateFormat',
            'date',
        ]);
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        parent::execute(...$functionArgs);
        $publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /** @var PublicationDateDAO $publicationDateDao */
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */

        $submission = $this->getSubmission();
        $publicationDate = $this->getPublicationDate();
        $publicationFormat = $publicationFormatDao->getById($this->getData('representationId'), $this->getPublication()->getId());

        if (!$publicationDate) {
            // this is a new publication date for this published submission
            $publicationDate = $publicationDateDao->newDataObject();
            $existingFormat = false;
            if ($publicationFormat != null) { // ensure this assigned format is in this submission
                $publicationDate->setPublicationFormatId($publicationFormat->getId());
            } else {
                throw new Exception('This assigned format not in authorized submission context!');
            }
        } else {
            $existingFormat = true;
            if ($publicationFormat->getId() != $publicationDate->getPublicationFormatId()) {
                throw new Exception('Invalid format!');
            }
        }

        $publicationDate->setRole($this->getData('role'));
        $publicationDate->setDateFormat($this->getData('dateFormat'));
        $publicationDate->setDate($this->getData('date'));

        if ($existingFormat) {
            $publicationDateDao->updateObject($publicationDate);
            $publicationDateId = $publicationDate->getId();
        } else {
            $publicationDateId = $publicationDateDao->insertObject($publicationDate);
        }

        return $publicationDateId;
    }
}
