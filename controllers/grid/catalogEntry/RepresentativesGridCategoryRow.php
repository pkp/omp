<?php

/**
 * @file controllers/grid/catalogEntry/RepresentativesGridCategoryRow.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RepresentativesGridCategoryRow
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Representatives grid category row definition
 */

namespace APP\controllers\grid\catalogEntry;

use PKP\controllers\grid\GridCategoryRow;

class RepresentativesGridCategoryRow extends GridCategoryRow
{
    //
    // Overridden methods from GridCategoryRow
    //

    /**
     * Category rows only have one cell and one label.  This is it.
     * return string
     */
    public function getCategoryLabel()
    {
        $data = $this->getData();
        return __($data['name']);
    }
}
