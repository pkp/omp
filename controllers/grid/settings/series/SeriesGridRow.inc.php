<?php

/**
 * @file controllers/grid/settings/series/SeriesGridRow.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SeriesGridRow
 * @ingroup controllers_grid_settings_series
 *
 * @brief Handle series grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\request\RemoteActionConfirmationModal;

class SeriesGridRow extends GridRow
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

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

        $this->setupTemplate($request);

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
                        'modal_edit',
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

    /**
     * @see PKPHandler::setupTemplate()
     */
    public function setupTemplate($request)
    {
        // Load manager translations. FIXME are these needed?
        AppLocale::requireComponents(
            LOCALE_COMPONENT_APP_MANAGER,
            LOCALE_COMPONENT_PKP_COMMON,
            LOCALE_COMPONENT_PKP_USER,
            LOCALE_COMPONENT_APP_COMMON
        );
    }
}
