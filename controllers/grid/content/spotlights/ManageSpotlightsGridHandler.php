<?php

/**
 * @file controllers/grid/content/spotlights/ManageSpotlightsGridHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SpotlightsGridHandler
 * @ingroup controllers_grid_content_spotlights
 *
 * @brief Handle grid requests for spotlights.
 */

namespace APP\controllers\grid\content\spotlights;

use APP\controllers\grid\content\spotlights\form\SpotlightForm;
use APP\facades\Repo;
use APP\notification\Notification;
use APP\notification\NotificationManager;
use APP\spotlight\Spotlight;
use APP\submission\Submission;
use PKP\controllers\grid\GridColumn;
use PKP\controllers\grid\GridHandler;
use PKP\core\JSONMessage;
use PKP\db\DAO;
use PKP\db\DAORegistry;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\Role;

class ManageSpotlightsGridHandler extends GridHandler
{
    /**
     * @var Press
     */
    public $_press;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
            ['fetchGrid', 'fetchRow', 'addSpotlight', 'editSpotlight',
                'updateSpotlight', 'deleteSpotlight', 'itemAutocomplete']
        );
    }

    //
    // Getters/Setters
    //
    /**
     * Get the press associated with this grid.
     *
     * @return Press
     */
    public function &getPress()
    {
        return $this->_press;
    }

    /**
     * Set the Press (authorized)
     *
     * @param Press
     */
    public function setPress($press)
    {
        $this->_press = & $press;
    }

    //
    // Overridden methods from PKPHandler
    //
    /**
     * @see PKPHandler::authorize()
     *
     * @param PKPRequest $request
     * @param array $args
     * @param array $roleAssignments
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        $returner = parent::authorize($request, $args, $roleAssignments);

        $spotlightId = $request->getUserVar('spotlightId');
        if ($spotlightId) {
            $press = $request->getPress();
            $spotlightDao = DAORegistry::getDAO('SpotlightDAO'); /** @var SpotlightDAO $spotlightDao */
            $spotlight = $spotlightDao->getById($spotlightId);
            if ($spotlight == null || $spotlight->getPressId() != $press->getId()) {
                return false;
            }
        }

        return $returner;
    }

    /**
     * @copydoc GridHandler::initialize()
     *
     * @param null|mixed $args
     */
    public function initialize($request, $args = null)
    {
        parent::initialize($request, $args);

        // Basic grid configuration
        $this->setTitle('spotlight.spotlights');

        // Set the no items row text
        $this->setEmptyRowText('spotlight.noneExist');

        $press = $request->getPress();
        $this->setPress($press);

        // Columns
        $spotlightsGridCellProvider = new SpotlightsGridCellProvider();
        $this->addColumn(
            new GridColumn(
                'title',
                'grid.content.spotlights.form.title',
                null,
                null,
                $spotlightsGridCellProvider,
                ['width' => 40]
            )
        );

        $this->addColumn(
            new GridColumn(
                'itemTitle',
                'grid.content.spotlights.spotlightItemTitle',
                null,
                null,
                $spotlightsGridCellProvider,
                ['width' => 40]
            )
        );

        $this->addColumn(
            new GridColumn(
                'type',
                'common.type',
                null,
                null,
                $spotlightsGridCellProvider
            )
        );

        // Add grid action.
        $router = $request->getRouter();
        $this->addAction(
            new LinkAction(
                'addSpotlight',
                new AjaxModal(
                    $router->url($request, null, null, 'addSpotlight', null, null),
                    __('grid.action.addSpotlight'),
                    'modal_add_item'
                ),
                __('grid.action.addSpotlight'),
                'add_item'
            )
        );
    }


    //
    // Overridden methods from GridHandler
    //
    /**
     * @see GridHandler::getRowInstance()
     *
     * @return SpotlightsGridRow
     */
    public function getRowInstance()
    {
        return new SpotlightsGridRow($this->getPress());
    }

    /**
     * @see GridHandler::loadData()
     *
     * @param null|mixed $filter
     */
    public function loadData($request, $filter = null)
    {
        $spotlightDao = DAORegistry::getDAO('SpotlightDAO'); /** @var SpotlightDAO $spotlightDao */
        $press = $this->getPress();
        return $spotlightDao->getByPressId($press->getId());
    }

    /**
     * Get the arguments that will identify the data in the grid
     * In this case, the press.
     *
     * @return array
     */
    public function getRequestArgs()
    {
        $press = $this->getPress();
        return [
            'pressId' => $press->getId()
        ];
    }

    //
    // Public Spotlights Grid Actions
    //

    public function addSpotlight($args, $request)
    {
        return $this->editSpotlight($args, $request);
    }

    /**
     * Edit a spotlight entry
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function editSpotlight($args, $request)
    {
        $spotlightId = (int)$request->getUserVar('spotlightId');
        $press = $request->getPress();
        $pressId = $press->getId();

        $spotlightForm = new SpotlightForm($pressId, $spotlightId);
        $spotlightForm->initData();

        return new JSONMessage(true, $spotlightForm->fetch($request));
    }

    /**
     * Update a spotlight entry
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function updateSpotlight($args, $request)
    {
        // Identify the spotlight entry to be updated
        $spotlightId = $request->getUserVar('spotlightId');

        $press = $this->getPress();

        $spotlightDao = DAORegistry::getDAO('SpotlightDAO'); /** @var SpotlightDAO $spotlightDao */
        $spotlight = $spotlightDao->getById($spotlightId, $press->getId());

        // Form handling
        $spotlightForm = new SpotlightForm($press->getId(), $spotlightId);

        $spotlightForm->readInputData();
        if ($spotlightForm->validate()) {
            $spotlightId = $spotlightForm->execute();

            if (!isset($spotlight)) {
                // This is a new entry
                $spotlight = $spotlightDao->getById($spotlightId, $press->getId());
                // New added entry action notification content.
                $notificationContent = __('notification.addedSpotlight');
            } else {
                // entry edit action notification content.
                $notificationContent = __('notification.editedSpotlight');
            }

            // Create trivial notification.
            $currentUser = $request->getUser();
            $notificationMgr = new NotificationManager();
            $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => $notificationContent]);

            // Prepare the grid row data
            $row = $this->getRowInstance();
            $row->setGridId($this->getId());
            $row->setId($spotlightId);
            $row->setData($spotlight);
            $row->initialize($request);

            // Render the row into a JSON response
            return DAO::getDataChangedEvent();
        } else {
            return new JSONMessage(true, $spotlightForm->fetch($request));
        }
    }

    /**
     * Delete a spotlight entry
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function deleteSpotlight($args, $request)
    {

        // Identify the entry to be deleted
        $spotlightId = $request->getUserVar('spotlightId');

        $spotlightDao = DAORegistry::getDAO('SpotlightDAO'); /** @var SpotlightDAO $spotlightDao */
        $press = $this->getPress();
        $spotlight = $spotlightDao->getById($spotlightId, $press->getId());
        if ($spotlight != null) { // authorized

            $result = $spotlightDao->deleteObject($spotlight);

            if ($result) {
                $currentUser = $request->getUser();
                $notificationMgr = new NotificationManager();
                $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => __('notification.removedSpotlight')]);
                return DAO::getDataChangedEvent();
            } else {
                return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
            }
        }
    }

    /**
     * Returns a JSON list for the autocomplete field. Fetches a list of possible spotlight options
     * based on the spotlight type chosen.
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function itemAutocomplete($args, $request)
    {
        $name = $request->getUserVar('name');
        $press = $this->getPress();
        $itemList = [];

        // get the items that match.
        $matches = [];

        $collector = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$press->getId()])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->limit(100);

        if ($name) {
            $collector->searchPhrase($name);
        }

        $submissions = $collector->getMany();
        foreach ($submissions as $submission) {
            $matches[] = ['label' => $submission->getLocalizedTitle(), 'value' => $submission->getId() . ':' . Spotlight::SPOTLIGHT_TYPE_BOOK];
        }

        if (!empty($matches)) {
            $itemList = array_merge($itemList, $matches);
        }

        $matches = [];

        $allSeries = Repo::section()
            ->getCollector()
            ->filterByContextIds([$press->getId()])
            ->getMany();
        foreach ($allSeries as $series) {
            if ($name == '' || preg_match('/' . preg_quote($name, '/') . '/i', $series->getLocalizedTitle())) {
                $matches[] = ['label' => $series->getLocalizedTitle(), 'value' => $series->getId() . ':' . Spotlight::SPOTLIGHT_TYPE_SERIES];
            }
        }

        if (!empty($matches)) {
            $itemList = array_merge($itemList, $matches);
        }

        if (count($itemList) == 0) {
            return $this->noAutocompleteResults();
        }

        return new JSONMessage(true, $itemList);
    }
}
