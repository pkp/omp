<?php

/**
 * @file classes/controllers/grid/users/SubmissionParticipantGridCellProvider.inc.php
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

class SubmissionParticipantGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function SubmissionParticipantGridCellProvider() {
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
			case 'name':
				$userId = $element->getUserId();
			////	$userDao =& DAORegistry::getDAO('UserDAO');
			//	$user =& $userDao->getUser($userId);
				return array('label' => $element->getFullName());
		}
	}
}