<?php

/**
 * @file classes/controllers/grid/users/StageParticipantGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataObjectGridCellProvider
 * @ingroup controllers_grid
 *
 * @brief Base class for a cell provider that can retrieve labels for submission participants
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
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'Signoff') && !empty($columnId));
		switch ($columnId) {
			case 'name':
				$userId = $element->getUserId();
				$userDao =& DAORegistry::getDAO('UserDAO');
				$user =& $userDao->getUser($userId);
				return array('label' => $user->getFullName());
			case 'userGroup':
				$userGroupId = $element->getUserGroupId();
				$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
				$userGroup =& $userGroupDao->getById($userGroupId);
				return array('label' => $userGroup->getLocalizedAbbrev());
		}
	}
}