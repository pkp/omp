<?php

/**
 * @file controllers/grid/plugins/PluginGridCellProvider.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginGridCellProvider
 * @ingroup controllers_grid_plugins
 *
 * @brief Cell provider for columns in a plugin grid.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class PluginGridCellProvider extends GridCellProvider {

	/**
	 * Constructor
	 */
	function PluginGridCellProvider() {
		parent::GridCellProvider();
	}

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, &$column) {
		$plugin =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($plugin, 'Plugin') && !empty($columnId));

		switch ($columnId) {
			case 'name':
				return array('label' => $plugin->getDisplayName());
				break;
			case 'category':
				return array('label' => $plugin->getCategory());
				break;
			case 'description':
				return array('label' => $plugin->getDescription());
				break;
			case 'enabled':
				$enabledLabel = __('common.no');
				if (is_callable(array($plugin, 'getEnabled'))) {
					if ($plugin->getEnabled()) {
						$enabledLabel = __('common.yes');
					}
				}
				return array('label' => $enabledLabel);
			default:
				break;
		}

		return parent::getTemplateVarsFromRowColumn($row, $column);
	}
}

?>
