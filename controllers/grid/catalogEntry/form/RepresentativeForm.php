<?php

/**
 * @file controllers/grid/catalogEntry/form/RepresentativeForm.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RepresentativeForm
 *
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing a representative entry
 */

namespace APP\controllers\grid\catalogEntry\form;

use APP\codelist\ONIXCodelistItemDAO;
use APP\core\Application;
use APP\monograph\Representative;
use APP\monograph\RepresentativeDAO;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\form\Form;

class RepresentativeForm extends Form
{
    /** @var Submission The monograph associated with the format being edited */
    public $_monograph;

    /** @var Representative the entry being edited */
    public $_representative;

    /**
     * Constructor.
     */
    public function __construct($monograph, $representative)
    {
        parent::__construct('controllers/grid/catalogEntry/form/representativeForm.tpl');
        $this->setMonograph($monograph);
        $this->setRepresentative($representative);

        // Validation checks for this form
        $form = $this;
        $this->addCheck(new \PKP\form\validation\FormValidatorCustom(
            $this,
            'isSupplier',
            'required',
            'grid.catalogEntry.roleRequired',
            function ($isSupplier) use ($form) {
                $request = Application::get()->getRequest();
                $agentRole = $request->getUserVar('agentRole');
                $supplierRole = $request->getUserVar('supplierRole');
                $onixDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixDao */
                return (!$isSupplier && $onixDao->codeExistsInList($agentRole, '69')) || ($isSupplier && $onixDao->codeExistsInList($supplierRole, '93'));
            }
        ));
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    //
    // Getters and Setters
    //
    /**
     * Get the representative
     *
     * @return Representative
     */
    public function &getRepresentative()
    {
        return $this->_representative;
    }

    /**
     * Set the representative
     *
     * @param Representative $representative
     */
    public function setRepresentative($representative)
    {
        $this->_representative = $representative;
    }

    /**
     * Get the Monograph
     *
     * @return Submission
     */
    public function getMonograph()
    {
        return $this->_monograph;
    }

    /**
     * Set the Monograph
     *
     * @param Submission
     */
    public function setMonograph($monograph)
    {
        $this->_monograph = $monograph;
    }


    //
    // Overridden template methods
    //
    /**
     * Initialize form data from the representative entry.
     */
    public function initData()
    {
        $representative = $this->getRepresentative();

        if ($representative) {
            $this->_data = [
                'representativeId' => $representative->getId(),
                'role' => $representative->getRole(),
                'representativeIdType' => $representative->getRepresentativeIdType(),
                'representativeIdValue' => $representative->getRepresentativeIdValue(),
                'name' => $representative->getName(),
                'phone' => $representative->getPhone(),
                'email' => $representative->getEmail(),
                'url' => $representative->getUrl(),
                'isSupplier' => $representative->getIsSupplier(),
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

        $monograph = $this->getMonograph();
        $templateMgr->assign('submissionId', $monograph->getId());
        $representative = $this->getRepresentative();
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
        $templateMgr->assign([
            'idTypeCodes' => $onixCodelistItemDao->getCodes('92'), // GLN, etc
            'agentRoleCodes' => $onixCodelistItemDao->getCodes('69'), // Sales Agent, etc
            'supplierRoleCodes' => $onixCodelistItemDao->getCodes('93'), // wholesaler, publisher to retailer, etc
            'isSupplier' => true,
        ]); // default to 'supplier' on the form.

        if ($representative) {
            $templateMgr->assign([
                'representativeId' => $representative->getId(),
                'role' => $representative->getRole(),
                'representativeIdType' => $representative->getRepresentativeIdType(),
                'representativeIdValue' => $representative->getRepresentativeIdValue(),
                'name' => $representative->getName(),
                'phone' => $representative->getPhone(),
                'email' => $representative->getEmail(),
                'url' => $representative->getUrl(),
                'isSupplier' => (bool)$representative->getIsSupplier(),
            ]);
        } else {
            $templateMgr->assign('representativeIdType', '06');
        } // pre-populate new forms with GLN as it is recommended

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
            'representativeId',
            'agentRole',
            'supplierRole',
            'representativeIdType',
            'representativeIdValue',
            'name',
            'phone',
            'email',
            'url',
            'isSupplier',
        ]);
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        parent::execute(...$functionArgs);
        $representativeDao = DAORegistry::getDAO('RepresentativeDAO'); /** @var RepresentativeDAO $representativeDao */
        $monograph = $this->getMonograph();
        $representative = $this->getRepresentative();

        if (!$representative) {
            // this is a new representative for this monograph
            $representative = $representativeDao->newDataObject();
            $representative->setMonographId($monograph->getId());
            $existingRepresentative = false;
        } else {
            $existingRepresentative = true;
            // verify that this representative is in this monograph's context
            if ($representativeDao->getById($representative->getId(), $monograph->getId()) == null) {
                throw new \Exception('Invalid representative!');
            }
        }

        if ($this->getData('isSupplier')) { // supplier
            $representative->setRole($this->getData('supplierRole'));
        } else {
            $representative->setRole($this->getData('agentRole'));
        }

        $representative->setRepresentativeIdType($this->getData('representativeIdType'));
        $representative->setRepresentativeIdValue($this->getData('representativeIdValue'));
        $representative->setName($this->getData('name'));
        $representative->setPhone($this->getData('phone'));
        $representative->setEmail($this->getData('email'));
        $representative->setUrl($this->getData('url'));
        $representative->setIsSupplier((bool)$this->getData('isSupplier'));

        if ($existingRepresentative) {
            $representativeDao->updateObject($representative);
            $representativeId = $representative->getId();
        } else {
            $representativeId = $representativeDao->insertObject($representative);
        }

        return $representativeId;
    }
}
