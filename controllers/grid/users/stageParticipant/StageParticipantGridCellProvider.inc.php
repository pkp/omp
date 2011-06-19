<?php

/**
 * @file controllers/grid/users/stageParticipant/StageParticipantGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataObjectGridCellProvider
 * @ingroup controllers_grid_users_submissionContributor
 *
 * @brief Base class for a cell provider that can retrieve labels for submission contributors
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class StageParticipantGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function StageParticipantGridCellProvider() {
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
		$users =& $row->getData();
		$columnId = $column->getId();

		switch ($columnId) {
			case 'participants':
				$userNameString = '-';
				// Put all assigned user names into an array
				if(!isset($users)) {
					return array('label' => $userNameString);
				}
				$userNames = array();
				while ($user =& $users->next()) {
					$userNames[] = $user->getFullName();
                    unset($user);
				}
                unset($users);

				// If we have no users for this group, we display a hyphen, else combine the names
				if (!empty($userNames)) $userNameString = implode (' - ', $userNames);

				return array('label' => $userNameString);
			case 'group':
				$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
				$userGroup =& $userGroupDao->getById($row->getId());
				return array('label' => $userGroup->getLocalizedName());
		}
	}
}

?>
