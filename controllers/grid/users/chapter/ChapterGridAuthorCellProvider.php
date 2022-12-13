<?php

/**
 * @file controllers/grid/users/chapter/ChapterGridAuthorCellProvider.inc.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DataObjectGridCellProvider
 * @ingroup controllers_grid_users_chapter
 *
 * @brief Base class for a cell provider that can retrieve labels for submission contributors
 */

namespace APP\controllers\grid\users\chapter;

use APP\author\Author;
use APP\publication\Publication;
use Exception;
use PKP\controllers\grid\DataObjectGridCellProvider;

class ChapterGridAuthorCellProvider extends DataObjectGridCellProvider
{
    private $_publication;

    public function __construct(Publication $publication)
    {
        $this->_publication = $publication;
    }

    //
    // Template methods from GridCellProvider
    //
    /**
     * Extracts variables for a given column from a data element
     * so that they may be assigned to template before rendering.
     *
     * @param $row GridRow
     * @param $column GridColumn
     *
     * @return array
     */
    public function getTemplateVarsFromRowColumn($row, $column)
    {
        $element = $row->getData();
        $columnId = $column->getId();
        if (!is_a($element, Author::class) && empty($columnId)) {
            throw new Exception('Author grid cell provider expected APP\author\Author and column id.');
        }
        switch ($columnId) {
            case 'name':
                return ['label' => $element->getFullName()];
            case 'role':
                return ['label' => $element->getLocalizedUserGroupName()];
            case 'email':
                return parent::getTemplateVarsFromRowColumn($row, $column);
        }
    }
}
