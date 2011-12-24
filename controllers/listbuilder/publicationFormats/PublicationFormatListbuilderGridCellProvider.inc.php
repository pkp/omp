<?php

/**
 * @file classes/controllers/listbuilder/publicationFormats/PublicationFormatListbuilderGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatListbuilderGridCellProvider
 * @ingroup controllers_grid
 *
 * @brief class for the Publication Format provider that can retrieve labels from arrays
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class PublicationFormatListbuilderGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function PublicationFormatListbuilderGridCellProvider() {
		parent::GridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * This implementation assumes a simple data element array that
	 * has column ids as keys.
	 * @see GridCellProvider::getTemplateVarsFromRowColumn()
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$format =& $row->getData();
		$columnId = $column->getId();
		assert((is_a($format, 'PublicationFormat')) && !empty($columnId));
		return array('labelKey' => $format->getId(), 'label' => $format->getLocalizedName());
	}
}
?>
