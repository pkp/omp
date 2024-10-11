<?php

/**
 * @file controllers/grid/catalogEntry/MarketsGridRow.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MarketsGridRow
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Markets grid row definition
 */

namespace APP\controllers\grid\catalogEntry;

use APP\publication\Publication;
use APP\submission\Submission;
use PKP\controllers\grid\GridRow;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\request\RemoteActionConfirmationModal;

class MarketsGridRow extends GridRow
{
    /** @var Submission */
    public $_monograph;

    /** @var Publication */
    public $_publication;

    /**
     * Constructor
     */
    public function __construct($monograph, $publication)
    {
        $this->_monograph = $monograph;
        $this->_publication = $publication;
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
        $market = $this->_data;

        if ($market != null && is_numeric($market->getId())) {
            $router = $request->getRouter();
            $actionArgs = [
                'submissionId' => $monograph->getId(),
                'publicationId' => $this->_publication->getId(),
                'marketId' => $market->getId()
            ];

            // Add row-level actions
            $this->addAction(
                new LinkAction(
                    'editMarket',
                    new AjaxModal(
                        $router->url($request, null, null, 'editMarket', null, $actionArgs),
                        __('grid.action.edit'),
                        'side-modal'
                    ),
                    __('grid.action.edit'),
                    'edit'
                )
            );

            $this->addAction(
                new LinkAction(
                    'deleteMarket',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('common.confirmDelete'),
                        __('common.delete'),
                        $router->url($request, null, null, 'deleteMarket', null, $actionArgs),
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
     * @return Submission
     */
    public function getMonograph()
    {
        return $this->_monograph;
    }
}
