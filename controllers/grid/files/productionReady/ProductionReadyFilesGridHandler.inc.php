<?php

/**
 * @file controllers/grid/files/productionready/ProductionReadyFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionReadyFilesGridHandler
 * @ingroup controllers_grid_files_productionready
 *
 * @brief Handle the fair copy files grid (displays copyedited files ready to move to proofreading)
 */

import('controllers.grid.files.SubmissionFilesGridHandler');
import('controllers.grid.files.UploaderUserGroupGridColumn');

class ProductionReadyFilesGridHandler extends SubmissionFilesGridHandler {
	/**
	 * Constructor
	 */
	function ProductionReadyFilesGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		parent::SubmissionFilesGridHandler(
			new SubmissionFilesGridDataProvider(MONOGRAPH_FILE_PRODUCTION_READY),
			WORKFLOW_STAGE_ID_PRODUCTION,
			FILE_GRID_ADD|FILE_GRID_DELETE|FILE_GRID_VIEW_NOTES
		);

		$this->addRoleAssignment(
			array(
				ROLE_ID_SERIES_EDITOR,
				ROLE_ID_PRESS_MANAGER,
				ROLE_ID_PRESS_ASSISTANT
			),
			array(
				'fetchGrid', 'fetchRow',
				'addFile',
				'downloadFile', 'downloadAllFiles',
				'deleteFile',
				'signOffFile'
			)
		);

		$this->setTitle('submission.productionReady');
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize($request) {
		parent::initialize($request);

		$currentUser =& $request->getUser();

		// Get all the uploader user group id's
		$uploaderUserGroupIds = array();
		$dataElements =& $this->getGridDataElements($request);
		foreach ($dataElements as $id => $rowElement) {
			$submissionFile =& $rowElement['submissionFile'];
			$uploaderUserGroupIds[] = $submissionFile->getUserGroupId();
		}
		// Make sure each is only present once
		$uploaderUserGroupIds = array_unique($uploaderUserGroupIds);

		// Add a Uploader UserGroup column for each group
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		foreach ($uploaderUserGroupIds as $userGroupId) {
			$userGroup =& $userGroupDao->getById($userGroupId);
			assert(is_a($userGroup, 'UserGroup'));
			$flags = array();
			if ($userGroupDao->userInGroup($currentUser->getId(), $userGroupId)) {
				$flags['myUserGroup'] = true;
			}

			$this->addColumn(new UploaderUserGroupGridColumn($userGroup, $flags));
			unset($userGroup);
		}
	}
}

?>
