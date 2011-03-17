<?php

/**
 * @file controllers/grid/system/preparedEmails/PreparedEmailsGridCellProvider.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridCellProvider
 * @ingroup controllers_grid_settings_preparedEmails
 *
 * @brief Class for a prepared email grid column's cell provider
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class PreparedEmailsGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function PreparedEmailsGridCellProvider() {
		parent::GridCellProvider();
	}

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $element mixed
	 * @param $columnId string
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		$roleDao =& DAORegistry::getDAO('RoleDAO'); /* @var $roleDao RoleDAO */
		switch ($columnId) {
			case 'name':
				$label = $element->getEmailKey();
				return array('label' => str_replace('_', ' ', $label));
			case 'sender':
				$roleId = $element->getFromRoleId();
				$label = $roleDao->getRoleNames(false, array($roleId));
				return array('label' => Locale::translate(array_shift($label)));
			case 'recipient':
				$roleId = $element->getToRoleId();
				$label = $roleDao->getRoleNames(false, array($roleId));
				return array('label' => Locale::translate(array_shift($label)));
			case 'subject':
				$locale = Locale::getLocale();
				$label = $element->getSubject();
				return array('label' => $label);
			case 'enabled':
				return array('isChecked' => $element->getEnabled());
		}
	}
}

?>
