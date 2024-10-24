<?php

/**
 * @file controllers/grid/catalogEntry/MarketsGridCellProvider.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MarketsGridCellProvider
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Base class for a cell provider that can retrieve labels for market regions
 */

namespace APP\controllers\grid\catalogEntry;

use APP\publicationFormat\Market;
use PKP\controllers\grid\DataObjectGridCellProvider;
use PKP\controllers\grid\GridColumn;
use PKP\controllers\grid\GridRow;
use PKP\core\DataObject;

class MarketsGridCellProvider extends DataObjectGridCellProvider
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
        /** @var Market $element */
        switch ($columnId) {
            case 'territory':
                return ['label' => $element->getTerritoriesAsString()];
            case 'rep':
                return ['label' => $element->getAssignedRepresentativeNames()];
            case 'price':
                return ['label' => $element->getPrice() . $element->getCurrencyCode()];
        }
    }
}
