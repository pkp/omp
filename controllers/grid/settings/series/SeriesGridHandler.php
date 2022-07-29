<?php

/**
 * @file controllers/grid/settings/series/SeriesGridHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SeriesGridHandler
 * @ingroup controllers_grid_settings_series
 *
 * @brief Handle series grid requests.
 */

namespace APP\controllers\grid\settings\series;

use APP\controllers\grid\settings\series\SeriesGridCellProvider;
use APP\controllers\grid\settings\series\form\SeriesForm;
use PKP\db\DAO;
use PKP\controllers\grid\settings\SetupGridHandler;
use APP\controllers\grid\settings\series\SeriesGridRow;
use APP\notification\NotificationManager;
use PKP\controllers\grid\feature\OrderGridItemsFeature;
use PKP\controllers\grid\GridColumn;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\db\DAORegistry;
use PKP\security\Role;

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
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $subEditorsDao = DAORegistry::getDAO('SubEditorsDAO'); /** @var SubEditorsDAO $subEditorsDao */
        $seriesIterator = $seriesDao->getByPressId($press->getId());

        $gridData = [];
        while ($series = $seriesIterator->next()) {
            // Get the categories data for the row
            $categories = $seriesDao->getCategories($series->getId(), $press->getId());
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
            $assignedSeriesEditors = $subEditorsDao->getBySubmissionGroupId($series->getId(), ASSOC_TYPE_SECTION, $press->getId());
            if (empty($assignedSeriesEditors)) {
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
                    'modal_manage'
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
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $press = $request->getPress();
        $series = $seriesDao->getById($rowId, $press->getId());
        $series->setSequence($newSequence);
        $seriesDao->updateObject($series);
    }

    //
    // Public Series Grid Actions
    //
    /**
     * An action to add a new series
     *
     * @param array $args
     * @param PKPRequest $request
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
     * @param PKPRequest $request
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
     * @param PKPRequest $request
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
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function deleteSeries($args, $request)
    {
        $press = $request->getPress();

        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $series = $seriesDao->getById(
            $request->getUserVar('seriesId'),
            $press->getId()
        );

        if (isset($series)) {
            $result = $seriesDao->getByContextId($press->getId());
            $activeSeriesCount = (!$series->getIsInactive()) ? -1 : 0;
            while (!$result->eof()) {
                if (!$result->next()->getIsInactive()) {
                    $activeSeriesCount++;
                }
            }
            if ($activeSeriesCount < 1) {
                return new JSONMessage(false, __('manager.series.confirmDeactivateSeries.error'));
                return false;
            }

            $seriesDao->deleteObject($series);
            return DAO::getDataChangedEvent($series->getId());
        } else {
            return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
        }
    }

    /**
     * Deactivate a series.
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function deactivateSeries($args, $request)
    {
        // Identify the current series
        $seriesId = (int) $request->getUserVar('seriesKey');

        // Identify the context id.
        $context = $request->getContext();

        // Get series object
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        // Validate if it can be inactive
        $seriesIterator = $seriesDao->getByContextId($context->getId(), null, false);
        $activeSeriesCount = 0;
        while ($series = $seriesIterator->next()) {
            if (!$series->getIsInactive()) {
                $activeSeriesCount++;
            }
        }
        if ($activeSeriesCount > 1) {
            $series = $seriesDao->getById($seriesId, $context->getId());

            if ($request->checkCSRF() && isset($series) && !$series->getIsInactive()) {
                $series->setIsInactive(1);
                $seriesDao->updateObject($series);

                // Create the notification.
                $notificationMgr = new NotificationManager();
                $user = $request->getUser();
                $notificationMgr->createTrivialNotification($user->getId());

                return DAO::getDataChangedEvent($seriesId);
            }
        } else {
            // Create the notification.
            $notificationMgr = new NotificationManager();
            $user = $request->getUser();
            $notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_ERROR, ['contents' => __('manager.series.confirmDeactivateSeries.error')]);
            return DAO::getDataChangedEvent($seriesId);
        }

        return new JSONMessage(false);
    }

    /**
     * Activate a series.
     *
     * @param array $args
     * @param PKPRequest $request
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
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $series = $seriesDao->getById($seriesId, $context->getId());

        if ($request->checkCSRF() && isset($series) && $series->getIsInactive()) {
            $series->setIsInactive(0);
            $seriesDao->updateObject($series);

            // Create the notification.
            $notificationMgr = new NotificationManager();
            $user = $request->getUser();
            $notificationMgr->createTrivialNotification($user->getId());

            return DAO::getDataChangedEvent($seriesId);
        }

        return new JSONMessage(false);
    }
}
