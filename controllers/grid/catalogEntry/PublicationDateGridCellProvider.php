<?php

/**
 * @file controllers/grid/catalogEntry/PublicationDateGridCellProvider.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationDateGridCellProvider
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Base class for a cell provider that can retrieve labels for publication dates
 */

namespace APP\controllers\grid\catalogEntry;

use APP\publicationFormat\PublicationDate;
use PKP\controllers\grid\DataObjectGridCellProvider;
use PKP\controllers\grid\GridColumn;
use PKP\controllers\grid\GridRow;
use PKP\core\DataObject;

class PublicationDateGridCellProvider extends DataObjectGridCellProvider
{
    //
    // Template methods from GridCellProvider
    //
    /**
     * Extracts variables for a given column from a data element
     * so that they may be assigned to template before rendering.
     *
     * @param GridRow $row
     * @param GridColumn $column
     *
     * @return array
     */
    public function getTemplateVarsFromRowColumn($row, $column)
    {
        $element = $row->getData();
        $columnId = $column->getId();
        assert($element instanceof DataObject && !empty($columnId));
        /** @var PublicationDate $element */
        switch ($columnId) {
            case 'code':
                return ['label' => $element->getNameForONIXCode()];
            case 'value':
                return ['label' => $element->getDate()];
        }
    }
}
