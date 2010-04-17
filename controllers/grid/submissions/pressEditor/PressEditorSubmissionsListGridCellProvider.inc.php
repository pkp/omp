<?php

/**
 * @file classes/controllers/grid/submissions/pressEditor/PressEditorSubmissionsListGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressEditorSubmissionsListGridCellProvider.inc.php
 * @ingroup controllers_grid_submissionsList_pressEditor
 *
 * @brief Class for a cell provider that can retrieve labels from submissions
 */

import('controllers.grid.submissions.SubmissionsListGridCellProvider');

class PressEditorSubmissionsListGridCellProvider extends SubmissionsListGridCellProvider {
	/**
	 * Constructor
	 */
	function SubmissionsListGridCellProvider() {
		parent::DataObjectGridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $element mixed
	 * @param $columnId string
	 * @return array
	 */
	function getTemplateVarsFromElement(&$element, $columnId) {
		assert(is_a($element, 'DataObject') && !empty($columnId));
		// numeric means its a userGroupId column
		if ( is_numeric($columnId) ) {
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$userGroup =& $userGroupDao->getById($columnId);
			$roleId = $userGroup->getRoleId();
			if ( $roleId == ROLE_ID_EDITOR || $roleId == ROLE_ID_SERIES_EDITOR ) {
				// First columns are the PressEditors and SeriesEditors
				// Determine status of editor columns

				// FIXME: need to implement different statuses
				return array('status' => 'new');
			} else if ( $roleId == ROLE_ID_AUTHOR ) {
				// Following columns are potential submitters
				if ( $columnId == $element->getUserGroupId() ) {
					// Show that this column's user group is the submitter
					return array('status' => 'uploaded');
				}
				// If column is not the submitter, cell is always empty.
				return array('status' => '');
			}
		}

		return parent::getTemplateVarsFromElement($element, $columnId);
	}
}