<?php

/**
 * @file controllers/grid/catalogEntry/MarketsGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MarketsGridCellProvider
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Base class for a cell provider that can retrieve labels for market regions
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class MarketsGridCellProvider extends DataObjectGridCellProvider {
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
		$element = $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'territory':
				return array('label' => $element->getTerritoriesAsString());
			case 'rep':
				return array('label' => $element->getAssignedRepresentativeNames());
			case 'price':
				return array('label' => $element->getPrice() . $element->getCurrencyCode());
		}
	}
}


