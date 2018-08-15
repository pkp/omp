<?php

/**
 * @file controllers/grid/content/spotlights/SpotlightsGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightsGridCellProvider
 * @ingroup controllers_grid_content_spotlights
 *
 * @brief Base class for a cell provider that can retrieve labels for spotlights
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class SpotlightsGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$data =& $row->getData();
		$element =& $data;

		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'type':
				return array('label' => $element->getLocalizedType());
			case 'title':
				return array('label' => $element->getLocalizedTitle());
			case 'itemTitle': {
				$item = $element->getSpotlightItem();
				return array('label' => $item->getLocalizedTitle());
			}
		}
	}
}


