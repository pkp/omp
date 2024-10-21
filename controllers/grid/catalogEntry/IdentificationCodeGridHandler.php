<?php

/**
 * @file controllers/grid/catalogEntry/IdentificationCodeGridHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IdentificationCodeGridHandler
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for identification codes.
 */

namespace APP\controllers\grid\catalogEntry;

use APP\controllers\grid\catalogEntry\form\IdentificationCodeForm;
use APP\core\Application;
use APP\core\Request;
use APP\notification\NotificationManager;
use APP\publication\Publication;
use APP\publicationFormat\IdentificationCodeDAO;
use APP\publicationFormat\PublicationFormat;
use APP\publicationFormat\PublicationFormatDAO;
use APP\submission\Submission;
use Exception;
use PKP\controllers\grid\GridColumn;
use PKP\controllers\grid\GridHandler;
use PKP\core\JSONMessage;
use PKP\db\DAO;
use PKP\db\DAORegistry;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\notification\Notification;
use PKP\security\authorization\PublicationAccessPolicy;
use PKP\security\Role;

class IdentificationCodeGridHandler extends GridHandler
{
    /** @var Submission */
    public $_submission;

    /** @var Publication */
    public $_publication;

    /** @var PublicationFormat */
    public $_publicationFormat;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
            ['fetchGrid', 'fetchRow', 'addCode', 'editCode', 'updateCode', 'deleteCode']
        );
    }


    //
    // Getters/Setters
    //
    /**
     * Get the submission associated with this grid.
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
     * Get the publication associated with this grid.
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

    /**
     * Get the publication format associated with these identification codes
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
     * @param PublicationFormat
     */
    public function setPublicationFormat($publicationFormat)
    {
        $this->_publicationFormat = $publicationFormat;
    }

    //
    // Overridden methods from PKPHandler
    //
    /**
     * @see PKPHandler::authorize()
     *
     * @param Request $request
     * @param array $args
     * @param array $roleAssignments
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $this->addPolicy(new PublicationAccessPolicy($request, $args, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * @copydoc GridHandler::initialize()
     *
     * @param null|mixed $args
     */
    public function initialize($request, $args = null)
    {
        parent::initialize($request, $args);

        // Retrieve the authorized submission.
        $this->setSubmission($this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION));
        $this->setPublication($this->getAuthorizedContextObject(Application::ASSOC_TYPE_PUBLICATION));
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */
        $representationId = null;

        // Retrieve the associated publication format for this grid.
        $identificationCodeId = (int) $request->getUserVar('identificationCodeId'); // set if editing or deleting a code

        if ($identificationCodeId) {
            $identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO'); /** @var IdentificationCodeDAO $identificationCodeDao */
            $identificationCode = $identificationCodeDao->getById($identificationCodeId, $this->getPublication()->getId());
            if ($identificationCode) {
                $representationId = $identificationCode->getPublicationFormatId();
            }
        } else { // empty form for new Code
            $representationId = (int) $request->getUserVar('representationId');
        }

        $publicationFormat = $representationId
            ? $publicationFormatDao->getById((int) $representationId, $this->getPublication()->getId())
            : null;

        if ($publicationFormat) {
            $this->setPublicationFormat($publicationFormat);
        } else {
            throw new Exception('The publication format is not assigned to authorized submission!');
        }

        // Basic grid configuration
        $this->setTitle('monograph.publicationFormat.productIdentifierType');

        // Grid actions
        $router = $request->getRouter();
        $actionArgs = $this->getRequestArgs();
        $this->addAction(
            new LinkAction(
                'addCode',
                new AjaxModal(
                    $router->url($request, null, null, 'addCode', null, $actionArgs),
                    __('grid.action.addCode'),
                ),
                __('grid.action.addCode'),
                'add_item'
            )
        );

        // Columns
        $cellProvider = new IdentificationCodeGridCellProvider();
        $this->addColumn(
            new GridColumn(
                'value',
                'grid.catalogEntry.identificationCodeValue',
                null,
                null,
                $cellProvider,
                ['width' => 50, 'alignment' => GridColumn::COLUMN_ALIGNMENT_LEFT]
            )
        );
        $this->addColumn(
            new GridColumn(
                'code',
                'grid.catalogEntry.identificationCodeType',
                null,
                null,
                $cellProvider
            )
        );
    }


    //
    // Overridden methods from GridHandler
    //
    /**
     * @see GridHandler::getRowInstance()
     *
     * @return IdentificationCodeGridRow
     */
    public function getRowInstance()
    {
        return new IdentificationCodeGridRow($this->getSubmission(), $this->getPublication());
    }

    /**
     * Get the arguments that will identify the data in the grid
     * In this case, the submission.
     *
     * @return array
     */
    public function getRequestArgs()
    {
        return [
            'submissionId' => $this->getSubmission()->getId(),
            'publicationId' => $this->getPublication()->getId(),
            'representationId' => $this->getPublicationFormat()->getId()
        ];
    }

    /**
     * @see GridHandler::loadData
     *
     * @param null|mixed $filter
     */
    public function loadData($request, $filter = null)
    {
        $publicationFormat = $this->getPublicationFormat();
        $identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO'); /** @var IdentificationCodeDAO $identificationCodeDao */
        $data = $identificationCodeDao->getByPublicationFormatId($publicationFormat->getId());
        return $data->toArray();
    }


    //
    // Public Identification Code Grid Actions
    //
    /**
     * Edit a new (empty) code
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function addCode($args, $request)
    {
        return $this->editCode($args, $request);
    }

    /**
     * Edit a code
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function editCode($args, $request)
    {
        // Identify the code to be updated
        $identificationCodeId = (int) $request->getUserVar('identificationCodeId');
        $submission = $this->getSubmission();

        $identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO'); /** @var IdentificationCodeDAO $identificationCodeDao */
        $identificationCode = $identificationCodeDao->getById($identificationCodeId, $this->getPublication()->getId());

        // Form handling
        $identificationCodeForm = new IdentificationCodeForm($submission, $this->getPublication(), $identificationCode);
        $identificationCodeForm->initData();

        return new JSONMessage(true, $identificationCodeForm->fetch($request));
    }

    /**
     * Update a code
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function updateCode($args, $request)
    {
        // Identify the code to be updated
        $identificationCodeId = $request->getUserVar('identificationCodeId');
        $submission = $this->getSubmission();

        $identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO'); /** @var IdentificationCodeDAO $identificationCodeDao */
        $identificationCode = $identificationCodeDao->getById($identificationCodeId, $this->getPublication()->getId());

        // Form handling
        $identificationCodeForm = new IdentificationCodeForm($submission, $this->getPublication(), $identificationCode);
        $identificationCodeForm->readInputData();
        if ($identificationCodeForm->validate()) {
            $identificationCodeId = $identificationCodeForm->execute();

            if (!isset($identificationCode)) {
                // This is a new code
                $identificationCode = $identificationCodeDao->getById($identificationCodeId, $this->getPublication()->getId());
                // New added code action notification content.
                $notificationContent = __('notification.addedIdentificationCode');
            } else {
                // code edit action notification content.
                $notificationContent = __('notification.editedIdentificationCode');
            }

            // Create trivial notification.
            $currentUser = $request->getUser();
            $notificationMgr = new NotificationManager();
            $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => $notificationContent]);

            // Prepare the grid row data
            $row = $this->getRowInstance();
            $row->setGridId($this->getId());
            $row->setId($identificationCodeId);
            $row->setData($identificationCode);
            $row->initialize($request);

            // Render the row into a JSON response
            return DAO::getDataChangedEvent();
        } else {
            return new JSONMessage(true, $identificationCodeForm->fetch($request));
        }
    }

    /**
     * Delete a code
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function deleteCode($args, $request)
    {
        // Identify the code to be deleted
        $identificationCodeId = $request->getUserVar('identificationCodeId');

        $identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO'); /** @var IdentificationCodeDAO $identificationCodeDao */
        $identificationCode = $identificationCodeDao->getById($identificationCodeId, $this->getPublication()->getId());
        if (!$identificationCode) {
            return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
        }

        $identificationCodeDao->deleteObject($identificationCode);
        $currentUser = $request->getUser();
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => __('notification.removedIdentificationCode')]);
        return DAO::getDataChangedEvent();
    }
}
