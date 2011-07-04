<?php

/**
 * @file controllers/grid/files/copyedit/CopyeditingFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingFilesGridHandler
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief Handle the fair copy files grid (displays copyedited files ready to move to proofreading)
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');

// import copyediting grid specific classes
import('controllers.grid.files.copyedit.CopyeditingFilesGridCategoryRow');
import('controllers.grid.files.copyedit.CopyeditingFilesGridRow');
import('controllers.grid.files.copyedit.CopyeditingFilesGridCellProvider');

// Link actions
import('lib.pkp.classes.linkAction.request.AjaxModal');

class CopyeditingFilesGridHandler extends CategoryGridHandler {
	/**
	 * Constructor
	 */
	function CopyeditingFilesGridHandler() {
		parent::CategoryGridHandler();

		$this->addRoleAssignment(
			ROLE_ID_AUTHOR,
			$authorOperations = array(
				'fetchGrid', 'fetchRow', 'addCopyeditedFile',
				'editCopyeditedFile', 'uploadCopyeditedFile',
				'returnSignoffRow', 'returnFileRow',
				'downloadFile', 'deleteFile',
			)
		);
		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			array_merge(
				$authorOperations,
				array(
					'addUser', 'saveAddUser', 'getCopyeditUserAutocomplete', 'deleteSignoff'
				)
			)
		);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the monograph associated with this chapter grid.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_EDITING));
		return parent::authorize($request, $args, $roleAssignments);
	}

	//
	// Implement template methods from PKPHandler
	//

	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic grid configuration
		$this->setId('copyeditingFiles');
		$this->setTitle('submission.copyediting');

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		$monograph =& $this->getMonograph();

		// Bring in file constants
		import('classes.monograph.MonographFile');

		// Grid actions
		// Action to add a file -- Adds a category row for the file
		import('controllers.api.file.linkAction.AddFileLinkAction');
		$this->addAction(new AddFileLinkAction(
			$request, $monograph->getId(),
			WORKFLOW_STAGE_ID_EDITING,
			array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			MONOGRAPH_FILE_COPYEDIT
		));

		$router =& $request->getRouter();

		// Action to add a user -- Adds the user as a subcategory to the files selected in its modal
		// FIXME: Not all roles should see this action. Bug #5975.
		$this->addAction(new LinkAction(
			'addUser',
			new AjaxModal(
				$router->url($request, null, null, 'addUser', null, array('monographId' => $monograph->getId())),
				__('editor.monograph.copyediting.addUser'),
				'add_item'
			),
			__('editor.monograph.copyediting.addUser'),
			'add_item'
		));

		// Grid Columns
		$cellProvider = new CopyeditingFilesGridCellProvider();

		// Add a column for the file's label
		$this->addColumn(
			new GridColumn(
				'name',
				'common.file',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);

		// Add role columns -- One of each user group currently assigned to the stage:
		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$stageAssignments = $stageAssignmentDao->getBySubmissionAndStageId($monograph->getId(), WORKFLOW_STAGE_ID_EDITING);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups = array();
		while($stageAssignment =& $stageAssignments->next()) {
			$userGroup =& $userGroupDao->getById($stageAssignment->getUserGroupId());
			$userGroups[$userGroup->getId()] = $userGroup->getLocalizedAbbrev();
			unset($stageAssignment, $userGroup);
		}
		foreach($userGroups as $userGroupId => $userGroupAbbrev) {
			$this->addColumn(
				new GridColumn(
					$userGroupId,
					null,
					$userGroupAbbrev,
					'controllers/grid/common/cell/statusCell.tpl',
					$cellProvider
				)
			);
		}
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData(&$request, $filter) {
		// Grab the copyediting files to display as categories
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), MONOGRAPH_FILE_COPYEDIT);

		// $monographFiles is keyed on file and revision, for the grid we need to key on file only
		// since the grid shows only the most recent revision.
		$data = array();
		foreach ($monographFiles as $monographFile) {
			$data[$monographFile->getFileId()] = $monographFile;
		}
		return $data;
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 * @return CopyeditingFilesGridCategoryRow
	 */
	function &getCategoryRowInstance() {
		$row = new CopyeditingFilesGridCategoryRow();
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryData()
	 * @param $monographFile MonographFile
	 * @return array Signoffs
	 */
	function getCategoryData(&$monographFile) {
		$monographFileSignoffDao =& DAORegistry::getDAO('MonographFileSignoffDAO');
		$signoffFactory =& $monographFileSignoffDao->getAllBySymbolic('SIGNOFF_COPYEDITING', $monographFile->getFileId()); /* @var $signoffs DAOResultFactory */
		$signoffs = $signoffFactory->toAssociativeArray();
		return $signoffs;
	}

	/**
	* Get the row handler - override the default row handler
	* @return CopyeditingFilesGridRow
	*/
	function &getRowInstance() {
		$row = new CopyeditingFilesGridRow();
		return $row;
	}

	//
	// Public methods
	//

	/**
	 * Adds a user to a copyediting file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addUser($args, &$request) {
		// Identify the monograph being worked on
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Form handling
		import('controllers.grid.files.copyedit.form.CopyeditingUserForm');
		$copyeditingUserForm = new CopyeditingUserForm($monograph);
		if ($copyeditingUserForm->isLocaleResubmit()) {
			$copyeditingUserForm->readInputData();
		} else {
			$copyeditingUserForm->initData($args, &$request);
		}

		$json = new JSONMessage(true, $copyeditingUserForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the form for adding a user to a copyediting file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveAddUser($args, &$request) {
		// Identify the monograph being worked on
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Form handling
		import('controllers.grid.files.copyedit.form.CopyeditingUserForm');
		$copyeditingUserForm = new CopyeditingUserForm($monograph);
		$copyeditingUserForm->readInputData();
		if ($copyeditingUserForm->validate()) {
			$copyeditingUserForm->execute($request);

			return DAO::getDataChangedEvent();
		}

		$m = new JSONMessage(false, __('editor.monograph.addUserError'));
		return $m->getString();
	}

	/**
	 * Get users for copyediting autocomplete.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function getCopyeditUserAutocomplete($args, &$request) {
		// Identify the Monograph we are working with
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Retrieve the users for the autocomplete control: Any author or press assistant user assigned to this stage
		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$stageUsers = $stageAssignmentDao->getBySubmissionAndStageId($monograph->getId(), WORKFLOW_STAGE_ID_EDITING);

		$itemList = array();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$userDao =& DAORegistry::getDAO('UserDAO');
		while($stageUser =& $stageUsers->next()) {
			$userGroup =& $userGroupDao->getById($stageUser->getUserGroupId());
			// Disallow if the user's user group is a reviewer role
			if ($userGroup->getRoleId() != ROLE_ID_REVIEWER) {
				$user =& $userDao->getUser($stageUser->getUserId());
				$itemList[] = array(
					'label' =>  sprintf('%s (%s)', $user->getFullName(), $userGroup->getLocalizedName()),
					'value' => $user->getId() . '-' . $stageUser->getUserGroupId()
				);
			}
			unset($stageUser, $userGroup);
		}

		import('lib.pkp.classes.core.JSONMessage');
		$json = new JSONMessage(true, $itemList);
		echo $json->getString();
	}

	/**
	 * Add a file to a copyediting assignment
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addCopyeditedFile($args, &$request) {
		// Calling editCopyeditedFile with an empty row id will add a new file
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('newFile', 'true');
		return $this->editCopyeditedFile($args, $request);
	}

	/**
	 * Show the copyedited file upload form (to add a new or edit an existing copyedited file)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editCopyeditedFile($args, &$request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$signoffId = (int) $request->getUserVar('signoffId');
		assert(!empty($signoffId));

		import('controllers.grid.files.copyedit.form.CopyeditingFileForm');
		$copyeditingFileForm = new CopyeditingFileForm($monograph, $signoffId);

		if ($copyeditingFileForm->isLocaleResubmit()) {
			$copyeditingFileForm->readInputData();
		} else {
			$copyeditingFileForm->initData($args, $request);
		}

		$json = new JSONMessage(true, $copyeditingFileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Return a grid row with for the copyediting grid
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function returnSignoffRow($args, &$request) {
		$signoffId = (int) $request->getUserVar('signoffId');
		assert(!empty($signoffId));

		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($signoffId);

		if($signoff) {
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSONMessage(false, Locale::translate('common.uploadFailed'));
			return $json->getString();
		}


	}

	/**
	 * Download the monograph file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function downloadFile($args, &$request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$fileId = (int) $request->getUserVar('fileId');
		assert(!empty($fileId));

		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFile($monograph->getId(), $fileId);
	}

	/**
	 * Delete a file if it has been uploaded to the signoff
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteFile($args, &$request) {
		$fileId = (int) $request->getUserVar('fileId');
		$signoffId = (int) $request->getUserVar('signoffId');

		if($fileId && $signoffId) {
			// Remove the file id from the signoff
			$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
			$signoff =& $signoffDao->getById($signoffId);
			assert($signoff->getFileId() == $fileId);
			$signoff->setFileId(null);
			$signoff->setDateCompleted(null);
			$signoffDao->updateObject($signoff);

			// Delete the file
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$submissionFileDao->deleteAllRevisionsById($fileId);

			// Fetch the updated row
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($signoffId);
			$row->setData($signoff);
			$row->initialize($request);

			$json = new JSONMessage(true, $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSONMessage(false);
		}
		return $json->getString();
	}

	/**
	 * Delete a user's signoff
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteSignoff($args, &$request) {
		$signoffId = (int) $request->getUserVar('signoffId');
		$fileId = (int) $request->getUserVar('fileId');

		if($signoffId) {
			// Remove the signoff
			$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
			$signoffDao->deleteObjectById($signoffId);

			return DAO::getDataChangedEvent($fileId);
		} else {
			$json = new JSONMessage(false, 'manager.setup.errorDeletingItem');
			return $json->getString();
		}

	}
}

?>
