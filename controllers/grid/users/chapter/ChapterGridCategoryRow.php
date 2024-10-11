<?php

/**
 * @file controllers/grid/users/chapter/ChapterGridCategoryRow.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterGridCategoryRow
 *
 * @ingroup controllers_grid_users_chapter
 *
 * @brief Chapter grid category row definition
 */

namespace APP\controllers\grid\users\chapter;

use APP\monograph\Chapter;
use APP\publication\Publication;
use APP\submission\Submission;
use PKP\controllers\grid\GridCategoryRow;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\RemoteActionConfirmationModal;

class ChapterGridCategoryRow extends GridCategoryRow
{
    /** @var Submission */
    public $_monograph;

    /** @var Publication */
    public $_publication;

    /** @var Chapter */
    public $_chapter;

    /** @var bool $_readOnly */
    public $_readOnly;

    /**
     * Constructor
     */
    public function __construct($monograph, $publication, $readOnly = false)
    {
        $this->_monograph = $monograph;
        $this->_publication = $publication;
        $this->_readOnly = $readOnly;
        parent::__construct();
    }

    //
    // Overridden methods from GridCategoryRow
    //
    /**
     * @copydoc GridCategoryRow::initialize()
     *
     * @param null|mixed $template
     */
    public function initialize($request, $template = null)
    {
        // Do the default initialization
        parent::initialize($request, $template);

        // Retrieve the monograph id from the request
        $monograph = $this->getMonograph();

        // Is this a new row or an existing row?
        $chapterId = $this->getId();
        if (!empty($chapterId) && is_numeric($chapterId)) {
            $chapter = $this->getData();
            $this->_chapter = $chapter;

            // Only add row actions if this is an existing row and the grid is not 'read only'
            if (!$this->isReadOnly()) {
                $router = $request->getRouter();
                $actionArgs = [
                    'submissionId' => $monograph->getId(),
                    'publicationId' => $this->getPublication()->getId(),
                    'chapterId' => $chapterId
                ];

                $this->addAction(
                    new LinkAction(
                        'deleteChapter',
                        new RemoteActionConfirmationModal(
                            $request->getSession(),
                            __('common.confirmDelete'),
                            __('common.delete'),
                            $router->url($request, null, null, 'deleteChapter', null, $actionArgs),
                            'negative'
                        ),
                        __('common.delete'),
                        'delete'
                    )
                );
            }
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

    /**
     * Get the publication for this row (already authorized)
     *
     * @return Publication
     */
    public function getPublication()
    {
        return $this->_publication;
    }

    /**
     * Get the chapter for this row
     *
     * @return Chapter
     */
    public function getChapter()
    {
        return $this->_chapter;
    }

    /**
     * Determine if this grid row should be read only.
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->_readOnly;
    }
}
