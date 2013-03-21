<?php

/**
 * @file controllers/grid/files/SubmissionFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridDataProvider
 * @ingroup controllers_grid_files
 *
 * @brief Provide access to submission file data for grids.
 */


import('lib.pkp.controllers.grid.files.PKPSubmissionFilesGridDataProvider');

class SubmissionFilesGridDataProvider extends PKPSubmissionFilesGridDataProvider {

	/** @var integer */
	var $_stageId;

	/** @var integer */
	var $_fileStage;


	/**
	 * Constructor
	 * @param $fileStage integer One of the SUBMISSION_FILE_* constants.
	 */
	function SubmissionFilesGridDataProvider($fileStage, $viewableOnly = false) {
		parent::PKPSubmissionFilesGridDataProvider($fileStage, $viewableOnly);
	}

	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		$this->setUploaderRoles($roleAssignments);

		import('classes.security.authorization.WorkflowStageAccessPolicy');
		$policy = new WorkflowStageAccessPolicy($request, $args, $roleAssignments, 'submissionId', $this->getStageId());
		return $policy;
	}
}

?>
