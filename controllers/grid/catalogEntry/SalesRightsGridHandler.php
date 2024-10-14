<?php

/**
 * @file controllers/grid/catalogEntry/SalesRightsGridHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SalesRightsGridHandler
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for sales rights.
 */

namespace APP\controllers\grid\catalogEntry;

use APP\controllers\grid\catalogEntry\form\SalesRightsForm;
use APP\core\Application;
use APP\core\Request;
use APP\notification\NotificationManager;
use APP\publication\Publication;
use APP\publicationFormat\PublicationFormat;
use APP\publicationFormat\PublicationFormatDAO;
use APP\publicationFormat\SalesRightsDAO;
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

class SalesRightsGridHandler extends GridHandler
{
    /** @var Submission */
    public $_submission;

    /** @var PublicationFormat */
    public $_publicationFormat;

    /** @var Publication */
    public $_publication;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
            ['fetchGrid', 'fetchRow', 'addRights', 'editRights', 'updateRights', 'deleteRights']
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
     * Get the publication format associated with these sales rights
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
        $salesRightsId = (int) $request->getUserVar('salesRightsId'); // set if editing or deleting a sales rights entry

        if ($salesRightsId) {
            $salesRightsDao = DAORegistry::getDAO('SalesRightsDAO'); /** @var SalesRightsDAO $salesRightsDao */
            $salesRights = $salesRightsDao->getById($salesRightsId, $this->getPublication()->getId());
            if ($salesRights) {
                $representationId = $salesRights->getPublicationFormatId();
            }
        } else { // empty form for new SalesRights
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
        $this->setTitle('grid.catalogEntry.salesRights');

        // Grid actions
        $router = $request->getRouter();
        $actionArgs = $this->getRequestArgs();
        $this->addAction(
            new LinkAction(
                'addRights',
                new AjaxModal(
                    $router->url($request, null, null, 'addRights', null, $actionArgs),
                    __('grid.action.addRights'),
                    'side-modal'
                ),
                __('grid.action.addRights'),
                'add_item'
            )
        );

        // Columns
        $cellProvider = new SalesRightsGridCellProvider();
        $this->addColumn(
            new GridColumn(
                'type',
                'grid.catalogEntry.salesRightsType',
                null,
                null,
                $cellProvider
            )
        );
        $this->addColumn(
            new GridColumn(
                'ROW',
                'grid.catalogEntry.salesRightsROW',
                null,
                'controllers/grid/common/cell/checkMarkCell.tpl',
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
     * @return SalesRightsGridRow
     */
    public function getRowInstance()
    {
        return new SalesRightsGridRow($this->getSubmission());
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
        $salesRightsDao = DAORegistry::getDAO('SalesRightsDAO'); /** @var SalesRightsDAO $salesRightsDao */
        $data = $salesRightsDao->getByPublicationFormatId($publicationFormat->getId());
        return $data->toArray();
    }


    //
    // Public Sales Rights Grid Actions
    //
    /**
     * Edit a new (empty) rights entry
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function addRights($args, $request)
    {
        return $this->editRights($args, $request);
    }

    /**
     * Edit a sales rights entry
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function editRights($args, $request)
    {
        // Identify the sales rights entry to be updated
        $salesRightsId = (int) $request->getUserVar('salesRightsId');
        $submission = $this->getSubmission();

        $salesRightsDao = DAORegistry::getDAO('SalesRightsDAO'); /** @var SalesRightsDAO $salesRightsDao */
        $salesRights = $salesRightsDao->getById($salesRightsId, $this->getPublication()->getId());

        // Form handling
        $salesRightsForm = new SalesRightsForm($submission, $this->getPublication(), $salesRights);
        $salesRightsForm->initData();

        return new JSONMessage(true, $salesRightsForm->fetch($request));
    }

    /**
     * Update a sales rights entry
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function updateRights($args, $request)
    {
        // Identify the sales rights entry to be updated
        $salesRightsId = $request->getUserVar('salesRightsId');
        $submission = $this->getSubmission();

        $salesRightsDao = DAORegistry::getDAO('SalesRightsDAO'); /** @var SalesRightsDAO $salesRightsDao */
        $salesRights = $salesRightsDao->getById($salesRightsId, $this->getPublication()->getId());

        // Form handling
        $salesRightsForm = new SalesRightsForm($submission, $this->getPublication(), $salesRights);
        $salesRightsForm->readInputData();
        if ($salesRightsForm->validate()) {
            $salesRightsId = $salesRightsForm->execute();

            if (!isset($salesRights)) {
                // This is a new entry
                $salesRights = $salesRightsDao->getById($salesRightsId, $this->getPublication()->getId());
                // New added entry action notification content.
                $notificationContent = __('notification.addedSalesRights');
            } else {
                // entry edit action notification content.
                $notificationContent = __('notification.editedSalesRights');
            }

            // Create trivial notification.
            $currentUser = $request->getUser();
            $notificationMgr = new NotificationManager();
            $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => $notificationContent]);

            // Prepare the grid row data
            $row = $this->getRowInstance();
            $row->setGridId($this->getId());
            $row->setId($salesRightsId);
            $row->setData($salesRights);
            $row->initialize($request);

            // Render the row into a JSON response
            return DAO::getDataChangedEvent();
        } else {
            return new JSONMessage(true, $salesRightsForm->fetch($request));
        }
    }

    /**
     * Delete a sales rights entry
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function deleteRights($args, $request)
    {
        // Identify the sales rights entry to be deleted
        $salesRightsId = $request->getUserVar('salesRightsId');
        $salesRightsDao = DAORegistry::getDAO('SalesRightsDAO'); /** @var SalesRightsDAO $salesRightsDao */
        $salesRights = $salesRightsDao->getById($salesRightsId, $this->getPublication()->getId());
        if (!$salesRights) {
            return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
        }

        $salesRightsDao->deleteObject($salesRights);
        $currentUser = $request->getUser();
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => __('notification.removedSalesRights')]);
        return DAO::getDataChangedEvent();
    }
}
