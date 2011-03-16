<?php
/**
 * @defgroup controllers_grid_files_fileSignoff
 */

/**
 * @file controllers/grid/files/fileSignoff/FileSignoffGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileSignoffGridHandler
 * @ingroup controllers_grid_files_fileSignoff
 *
 * @brief Base grid for file lists that allow for file signoff. This grid shows
 *  signoff columns in addition to the file name.
 */

import('controllers.grid.files.SubmissionFilesGridHandler');
import('controllers.grid.files.UploaderGridColumn');

class FileSignoffGridHandler extends SubmissionFilesGridHandler {

	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function FileSignoffGridHandler($dataProvider, $stageId, $capabilities) {
		parent::SubmissionFilesGridHandler($dataProvider, $stageId, $capabilities);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Retrieve the submission files in this grid.
		$submissionFiles =& $this->getGridDataElements($request);

		// Go through the list of files and identify all uploader user groups.
		$uploaderUserGroups = array();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		foreach($submissionFiles as $submissionFile) { /* @var $submissionFile MonographFile */
			if (!isset($uploaderUserGroups[$submissionFile->getUserGroupId()])) {
				// Retrieve the full user group.
				$userGroupId = $submissionFile->getUserGroupId();
				$userGroup =& $userGroupDao->getById($userGroupId);
				assert(is_a($userGroup, 'UserGroup'));
				$uploaderUserGroups[$userGroupId] =& $userGroup;
				unset($userGroup);
			}
		}

		// Add uploader user group columns.
		foreach($uploaderUserGroups as $uploaderUserGroup) { /* @var $uploaderUserGroup UserGroup */
			$this->addColumn(new UploaderGridColumn($uploaderUserGroup));
		}

		// Go through the list of workflow stage participants and
		// identify all assigned press and series editors.
		$stageId = $this->getStageId();
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$monograph =& $this->getMonograph();
		$signoffDao->getAllBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monograph->getId());
	}
}