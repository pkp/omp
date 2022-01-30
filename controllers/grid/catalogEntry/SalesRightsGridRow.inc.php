<?php

/**
 * @file controllers/grid/catalogEntry/SalesRightsGridRow.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SalesRightsGridRow
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Sales Rights grid row definition
 */

use PKP\controllers\grid\GridRow;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\request\RemoteActionConfirmationModal;

class SalesRightsGridRow extends GridRow
{
    /** @var Monograph */
    public $_monograph;

    /**
     * Constructor
     */
    public function __construct($monograph)
    {
        $this->_monograph = $monograph;
        parent::__construct();
    }

    //
    // Overridden methods from GridRow
    //
    /**
     * @copydoc GridRow::initialize()
     *
     * @param null|mixed $template
     */
    public function initialize($request, $template = null)
    {
        // Do the default initialization
        parent::initialize($request, $template);

        $monograph = $this->getMonograph();

        // Is this a new row or an existing row?
        $salesRights = $this->_data;

        if ($salesRights != null && is_numeric($salesRights->getId())) {
            $router = $request->getRouter();
            $actionArgs = [
                'submissionId' => $monograph->getId(),
                'salesRightsId' => $salesRights->getId()
            ];

            // Add row-level actions
            $this->addAction(
                new LinkAction(
                    'editRights',
                    new AjaxModal(
                        $router->url($request, null, null, 'editRights', null, $actionArgs),
                        __('grid.action.edit'),
                        'modal_edit'
                    ),
                    __('grid.action.edit'),
                    'edit'
                )
            );

            $this->addAction(
                new LinkAction(
                    'deleteRights',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('common.confirmDelete'),
                        __('common.delete'),
                        $router->url($request, null, null, 'deleteRights', null, $actionArgs),
                        'modal_delete'
                    ),
                    __('grid.action.delete'),
                    'delete'
                )
            );
        }
    }

    /**
     * Get the monograph for this row (already authorized)
     *
     * @return Monograph
     */
    public function &getMonograph()
    {
        return $this->_monograph;
    }
}
