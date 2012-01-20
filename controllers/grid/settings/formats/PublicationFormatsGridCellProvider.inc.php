<?php

/**
 * @file controllers/grid/settings/formats/PublicationFormatsGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatsGridCellProvider
 * @ingroup controllers_grid_settings_formats
 *
 * @brief Base class for a cell provider that can retrieve labels for publication formats
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class PublicationFormatsGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function PublicationFormatsGridCellProvider() {
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
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'code':
				return array('label' => $element->getNameForONIXCode());
			case 'name':
				return array('label' => $element->getLocalizedName());
			case 'enabled':
				return array('isChecked' => $element->getEnabled() ? true : false);
		}
	}
}

?>
