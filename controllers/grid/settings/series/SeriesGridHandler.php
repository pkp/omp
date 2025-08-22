<?php

/**
 * @file controllers/grid/settings/series/SeriesGridHandler.php
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SeriesGridHandler
 *
 * @ingroup controllers_grid_settings_series
 *
 * @brief Handle series grid requests.
 */

namespace APP\controllers\grid\settings\series;

use APP\controllers\grid\settings\series\form\SeriesForm;
use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\notification\NotificationManager;
use PKP\context\SubEditorsDAO;
use PKP\controllers\grid\feature\OrderGridItemsFeature;
use PKP\controllers\grid\GridColumn;
use PKP\controllers\grid\settings\SetupGridHandler;
use PKP\core\JSONMessage;
use PKP\db\DAO;
use PKP\db\DAORegistry;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\notification\Notification;
use PKP\security\Role;
use stdClass;

class SeriesGridHandler extends SetupGridHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
            ['fetchGrid', 'fetchRow', 'addSeries', 'editSeries', 'updateSeries', 'deleteSeries', 'saveSequence', 'deactivateSeries','activateSeries']
        );
    }


    //
    // Overridden template methods
    //
    /**
     * @copydoc SetupGridHandler::initialize
     *
     * @param null|mixed $args
     */
    public function initialize($request, $args = null)
    {
        parent::initialize($request, $args);
        $press = $request->getPress();

        // Set the grid title.
        $this->setTitle('catalog.manage.series');

        // Elements to be displayed in the grid
        $subEditorsDao = DAORegistry::getDAO('SubEditorsDAO'); /** @var SubEditorsDAO $subEditorsDao */
        $seriesIterator = Repo::section()
            ->getCollector()
            ->filterByContextIds([$press->getId()])
            ->getMany();

        $gridData = [];
        foreach ($seriesIterator as $series) {
            // Get the categories data for the row
            $categories = Repo::section()->getAssignedCategories($series->getId(), $press->getId());
            $categoriesString = null;
            foreach ($categories as $category) {
                if (!empty($categoriesString)) {
                    $categoriesString .= ', ';
                }
                $categoriesString .= $category->getLocalizedTitle();
            }
            if (empty($categoriesString)) {
                $categoriesString = __('common.none');
            }

            // Get the series editors data for the row
            $assignments = $subEditorsDao->getBySubmissionGroupIds([$series->getId()], Application::ASSOC_TYPE_SECTION, $press->getId());
            $assignedSeriesEditors = Repo::user()
                ->getCollector()
                ->filterByUserIds(
                    $assignments
                        ->map(fn (stdClass $assignment) => $assignment->userId)
                        ->filter()
                        ->toArray()
                )
                ->getMany();
            if ($assignedSeriesEditors->isEmpty()) {
                $editorsString = __('common.none');
            } else {
                $editors = [];
                foreach ($assignedSeriesEditors as $seriesEditor) {
                    $editors[] = $seriesEditor->getFullName();
                }
                $editorsString = implode(', ', $editors);
            }

            $seriesId = $series->getId();
            $gridData[$seriesId] = [
                'title' => $series->getLocalizedTitle(),
                'categories' => $categoriesString,
                'editors' => $editorsString,
                'inactive' => $series->getIsInactive(),
                'seq' => $series->getSequence()
            ];
        }

        $this->setGridDataElements($gridData);

        // Add grid-level actions
        $router = $request->getRouter();
        $this->addAction(
            new LinkAction(
                'addSeries',
                new AjaxModal(
                    $router->url($request, null, null, 'addSeries', null, ['gridId' => $this->getId()]),
                    __('grid.action.addSeries'),
                ),
                __('grid.action.addSeries'),
                'add_category'
            )
        );

        $seriesGridCellProvider = new SeriesGridCellProvider();
        // Columns
        $this->addColumn(
            new GridColumn(
                'title',
                'common.title'
            )
        );
        $this->addColumn(new GridColumn('categories', 'grid.category.categories'));
        $this->addColumn(new GridColumn('editors', 'user.role.editors'));
        // Series 'inactive'
        $this->addColumn(
            new GridColumn(
                'inactive',
                'common.inactive',
                null,
                'controllers/grid/common/cell/selectStatusCell.tpl',
                $seriesGridCellProvider,
                ['alignment' => GridColumn::COLUMN_ALIGNMENT_CENTER,
                    'width' => 20]
            )
        );
    }

    //
    // Overridden methods from GridHandler
    //
    /**
     * @copydoc GridHandler::initFeatures()
     */
    public function initFeatures($request, $args)
    {
        return [new OrderGridItemsFeature()];
    }

    /**
     * Get the list of "publish data changed" events.
     * Used to update the site context switcher upon create/delete.
     *
     * @return array
     */
    public function getPublishChangeEvents()
    {
        return ['updateSidebar'];
    }

    /**
     * Get the row handler - override the default row handler
     *
     * @return SeriesGridRow
     */
    public function getRowInstance()
    {
        return new SeriesGridRow();
    }

    /**
     * @copydoc GridHandler::getDataElementSequence()
     */
    public function getDataElementSequence($gridDataElement)
    {
        return $gridDataElement['seq'];
    }

    /**
     * @copydoc GridHandler::setDataElementSequence()
     */
    public function setDataElementSequence($request, $rowId, $gridDataElement, $newSequence)
    {
        $press = $request->getPress();
        $series = Repo::section()->get($rowId, $press->getId());
        $series->setSequence($newSequence);
        Repo::section()->edit($series, []);
    }

    //
    // Public Series Grid Actions
    //
    /**
     * An action to add a new series
     *
     * @param array $args
     * @param Request $request
     */
    public function addSeries($args, $request)
    {
        // Calling editSeries with an empty ID will add
        // a new series.
        return $this->editSeries($args, $request);
    }

    /**
     * An action to edit a series
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function editSeries($args, $request)
    {
        $seriesId = $args['seriesId'] ?? null;
        $this->setupTemplate($request);

        $seriesForm = new SeriesForm($request, $seriesId);
        $seriesForm->initData();
        return new JSONMessage(true, $seriesForm->fetch($request));
    }

    /**
     * Update a series
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function updateSeries($args, $request)
    {
        $seriesId = $request->getUserVar('seriesId');

        $seriesForm = new SeriesForm($request, $seriesId);
        $seriesForm->readInputData();

        if ($seriesForm->validate()) {
            $seriesForm->execute();
            $notificationManager = new NotificationManager();
            $notificationManager->createTrivialNotification($request->getUser()->getId());
            return DAO::getDataChangedEvent($seriesForm->getSeriesId());
        } else {
            return new JSONMessage(false);
        }
    }

    /**
     * Delete a series
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function deleteSeries($args, $request)
    {
        $press = $request->getPress();
        $series = Repo::section()->get($request->getUserVar('seriesId'), $press->getId());

        if (!$series) {
            return $this->sendErrorToUser($request->getUser()->getId(), 'manager.setup.errorDeletingItem');
        }

        // Validate if it can be deleted
        $seriesEmpty = Repo::section()->isEmpty($series->getId(), $press->getId());
        if (!$seriesEmpty) {
            return $this->sendErrorToUser($request->getUser()->getId(), 'manager.sections.alertDelete', $series->getId());
        }

        Repo::section()->delete($series);

        return DAO::getDataChangedEvent($series->getId());
    }

    /**
     * Deactivate a series.
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function deactivateSeries($args, $request)
    {
        // Identify the current series
        $seriesId = (int) $request->getUserVar('seriesKey');

        // Identify the context id.
        $context = $request->getContext();

        // Validate if it can be inactive
        $series = Repo::section()->get($seriesId, $context->getId());
        if ($request->checkCSRF() && isset($series) && !$series->getIsInactive()) {
            $series->setIsInactive(1);
            Repo::section()->edit($series, []);

            // Create the notification.
            $notificationMgr = new NotificationManager();
            $user = $request->getUser();
            $notificationMgr->createTrivialNotification($user->getId());

            return DAO::getDataChangedEvent($seriesId);
        }

        return new JSONMessage(false);
    }

    /**
     * Activate a series.
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function activateSeries($args, $request)
    {
        // Identify the current series
        $seriesId = (int) $request->getUserVar('seriesKey');

        // Identify the context id.
        $context = $request->getContext();

        // Get series object
        $series = Repo::section()->get($seriesId, $context->getId());

        if ($request->checkCSRF() && isset($series) && $series->getIsInactive()) {
            $series->setIsInactive(0);
            Repo::section()->edit($series, []);

            // Create the notification.
            $notificationMgr = new NotificationManager();
            $user = $request->getUser();
            $notificationMgr->createTrivialNotification($user->getId());

            return DAO::getDataChangedEvent($seriesId);
        }

        return new JSONMessage(false);
    }

    private function sendErrorToUser(int $userId, string $errorKey, ?int $elementId = null): JSONMessage
    {
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification(
            $userId,
            Notification::NOTIFICATION_TYPE_ERROR,
            ['contents' => __($errorKey)]
        );

        return DAO::getDataChangedEvent($elementId);
    }
}
