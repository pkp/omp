<?php

/**
 * @file controllers/grid/catalogEntry/PublicationDateGridRow.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationDateGridRow
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Publication Date grid row definition
 */

namespace APP\controllers\grid\catalogEntry;

use PKP\controllers\grid\GridRow;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\request\RemoteActionConfirmationModal;

class PublicationDateGridRow extends GridRow
{
    /** @var Monograph */
    public $_monograph;

    /** @var Publication */
    public $_publication;

    /**
     * Constructor
     *
     * @param Monograph $monograph
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
        $publicationDate = $this->_data;

        if ($publicationDate != null && is_numeric($publicationDate->getId())) {
            $router = $request->getRouter();
            $actionArgs = [
                'submissionId' => $monograph->getId(),
                'publicationId' => $this->_publication->getId(),
                'publicationDateId' => $publicationDate->getId()
            ];

            // Add row-level actions
            $this->addAction(
                new LinkAction(
                    'editDate',
                    new AjaxModal(
                        $router->url($request, null, null, 'editDate', null, $actionArgs),
                        __('grid.action.edit'),
                        'modal_edit'
                    ),
                    __('grid.action.edit'),
                    'edit'
                )
            );

            $this->addAction(
                new LinkAction(
                    'deleteDate',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('common.confirmDelete'),
                        __('common.delete'),
                        $router->url($request, null, null, 'deleteDate', null, $actionArgs),
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
    public function getMonograph()
    {
        return $this->_monograph;
    }
}
