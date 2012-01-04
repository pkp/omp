<?php

/**
 * @file controllers/grid/files/final/form/ManageFinalDraftFilesForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageFinalDraftFilesForm
 * @ingroup controllers_grid_files_finalDraftFiles
 *
 * @brief Form to add files to the final draft files grid
 */

import('controllers.grid.files.form.ManageSubmissionFilesForm');

class ManageFinalDraftFilesForm extends ManageSubmissionFilesForm {

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 */
	function ManageFinalDraftFilesForm($monographId) {
		parent::ManageSubmissionFilesForm($monographId, 'controllers/grid/files/final/manageFinalDraftFiles.tpl');
	}


	//
	// Overridden template methods
	//
	/**
	 * Save Selection of Final Draft files
	 * @param $args array
	 * @param $request PKPRequest
	 * @return array a list of all monograph files marked as "final".
	 */
	function execute($args, &$request, &$stageMonographFiles) {
		parent::execute($args, $request, $stageMonographFiles, MONOGRAPH_FILE_FINAL);
	}
}

?>
