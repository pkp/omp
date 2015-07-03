<?php

/**
 * @file plugins/generic/addThis/AddThisStatisticsGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddThisStatisticsGridCellProvider
 * @ingroup plugins_generic_addThis
 *
 * @brief Base class for a cell provider that can retrieve labels for AddThis stats.
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class AddThisStatisticsGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function AddThisStatisticsGridCellProvider() {
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
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(!empty($columnId));
		switch ($columnId) {
			case 'url':
				return array('label' => '<a href="' . String::stripUnsafeHtml($element['url']) . '" target="_blank">' . String::stripUnsafeHtml($element['url']) . '</a>');
			case 'shares':
				return array('label' => $element['shares']);
		}
	}
}

?>
