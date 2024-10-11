<?php

/**
 * @file controllers/grid/settings/series/SeriesGridRow.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SeriesGridRow
 *
 * @ingroup controllers_grid_settings_series
 *
 * @brief Handle series grid row requests.
 */

namespace APP\controllers\grid\settings\series;

use PKP\controllers\grid\GridRow;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\request\RemoteActionConfirmationModal;

class SeriesGridRow extends GridRow
{
    //
    // Overridden template methods
    //
    /**
     * @copydoc GridRow::initialize()
     *
     * @param null|mixed $template
     */
    public function initialize($request, $template = null)
    {
        parent::initialize($request, $template);

        // Is this a new row or an existing row?
        $seriesId = $this->getId();
        if (!empty($seriesId) && is_numeric($seriesId)) {
            $router = $request->getRouter();

            $this->addAction(
                new LinkAction(
                    'editSeries',
                    new AjaxModal(
                        $router->url($request, null, null, 'editSeries', null, ['seriesId' => $seriesId]),
                        __('grid.action.edit'),
                        'side-modal',
                        true
                    ),
                    __('grid.action.edit'),
                    'edit'
                )
            );

            $this->addAction(
                new LinkAction(
                    'deleteSeries',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('common.confirmDelete'),
                        __('grid.action.delete'),
                        $router->url($request, null, null, 'deleteSeries', null, ['seriesId' => $seriesId]),
                        'modal_delete'
                    ),
                    __('grid.action.delete'),
                    'delete'
                )
            );
        }
    }
}
