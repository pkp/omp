<?php

/**
 * @file controllers/grid/catalogEntry/IdentificationCodeGridRow.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IdentificationCodeGridRow
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Identification Code grid row definition
 */

namespace APP\controllers\grid\catalogEntry;

use APP\publication\Publication;
use APP\submission\Submission;
use PKP\controllers\grid\GridRow;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\request\RemoteActionConfirmationModal;

class IdentificationCodeGridRow extends GridRow
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
        $identificationCode = $this->_data;

        if ($identificationCode != null && is_numeric($identificationCode->getId())) {
            $router = $request->getRouter();
            $actionArgs = [
                'submissionId' => $monograph->getId(),
                'publicationId' => $this->_publication->getId(),
                'identificationCodeId' => $identificationCode->getId()
            ];

            // Add row-level actions
            $this->addAction(
                new LinkAction(
                    'editCode',
                    new AjaxModal(
                        $router->url($request, null, null, 'editCode', null, $actionArgs),
                        __('grid.action.edit'),
                        'side-modal'
                    ),
                    __('grid.action.edit'),
                    'edit'
                )
            );

            $this->addAction(
                new LinkAction(
                    'deleteCode',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('common.confirmDelete'),
                        __('common.delete'),
                        $router->url($request, null, null, 'deleteCode', null, $actionArgs),
                        'negative'
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
