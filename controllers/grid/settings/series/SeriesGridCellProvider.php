<?php
/**
 * @file controllers/grid/settings/series/SeriesGridCellProvider.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SeriesGridCellProvider
 *
 * @ingroup controllers_grid_settings_series
 *
* @brief Grid cell provider for series grid
 */

namespace APP\controllers\grid\settings\series;

use PKP\controllers\grid\GridCellProvider;
use PKP\controllers\grid\GridColumn;
use PKP\controllers\grid\GridHandler;
use PKP\controllers\grid\GridRow;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\RemoteActionConfirmationModal;

class SeriesGridCellProvider extends GridCellProvider
{
    /**
     * Extracts variables for a given column from a data element
     * so that they may be assigned to template before rendering.
     *
     * @param GridRow $row
     * @param GridColumn $column
     *
     * @return array
     */
    public function getTemplateVarsFromRowColumn($row, $column)
    {
        $element = $row->getData();
        $columnId = $column->getId();
        assert(!empty($columnId));
        switch ($columnId) {
            case 'inactive':
                return ['selected' => $element['inactive']];
        }
        return parent::getTemplateVarsFromRowColumn($row, $column);
    }

    /**
     * @see GridCellProvider::getCellActions()
     */
    public function getCellActions($request, $row, $column, $position = GridHandler::GRID_ACTION_POSITION_DEFAULT)
    {
        switch ($column->getId()) {
            case 'inactive':
                $element = $row->getData(); /** @var array $element */

                $router = $request->getRouter();

                if ($element['inactive']) {
                    return [new LinkAction(
                        'activateSeries',
                        new RemoteActionConfirmationModal(
                            $request->getSession(),
                            __('manager.sections.confirmActivateSection'),
                            null,
                            $router->url(
                                $request,
                                null,
                                'grid.settings.series.SeriesGridHandler',
                                'activateSeries',
                                null,
                                ['seriesKey' => $row->getId()]
                            ),
                            'primary'
                        )
                    )];
                } else {
                    return [new LinkAction(
                        'deactivateSeries',
                        new RemoteActionConfirmationModal(
                            $request->getSession(),
                            __('manager.sections.confirmDeactivateSection'),
                            null,
                            $router->url(
                                $request,
                                null,
                                'grid.settings.series.SeriesGridHandler',
                                'deactivateSeries',
                                null,
                                ['seriesKey' => $row->getId()]
                            ),
                            'negative'
                        )
                    )];
                }
        }
        return parent::getCellActions($request, $row, $column, $position);
    }
}
