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
		$signoffUserGroups = array();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		foreach($submissionFiles as $submissionFileData) {
			assert(isset($submissionFileData['submissionFile']));
			$monographFile =& $submissionFileData['submissionFile']; /* @var $submissionFile MonographFile */

			// Add the signoff user groups to the signoff
			// user group list if it had not been added before.
			assert(isset($submissionFileData['signoffs']));
			$fileSignoffs = $submissionFileData['signoffs'];
			foreach($fileSignoffs as $userId => $signoffUserGroups) {
				foreach($signoffUserGroups as $signoffUserGroup) {/* @var $signoffUserGroup UserGroup */
					if (!isset($signoffUserGroups[$signoffUserGroup->getId()])) {
						$signoffUserGroups[$signoffUserGroup->getId()] =& $signoffUserGroup;
					}
				}
			}

			// Add the uploader user group if not already.
			$uploaderUserGroupId = $monographFile->getUserGroupId();
			if (!isset($signoffUserGroups[$uploaderUserGroupId])) {
				// Retrieve the user group object.
				$userGroup =& $userGroupDao->getById($uploaderUserGroupId);
				assert(is_a($userGroup, 'UserGroup'));
				$signoffUserGroups[$uploaderUserGroupId] =& $userGroup;
			}
		}

		// Add user group columns.
		import('controllers.grid.files.SignoffStatusGridColumn');
		foreach($signoffUserGroups as $signoffUserGroup) { /* @var $uploaderUserGroup UserGroup */
			$this->addColumn(new SignoffStatusGridColumn($signoffUserGroup, $this->getStageId(), $this->getRequestArgs()));
		}
	}


	//
	// Implement protected template methods from GridHandler
	//
	/**
	 * @see GridHandler::loadData()
	 */
	function &loadData($request, $filter) {
		// Retrieve all press and series editor user
		// groups from the database.
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$editorGroups = array();
		foreach(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR) as $editorRoleId) {
			$editorGroupFactory =& $userGroupDao->getByRoleId($press->getId(), $editorRoleId);
			while($editorGroup =& $editorGroupFactory->next()) { /* @var $editorGroup UserGroup */
				$editorGroups[$editorGroup->getId()] =& $editorGroup;
				unset($editorGroup);
			}
			unset($editorGroupFactory);
		}

		// Go through the list of workflow stage participants and
		// identify all assigned press and series editors.
		$monograph =& $this->getMonograph();
		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$stageAssignments = $stageAssignmentDao->getBySubmissionAndStageId($monograph->getId(), $this->getStageId());

		$stageEditors = array();
		while($stageAssignment =& $stageAssignments->next()) { /* @var $signoff Signoff */
			if (isset($editorGroups[$stageAssignment->getUserGroupId()])) {
				if (!isset($stageEditors[$stageAssignment->getUserId()])) {
					$stageEditors[$stageAssignment->getUserId()] = array();
				}
				$stageEditors[$stageAssignment->getUserId()][$stageAssignment->getUserGroupId()] =&
						$editorGroups[$stageAssignment->getUserGroupId()];
			}
		}

		// Now go through all files and create a signoff for each of
		// the assigned editors unless that editor is the uploader.
		$submissionFiles =& parent::loadData($request, $filter);
		foreach($submissionFiles as $fileId => $submissionFileData) {
			assert(isset($submissionFileData['submissionFile']));
			$monographFile =& $submissionFileData['submissionFile']; /* @var $monographFile MonographFile */

			// All press and series editors assigned to the stage except
			// for those who uploaded the file have to sign off this file.
			$fileSignoffs = $stageEditors;
			unset($fileSignoffs[$monographFile->getUploaderUserId()]);
			$submissionFiles[$fileId]['signoffs'] = $fileSignoffs;
		}

		return $submissionFiles;
	}


	//
	// Public handler methods
	//
	/**
	 * Sign off the given file revision.
	 * @param $args array
	 * @param $request Request
	 */
	function signOffFiles($args, &$request) {
		// Retrieve the monograph.
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();

		// Retrieve the file to be signed off.
		$fileId = (int)$request->getUserVar('fileId');

		// Make sure that the file revision is in the grid.
		$submissionFiles =& $this->getGridDataElements($request);
		if (!isset($submissionFiles[$fileId])) fatalError('Invalid file id!');
		assert(isset($submissionFiles[$fileId]['submissionFile']));
		$submissionFile =& $submissionFiles[$fileId]['submissionFile'];
		assert(is_a($submissionFile, 'SubmissionFile'));

		// Retrieve the user.
		$user =& $request->getUser();
		assert(is_a($user, 'User'));

		// Retrieve the user group id.
		$userGroupId = (int)$request->getUserVar('userGroupId');

		// Make sure that the user group id is one of the
		// assigned user groups of the current user in this
		// stage and for this file.
		assert(isset($submissionFiles[$fileId]['signoffs']));
		$fileSignoffs =& $submissionFiles[$fileId]['signoffs'];
		if (
			!isset($fileSignoffs[$user->getId()]) ||
			!isset($fileSignoffs[$user->getId()][$userGroupId])
		) fatalError('Invalid user group id!');

		// Insert or update the sign off corresponding
		// to this file revision.
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->build(
			'SIGNOFF_STAGE_FILE', ASSOC_TYPE_MONOGRAPH, $submissionFile->getSubmissionId(), $user->getId(),
			$userGroupId, $submissionFile->getFileId(), $submissionFile->getRevision()
		);
		$signoff->setDateCompleted(Core::getCurrentDate());
		$signoffDao->updateObject($signoff);

		return DAO::getDataChangedEvent($fileId);
	}
}

?>
