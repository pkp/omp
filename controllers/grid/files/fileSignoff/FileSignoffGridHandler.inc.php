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
import('controllers.grid.files.SignoffStatusFromFileGridColumn');
import('controllers.grid.files.UploaderUserGroupGridColumn');

class FileSignoffGridHandler extends SubmissionFilesGridHandler {
	/** @var integer */
	var $_symbolic;

	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function FileSignoffGridHandler($dataProvider, $stageId, $symbolic, $capabilities) {
		$this->_symbolic = $symbolic;
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
		$currentUser =& $request->getUser();
		$monograph =& $this->getMonograph();

		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */

		// Set up the roles we may include as columns
		$roles = array(
			ROLE_ID_PRESS_MANAGER => 'user.role.manager',
			ROLE_ID_SERIES_EDITOR => 'user.role.seriesEditor',
			ROLE_ID_PRESS_ASSISTANT => 'user.role.pressAssistant'
		);

		// Get all the uploader user group id's
		$uploaderUserGroupIds = array();
		$dataElements =& $this->getGridDataElements($request);
		foreach ($dataElements as $id => $rowElement) {
			$submissionFile =& $rowElement['submissionFile'];
			$uploaderUserGroupIds[] = $submissionFile->getUserGroupId();
		}
		$uploaderUserGroupIds = array_unique($uploaderUserGroupIds);

		$userGroupIds = array();
		foreach ($roles as $roleId => $roleName) {
			$userIds = array();
			$assignments =& $stageAssignmentDao->getBySubmissionAndRoleId($monograph->getId(), $roleId, $this->getStageId());

			// Only include a role column if there is at least one user assigned from that role to this stage.
			if (!$assignments->wasEmpty()) {
				while ($assignment =& $assignments->next()) {
					$userIds[] = $assignment->getUserId();
					$userGroupIds[] = $assignment->getUserGroupId();
					unset($assignment);
				}

				$userIds = array_unique($userIds);
				$flags = array();
				if (in_array($currentUser->getId(), $userIds)) $flags['myUserGroup'] = true;
				$this->addColumn(
					new SignoffStatusFromFileGridColumn(
						'role-' . $roleId,
						$roleName,
						null,
						$this->getSymbolic(),
						$userIds,
						$this->getRequestArgs(),
						$flags
					)
				);
			}
			unset($assignments);
		}

		// Add a column for uploader User Groups not present in the previous groups
		$uploaderUserGroupIds = array_diff($uploaderUserGroupIds, array_unique($userGroupIds));
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

	//
	// Getter/Setters
	//
	/**
	 * Get the signoff's symbolic
	 * @return integer
	 */
	function getSymbolic() {
		return $this->_symbolic;
	}


	//
	// Public Methods
	//
	/**
	 * Sign off the given file revision.
	 * @param $args array
	 * @param $request Request
	 */
	function signOffFile($args, &$request) {
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

		// Insert or update the sign off corresponding
		// to this file revision.
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->build(
			$this->getSymbolic(), ASSOC_TYPE_MONOGRAPH_FILE, $submissionFile->getFileId(), $user->getId()
		);
		$signoff->setDateCompleted(Core::getCurrentDate());
		$signoffDao->updateObject($signoff);

		$this->setupTemplate();
		$user =& $request->getUser();
		NotificationManager::createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.signedFile')));

		return DAO::getDataChangedEvent($fileId);
	}
}

?>
