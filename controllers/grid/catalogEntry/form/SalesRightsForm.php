<?php

/**
 * @file controllers/grid/catalogEntry/form/SalesRightsForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SalesRightsForm
 *
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing a sales rights entry
 */

namespace APP\controllers\grid\catalogEntry\form;

use APP\codelist\ONIXCodelistItemDAO;
use APP\publication\Publication;
use APP\publicationFormat\PublicationFormatDAO;
use APP\publicationFormat\SalesRights;
use APP\publicationFormat\SalesRightsDAO;
use APP\submission\Submission;
use APP\template\TemplateManager;
use Exception;
use PKP\db\DAORegistry;
use PKP\form\Form;

class SalesRightsForm extends Form
{
    /** @var Submission The submission associated with the format being edited */
    public $_submission;

    /** @var SalesRights the entry being edited */
    public $_salesRights;

    /** @var Publication */
    public $_publication;

    /**
     * Constructor.
     */
    public function __construct($submission, $publication, $salesRights)
    {
        parent::__construct('controllers/grid/catalogEntry/form/salesRightsForm.tpl');
        $this->setSubmission($submission);
        $this->setPublication($publication);
        $this->setSalesRights($salesRights);

        // Validation checks for this form
        $form = $this;
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'type', 'required', 'grid.catalogEntry.typeRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'representationId', 'required', 'grid.catalogEntry.publicationFormatRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidatorCustom(
            $this,
            'ROWSetting',
            'optional',
            'grid.catalogEntry.oneROWPerFormat',
            function ($ROWSetting) use ($form, $salesRights) {
                $salesRightsDao = DAORegistry::getDAO('SalesRightsDAO'); /** @var SalesRightsDAO $salesRightsDao */
                $pubFormatId = $form->getData('representationId');
                return $ROWSetting == '' || $salesRightsDao->getROWByPublicationFormatId($pubFormatId) == null ||
                    ($salesRights != null && $salesRightsDao->getROWByPublicationFormatId($pubFormatId)->getId() == $salesRights->getId());
            }
        ));

        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    //
    // Getters and Setters
    //
    /**
     * Get the entry
     *
     * @return SalesRights
     */
    public function getSalesRights()
    {
        return $this->_salesRights;
    }

    /**
     * Set the entry
     *
     * @param SalesRights $salesRights
     */
    public function setSalesRights($salesRights)
    {
        $this->_salesRights = $salesRights;
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
     * Initialize form data from the sales rights entry.
     */
    public function initData()
    {
        $salesRights = $this->getSalesRights();

        if ($salesRights) {
            $this->_data = [
                'salesRightsId' => $salesRights->getId(),
                'type' => $salesRights->getType(),
                'ROWSetting' => $salesRights->getROWSetting(),
                'countriesIncluded' => $salesRights->getCountriesIncluded(),
                'countriesExcluded' => $salesRights->getCountriesExcluded(),
                'regionsIncluded' => $salesRights->getRegionsIncluded(),
                'regionsExcluded' => $salesRights->getRegionsExcluded(),
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
        $salesRights = $this->getSalesRights();
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
        $templateMgr->assign('countryCodes', $onixCodelistItemDao->getCodes('List91')); // countries (CA, US, GB, etc)
        $templateMgr->assign('regionCodes', $onixCodelistItemDao->getCodes('List49')); // regions (British Columbia, England, etc)

        if ($salesRights) {
            $templateMgr->assign('salesRightsId', $salesRights->getId());
            $templateMgr->assign('type', $salesRights->getType());
            $templateMgr->assign('ROWSetting', $salesRights->getROWSetting());
            $templateMgr->assign('countriesIncluded', $salesRights->getCountriesIncluded());
            $templateMgr->assign('countriesExcluded', $salesRights->getCountriesExcluded());
            $templateMgr->assign('regionsIncluded', $salesRights->getRegionsIncluded());
            $templateMgr->assign('regionsExcluded', $salesRights->getRegionsExcluded());

            $representationId = $salesRights->getPublicationFormatId();
        } else { // loading a blank form
            $representationId = (int) $request->getUserVar('representationId');
        }

        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */
        $publicationFormat = $publicationFormatDao->getById($representationId, $this->getPublication()->getId());

        if ($publicationFormat) { // the format exists for this submission
            $templateMgr->assign('representationId', $representationId);
            // SalesRightsType values are not normally used more than once per PublishingDetail block, so filter used ones out.
            $assignedSalesRights = $publicationFormat->getSalesRights();
            $assignedTypes = array_keys($assignedSalesRights->toAssociativeArray('type')); // currently assigned types

            if ($salesRights) {
                $assignedTypes = array_diff($assignedTypes, [$salesRights->getType()]);
            } // allow existing codes to keep their value

            $types = $onixCodelistItemDao->getCodes('List46', $assignedTypes); // ONIX list for these
            $templateMgr->assign('salesRights', $types);
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
            'salesRightsId',
            'representationId',
            'type',
            'ROWSetting',
            'countriesIncluded',
            'countriesExcluded',
            'regionsIncluded',
            'regionsExcluded',
        ]);
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        parent::execute(...$functionArgs);
        $salesRightsDao = DAORegistry::getDAO('SalesRightsDAO'); /** @var SalesRightsDAO $salesRightsDao */
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */

        $submission = $this->getSubmission();
        $salesRights = $this->getSalesRights();
        $publicationFormat = $publicationFormatDao->getById($this->getData('representationId'), $this->getPublication()->getId());

        if (!$salesRights) {
            // this is a new assigned format to this published submission
            $salesRights = $salesRightsDao->newDataObject();
            $existingFormat = false;
            if ($publicationFormat != null) { // ensure this assigned format is in this submission
                $salesRights->setPublicationFormatId($publicationFormat->getId());
            } else {
                throw new Exception('This assigned format not in authorized submission context!');
            }
        } else {
            $existingFormat = true;
            if ($publicationFormat->getId() != $salesRights->getPublicationFormatId()) {
                throw new Exception('Invalid format!');
            }
        }

        $salesRights->setType($this->getData('type'));
        $salesRights->setROWSetting($this->getData('ROWSetting') ? true : false);
        $salesRights->setCountriesIncluded($this->getData('countriesIncluded') ? $this->getData('countriesIncluded') : []);
        $salesRights->setCountriesExcluded($this->getData('countriesExcluded') ? $this->getData('countriesExcluded') : []);
        $salesRights->setRegionsIncluded($this->getData('regionsIncluded') ? $this->getData('regionsIncluded') : []);
        $salesRights->setRegionsExcluded($this->getData('regionsExcluded') ? $this->getData('regionsExcluded') : []);

        if ($existingFormat) {
            $salesRightsDao->updateObject($salesRights);
            $salesRightsId = $salesRights->getId();
        } else {
            $salesRightsId = $salesRightsDao->insertObject($salesRights);
        }

        return $salesRightsId;
    }
}
