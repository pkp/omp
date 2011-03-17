<?php

/**
 * @file classes/controllers/grid/users/user/UserEnrollmentGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserEnrollmentGridCellProvider
 * @ingroup controllers_grid_users_user
 *
 * @brief Cell provider that retrieves user data 
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class UserEnrollmentGridCellProvider extends DataObjectGridCellProvider {

	/** @var For press specific user settings **/
	var $pressId;

	/**
	 * Constructor
	 */
	function UserEnrollmentGridCellProvider($pressId) {
		$this->pressId = $pressId;

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

		assert(is_a($element, 'User') && !empty($columnId));
		switch ($columnId) {
			case 'roles': // User's roles
				$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
				$userGroups =& $userGroupDao->getByUserId($row->getId(), $this->pressId);
				return array('userGroups' => $userGroups);
		}
	}
}

?>
