<?php

/**
 * @file controllers/grid/catalogEntry/PublicationDateGridHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationDateGridHandler
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for publication dates.
 */

namespace APP\controllers\grid\catalogEntry;

use APP\controllers\grid\catalogEntry\form\PublicationDateForm;
use APP\core\Application;
use APP\core\Request;
use APP\notification\NotificationManager;
use APP\publication\Publication;
use APP\publicationFormat\PublicationDateDAO;
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

class PublicationDateGridHandler extends GridHandler
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
            ['fetchGrid', 'fetchRow', 'addDate', 'editDate', 'updateDate', 'deleteDate']
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
     * Get the publication format associated with these dates
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
        $publicationDateId = (int) $request->getUserVar('publicationDateId'); // set if editing or deleting a date

        if ($publicationDateId) {
            $publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /** @var PublicationDateDAO $publicationDateDao */
            $publicationDate = $publicationDateDao->getById($publicationDateId, $this->getPublication()->getId());
            if ($publicationDate) {
                $representationId = $publicationDate->getPublicationFormatId();
            }
        } else { // empty form for new Date
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
        $this->setTitle('grid.catalogEntry.publicationDates');

        // Grid actions
        $router = $request->getRouter();
        $actionArgs = $this->getRequestArgs();
        $this->addAction(
            new LinkAction(
                'addDate',
                new AjaxModal(
                    $router->url($request, null, null, 'addDate', null, $actionArgs),
                    __('grid.action.addDate'),
                    'modal_add_item'
                ),
                __('grid.action.addDate'),
                'add_item'
            )
        );

        // Columns
        $cellProvider = new PublicationDateGridCellProvider();
        $this->addColumn(
            new GridColumn(
                'value',
                'grid.catalogEntry.dateValue',
                null,
                null,
                $cellProvider,
                ['width' => 50, 'alignment' => GridColumn::COLUMN_ALIGNMENT_LEFT]
            )
        );
        $this->addColumn(
            new GridColumn(
                'code',
                'grid.catalogEntry.dateRole',
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
     * @return PublicationDateGridRow
     */
    public function getRowInstance()
    {
        return new PublicationDateGridRow($this->getSubmission(), $this->getPublication());
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
        $publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /** @var PublicationDateDAO $publicationDateDao */
        $data = $publicationDateDao->getByPublicationFormatId($publicationFormat->getId());
        return $data->toArray();
    }


    //
    // Public Date Grid Actions
    //
    /**
     * Edit a new (empty) date
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function addDate($args, $request)
    {
        return $this->editDate($args, $request);
    }

    /**
     * Edit a date
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function editDate($args, $request)
    {
        // Identify the date to be updated
        $publicationDateId = (int) $request->getUserVar('publicationDateId');
        $submission = $this->getSubmission();

        $publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /** @var PublicationDateDAO $publicationDateDao */
        $publicationDate = $publicationDateDao->getById($publicationDateId, $this->getPublication()->getId());

        // Form handling
        $publicationDateForm = new PublicationDateForm($submission, $this->getPublication(), $publicationDate);
        $publicationDateForm->initData();

        return new JSONMessage(true, $publicationDateForm->fetch($request));
    }

    /**
     * Update a date
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function updateDate($args, $request)
    {
        // Identify the code to be updated
        $publicationDateId = $request->getUserVar('publicationDateId');
        $submission = $this->getSubmission();

        $publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /** @var PublicationDateDAO $publicationDateDao */
        $publicationDate = $publicationDateDao->getById($publicationDateId, $this->getPublication()->getId());

        // Form handling
        $publicationDateForm = new PublicationDateForm($submission, $this->getPublication(), $publicationDate);
        $publicationDateForm->readInputData();
        if ($publicationDateForm->validate()) {
            $publicationDateId = $publicationDateForm->execute();

            if (!isset($publicationDate)) {
                // This is a new code
                $publicationDate = $publicationDateDao->getById($publicationDateId, $this->getPublication()->getId());
                // New added code action notification content.
                $notificationContent = __('notification.addedPublicationDate');
            } else {
                // code edit action notification content.
                $notificationContent = __('notification.editedPublicationDate');
            }

            // Create trivial notification.
            $currentUser = $request->getUser();
            $notificationMgr = new NotificationManager();
            $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => $notificationContent]);

            // Prepare the grid row data
            $row = $this->getRowInstance();
            $row->setGridId($this->getId());
            $row->setId($publicationDateId);
            $row->setData($publicationDate);
            $row->initialize($request);

            // Render the row into a JSON response
            return DAO::getDataChangedEvent();
        } else {
            return new JSONMessage(true, $publicationDateForm->fetch($request));
        }
    }

    /**
     * Delete a date
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function deleteDate($args, $request)
    {
        // Identify the code to be deleted
        $publicationDateId = $request->getUserVar('publicationDateId');

        $publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /** @var PublicationDateDAO $publicationDateDao */
        $publicationDate = $publicationDateDao->getById($publicationDateId, $this->getPublication()->getId());
        if (!$publicationDate) {
            return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
        }

        $publicationDateDao->deleteObject($publicationDate);
        $currentUser = $request->getUser();
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => __('notification.removedPublicationDate')]);
        return DAO::getDataChangedEvent();
    }
}
