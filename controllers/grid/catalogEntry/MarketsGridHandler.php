<?php

/**
 * @file controllers/grid/catalogEntry/MarketsGridHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MarketsGridHandler
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for markets.
 */

namespace APP\controllers\grid\catalogEntry;

use APP\controllers\grid\catalogEntry\form\MarketForm;
use APP\core\Application;
use APP\core\Request;
use APP\notification\NotificationManager;
use APP\publication\Publication;
use APP\publicationFormat\MarketDAO;
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

class MarketsGridHandler extends GridHandler
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
            ['fetchGrid', 'fetchRow', 'addMarket', 'editMarket',
                'updateMarket', 'deleteMarket']
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
     * Get the Publication associated with this grid.
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
     * Get the publication format associated with these markets
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
        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);
        $this->setPublication($this->getAuthorizedContextObject(Application::ASSOC_TYPE_PUBLICATION));
        $this->setSubmission($submission);
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */
        $representationId = null;

        // Retrieve the associated publication format for this grid.
        $marketId = (int) $request->getUserVar('marketId'); // set if editing or deleting a market entry

        if ($marketId) {
            $marketDao = DAORegistry::getDAO('MarketDAO'); /** @var MarketDAO $marketDao */
            $market = $marketDao->getById($marketId, $this->getPublication()->getId());
            if ($market) {
                $representationId = $market->getPublicationFormatId();
            }
        } else { // empty form for new Market
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
        $this->setTitle('grid.catalogEntry.markets');

        // Grid actions
        $router = $request->getRouter();
        $actionArgs = $this->getRequestArgs();
        $this->addAction(
            new LinkAction(
                'addMarket',
                new AjaxModal(
                    $router->url($request, null, null, 'addMarket', null, $actionArgs),
                    __('grid.action.addMarket'),
                ),
                __('grid.action.addMarket'),
                'add_item'
            )
        );

        // Columns
        $cellProvider = new MarketsGridCellProvider();
        $this->addColumn(
            new GridColumn(
                'territory',
                'grid.catalogEntry.marketTerritory',
                null,
                null,
                $cellProvider
            )
        );
        $this->addColumn(
            new GridColumn(
                'rep',
                'grid.catalogEntry.representatives',
                null,
                null,
                $cellProvider
            )
        );
        $this->addColumn(
            new GridColumn(
                'price',
                'monograph.publicationFormat.price',
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
     * @return MarketsGridRow
     */
    public function getRowInstance()
    {
        return new MarketsGridRow($this->getSubmission(), $this->getPublication());
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
     * @copydoc GridHandler::loadData
     *
     * @param null|mixed $filter
     */
    public function loadData($request, $filter = null)
    {
        $publicationFormat = $this->getPublicationFormat();
        $marketDao = DAORegistry::getDAO('MarketDAO'); /** @var MarketDAO $marketDao */
        $data = $marketDao->getByPublicationFormatId($publicationFormat->getId());
        return $data->toArray();
    }


    //
    // Public  Market Grid Actions
    //
    /**
     * Add a new market
     *
     * @param array $args
     * @param Request $request
     */
    public function addMarket($args, $request)
    {
        return $this->editMarket($args, $request);
    }

    /**
     * Edit a markets entry
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function editMarket($args, $request)
    {
        // Identify the market entry to be updated
        $marketId = (int) $request->getUserVar('marketId');
        $submission = $this->getSubmission();

        $marketDao = DAORegistry::getDAO('MarketDAO'); /** @var MarketDAO $marketDao */
        $market = $marketDao->getById($marketId, $this->getPublication()->getId());

        // Form handling
        $marketForm = new MarketForm($submission, $this->getPublication(), $market);
        $marketForm->initData();

        return new JSONMessage(true, $marketForm->fetch($request));
    }

    /**
     * Update a markets entry
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function updateMarket($args, $request)
    {
        // Identify the market entry to be updated
        $marketId = $request->getUserVar('marketId');
        $submission = $this->getSubmission();

        $marketDao = DAORegistry::getDAO('MarketDAO'); /** @var MarketDAO $marketDao */
        $market = $marketDao->getById($marketId, $this->getPublication()->getId());

        // Form handling
        $marketForm = new MarketForm($submission, $this->getPublication(), $market);
        $marketForm->readInputData();
        if ($marketForm->validate()) {
            $marketId = $marketForm->execute();

            if (!isset($market)) {
                // This is a new entry
                $market = $marketDao->getById($marketId, $this->getPublication()->getId());
                // New added entry action notification content.
                $notificationContent = __('notification.addedMarket');
            } else {
                // entry edit action notification content.
                $notificationContent = __('notification.editedMarket');
            }

            // Create trivial notification.
            $currentUser = $request->getUser();
            $notificationMgr = new NotificationManager();
            $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => $notificationContent]);

            // Prepare the grid row data
            $row = $this->getRowInstance();
            $row->setGridId($this->getId());
            $row->setId($marketId);
            $row->setData($market);
            $row->initialize($request);

            // Render the row into a JSON response
            return DAO::getDataChangedEvent();
        } else {
            return new JSONMessage(true, $marketForm->fetch($request));
        }
    }

    /**
     * Delete a market entry
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function deleteMarket($args, $request)
    {
        // Identify the markets entry to be deleted
        $marketId = $request->getUserVar('marketId');

        $marketDao = DAORegistry::getDAO('MarketDAO'); /** @var MarketDAO $marketDao */
        $market = $marketDao->getById($marketId, $this->getPublication()->getId());
        if (!$market) {
            return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
        }

        $marketDao->deleteObject($market);
        $currentUser = $request->getUser();
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => __('notification.removedMarket')]);
        return DAO::getDataChangedEvent();
    }
}
