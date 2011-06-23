<?php

/**
 * @file controllers/listbuilder/settings/DivisionsListbuilderGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DivisionsListbuilderGridCellProvider
 * @ingroup controllers_listbuilder_settings
 *
 * @brief Base class for a cell provider that can retrieve labels from arrays
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class DivisionsListbuilderGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function DivisionsListbuilderGridCellProvider() {
		parent::GridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * This implementation assumes a simple data element array that
	 * has column ids as keys.
	 * @see GridCellProvider::getTemplateVarsFromRowColumn()
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$division =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($division, 'Division') && !empty($columnId));
		switch ($columnId) {
			case 'title':
				return array('labelKey' => $division->getId(), 'label' => $division->getLocalizedTitle());
		}
		// we got an unexpected column
		assert(false);
	}
}

?>
