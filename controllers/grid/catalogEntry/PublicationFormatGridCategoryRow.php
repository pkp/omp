<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridCategoryRow.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridCategoryRow
 *
 * @ingroup controllers_grid_representations
 *
 * @brief Representations grid row definition
 */

namespace APP\controllers\grid\catalogEntry;

use APP\publication\Publication;
use APP\submission\Submission;
use PKP\controllers\grid\GridCategoryRow;
use PKP\controllers\grid\GridCellProvider;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\request\RemoteActionConfirmationModal;

class PublicationFormatGridCategoryRow extends GridCategoryRow
{
    /** @var Submission */
    public $_submission;

    /** @var bool */
    protected $_canManage;

    /** @var Publication */
    public $_publication;

    /**
     * Constructor
     *
     * @param Submission $submission
     * @param GridCellProvider $cellProvider
     * @param bool $canManage
     * @param Publication $publication
     */
    public function __construct($submission, $cellProvider, $canManage, $publication)
    {
        $this->_submission = $submission;
        $this->_canManage = $canManage;
        $this->_publication = $publication;
        parent::__construct();
        $this->setCellProvider($cellProvider);
    }

    //
    // Overridden methods from GridCategoryRow
    //
    /**
     * @copydoc GridCategoryRow::getCategoryLabel()
     */
    public function getCategoryLabel()
    {
        return $this->getData()->getLocalizedName();
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

        // Retrieve the submission from the request
        $submission = $this->getSubmission();

        // Is this a new row or an existing row?
        $representation = $this->getData();
        if ($representation && is_numeric($representation->getId()) && $this->_canManage) {
            $router = $request->getRouter();
            $actionArgs = [
                'submissionId' => $submission->getId(),
                'representationId' => $representation->getId(),
                'publicationId' => $this->getPublication()->getId(),
            ];

            // Add row-level actions
            $this->addAction(
                new LinkAction(
                    'editFormat',
                    new AjaxModal(
                        $router->url($request, null, null, 'editFormat', null, $actionArgs),
                        __('grid.action.edit'),
                        'side-modal'
                    ),
                    __('grid.action.edit'),
                    'edit'
                )
            );

            $this->addAction(
                new LinkAction(
                    'deleteFormat',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('common.confirmDelete'),
                        __('common.delete'),
                        $router->url($request, null, null, 'deleteFormat', null, $actionArgs),
                        'negative'
                    ),
                    __('grid.action.delete'),
                    'delete'
                )
            );
        }
    }

    /**
     * Get the submission for this row (already authorized)
     *
     * @return Submission
     */
    public function getSubmission()
    {
        return $this->_submission;
    }

    /**
     * Get the publication for this row (already authorized)
     *
     * @return Publication
     */
    public function getPublication()
    {
        return $this->_publication;
    }
}
