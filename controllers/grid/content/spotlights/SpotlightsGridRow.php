<?php

/**
 * @file controllers/grid/content/spotlights/SpotlightsGridRow.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SpotlightsGridRow
 *
 * @ingroup controllers_grid_content_spotlights
 *
 * @brief Spotlights grid row definition
 */

namespace APP\controllers\grid\content\spotlights;

use APP\press\Press;
use PKP\controllers\grid\GridRow;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\request\RemoteActionConfirmationModal;

class SpotlightsGridRow extends GridRow
{
    /** @var Press */
    public $_press;

    /**
     * Constructor
     *
     * @param Press $press
     */
    public function __construct($press)
    {
        $this->setPress($press);
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

        $press = $this->getPress();

        // Is this a new row or an existing row?
        $spotlight = $this->_data;
        if ($spotlight != null && is_numeric($spotlight->getId())) {
            $router = $request->getRouter();
            $actionArgs = [
                'pressId' => $press->getId(),
                'spotlightId' => $spotlight->getId()
            ];

            // Add row-level actions
            $this->addAction(
                new LinkAction(
                    'editSpotlight',
                    new AjaxModal(
                        $router->url($request, null, null, 'editSpotlight', null, $actionArgs),
                        __('grid.action.edit'),
                        'modal_edit'
                    ),
                    __('grid.action.edit'),
                    'edit'
                )
            );

            $this->addAction(
                new LinkAction(
                    'deleteSpotlight',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('common.confirmDelete'),
                        __('common.delete'),
                        $router->url($request, null, null, 'deleteSpotlight', null, $actionArgs),
                        'modal_delete'
                    ),
                    __('grid.action.delete'),
                    'delete'
                )
            );
        }
    }

    /**
     * Get the press for this row (already authorized)
     *
     * @return Press
     */
    public function getPress()
    {
        return $this->_press;
    }

    /**
     * Set the press for this row (already authorized)
     */
    public function setPress($press)
    {
        $this->_press = $press;
    }
}
