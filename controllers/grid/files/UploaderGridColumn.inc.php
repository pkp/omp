<?php

/**
 * @file controllers/grid/files/UploaderGridColumn.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UploaderGridColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a grid column that displays uploader user groups.
 */

import('controllers.grid.files.UserGroupColumn');

class UploaderGridColumn extends UserGroupColumn {

	/**
	 * Constructor
	 * @param $uploaderUserGroup UserGroup The user
	 *  group to be represented in this column.
	 */
	function UploaderGridColumn(&$uploaderUserGroup) {
		parent::UserGroupColumn($uploaderUserGroup, 'uploader');
	}


	//
	// Overridden methods from UserGroupColumn
	//
	/**
	 * @see UserGroupColiumn::getTemplateVarsFromRowColumn()
	 */
	function getTemplateVarsFromRow($row) {
		$templateVars = parent::getTemplateVarsFromRow($row);

		// Retrieve the monograph file.
		$monographFile =& $this->getMonographFile($row);

		// By default we return an empty cell.
		$templateVars = array('status' => '');

		// Find out whether the uploader of the current file
		// belongs to the user group displayed in this column.
		$uploaderUserGroup =& $this->getUserGroup();
		if ($uploaderUserGroup->getId() == $monographFile->getUserGroupId()) {
			// Show that this column's user group is the uploading
			// user group.
			$templateVars = array('status' => 'uploaded');
		}

		return $templateVars;
	}
}

?>
