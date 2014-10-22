<?php

/**
 * @file controllers/listbuilder/LocaleFileListbuilderGridCellProvider.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LocaleFileListbuilderGridCellProvider
 * @ingroup controllers_listbuilder
 *
 * @brief Provide labels for locale file listbuilder.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class LocaleFileListbuilderGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function LocaleFileListbuilderGridCellProvider() {
		parent::GridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * @copydoc GridCellProvider::getTemplateVarsFromRowColumn()
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		switch ($column->getId()) {
			case 'key':
				return array('labelKey' => $row->getId(), 'label' => $row->getId());
			case 'value':
				return array('labelKey' => $row->getId(), 'label' => $row->getData());
		}
		assert(false);
	}
}

?>
