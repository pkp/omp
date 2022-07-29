<?php

/**
 * @file controllers/grid/users/chapter/ChapterGridCategoryRowCellProvider.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterGridCategoryRowCellProvider
 * @ingroup controllers_grid
 *
 * @brief Chapter grid category rows cell provider.
 */

namespace APP\controllers\grid\users\chapter;

use PKP\controllers\grid\GridCellProvider;
use PKP\controllers\grid\GridHandler;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;

class ChapterGridCategoryRowCellProvider extends GridCellProvider
{
    public $_readOnly;

    /**
     * @see GridCellProvider::getCellActions()
     */
    public function getCellActions($request, $row, $column, $position = GridHandler::GRID_ACTION_POSITION_DEFAULT)
    {
        if ($column->getId() == 'name' && !$row->isReadOnly()) {
            $chapter = $row->getData();
            $monograph = $row->getMonograph();
            $publication = $row->getPublication();

            $router = $request->getRouter();
            $actionArgs = [
                'submissionId' => $monograph->getId(),
                'publicationId' => $publication->getId(),
                'chapterId' => $chapter->getId()
            ];

            return [new LinkAction(
                'editChapter',
                new AjaxModal(
                    $router->url($request, null, null, 'editChapter', null, $actionArgs),
                    __('submission.chapter.editChapter'),
                    'modal_edit'
                ),
                htmlspecialchars($chapter->getLocalizedTitle())
            )
            ];
        }
        return parent::getCellActions($request, $row, $column, $position);
    }

    /**
     * @see GridCellProvider::getTemplateVarsFromRowColumn()
     */
    public function getTemplateVarsFromRowColumn($row, $column)
    {
        // If row is not read only, the cell will contains a link
        // action. See getCellActions() above.
        if ($column->getId() == 'name' && $row->isReadOnly()) {
            $chapter = $row->getData();
            $label = $chapter->getLocalizedTitle();
        } else {
            $label = '';
        }

        return ['label' => $label];
    }
}
