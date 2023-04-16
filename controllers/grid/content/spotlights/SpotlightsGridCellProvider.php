<?php

/**
 * @file controllers/grid/content/spotlights/SpotlightsGridCellProvider.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SpotlightsGridCellProvider
 *
 * @ingroup controllers_grid_content_spotlights
 *
 * @brief Base class for a cell provider that can retrieve labels for spotlights
 */

namespace APP\controllers\grid\content\spotlights;

use PKP\controllers\grid\DataObjectGridCellProvider;

class SpotlightsGridCellProvider extends DataObjectGridCellProvider
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
        $data = & $row->getData();
        $element = & $data;

        $columnId = $column->getId();
        assert(is_a($element, 'DataObject') && !empty($columnId));
        switch ($columnId) {
            case 'type':
                return ['label' => $element->getLocalizedType()];
            case 'title':
                return ['label' => $element->getLocalizedTitle()];
            case 'itemTitle': {
                $item = $element->getSpotlightItem();
                return ['label' => $item->getLocalizedTitle()];
            }
        }
    }
}
