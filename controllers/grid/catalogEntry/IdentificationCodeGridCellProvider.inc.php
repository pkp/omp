<?php

/**
 * @file controllers/grid/catalogEntry/IdentificationCodeGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IdentificationCodeGridCellProvider
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Base class for a cell provider that can retrieve labels for identification codes
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class IdentificationCodeGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function IdentificationCodeGridCellProvider() {
		parent::DataObjectGridCellProvider();
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
			case 'code':
				return array('label' => $element->getNameForONIXCode());
			case 'value':
				return array('label' => $element->getValue());
		}
	}
}

?>
