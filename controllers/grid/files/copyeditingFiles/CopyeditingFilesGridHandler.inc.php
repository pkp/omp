<?php

/**
 * @file controllers/grid/files/copyeditingFiles/CopyeditingFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingFilesGridHandler
 * @ingroup controllers_grid_files_CopyeditingFiles
 *
 * @brief Handle the fair copy files grid (displays copyedited files ready to move to proofreading)
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

// import copyediting grid specific classes
import('controllers.grid.files.copyeditingFiles.CopyeditingFilesGridCategoryRow');
import('controllers.grid.files.copyeditingFiles.CopyeditingFilesGridRow');
import('controllers.grid.files.copyeditingFiles.CopyeditingFilesGridCellProvider');

class CopyeditingFilesGridHandler extends CategoryGridHandler {
	/**
	 * Constructor
	 */
	function CopyeditingFilesGridHandler() {
		parent::CategoryGridHandler();

		$this->addRoleAssignment(ROLE_ID_AUTHOR,
			$authorOperations = array('fetchGrid', 'addCopyeditedFile', 'editCopyeditedFile', 'uploadCopyeditedFile', 'returnSignoffRow', 'returnFileRow', 'downloadFile', 'deleteFile'));
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
				array_merge($authorOperations, array('addUser', 'saveAddUser', 'getCopyeditUserAutocomplete', 'deleteUser')));
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

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic grid configuration
		$this->setId('copyeditingFiles');
		$this->setTitle('submission.copyediting');

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Grab the copyediting files to display as categories
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$monographFiles =& $monographFileDao->getByMonographId($monograph->getId(), MONOGRAPH_FILE_COPYEDIT);
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] = $monographFile;
		}
		$this->setData($rowData);

		// Grid actions
		// Action to add a file -- Adds a category row for the file
		$router =& $request->getRouter();
		$this->addAction(
			new LinkAction(
				'uploadFile',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$router->url($request, null, 'grid.files.submissionFiles.CopyeditingSubmissionFilesGridHandler', 'addFile', null, array('monographId' => $monograph->getId(), 'fileStage' => MONOGRAPH_FILE_COPYEDIT)),
				'editor.monograph.fairCopy.addFile',
				null,
				'add'
			)
		);
		// Action to add a user -- Adds the user as a subcategory to the files selected in its modal
		$this->addAction(
			new LinkAction(
				'addUser',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_REPLACE,
				$router->url($request, null, null, 'addUser', null, array('monographId' => $monograph->getId())),
				'editor.monograph.copyediting.addUser',
				null,
				'add'
			)
		);

		// Grid Columns
		$cellProvider =& new CopyeditingFilesGridCellProvider();

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
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoffs =& $signoffDao->getAllBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monograph->getId(), null, WORKFLOW_STAGE_ID_EDITING);
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups = array();
		while($signoff =& $signoffs->next()) {
			$userGroup =& $userGroupDao->getById($signoff->getUserGroupId());
			$userGroups[$userGroup->getId()] = $userGroup->getLocalizedAbbrev();
			unset($signoff, $userGroup);
		}
		foreach($userGroups as $userGroupId => $userGroupAbbrev) {
			$this->addColumn(
				new GridColumn(
					$userGroupId,
					null,
					$userGroupAbbrev,
					'controllers/grid/common/cell/roleCell.tpl',
					$cellProvider
				)
			);
		}
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 * @return CopyeditingFilesGridCategoryRow
	 */
	function &getCategoryRowInstance() {
		$row =& new CopyeditingFilesGridCategoryRow();
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryData()
	 * @param $monographFile MonographFile
	 * @return signoff
	 */
	function getCategoryData(&$monographFile) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoffs =& $signoffDao->getAllBySymbolic('SIGNOFF_COPYEDITING', ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileId());
		return $signoffs;
	}

	/**
	* Get the row handler - override the default row handler
	* @return FairCopyFilesGridRow
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
		import('controllers.grid.files.copyeditingFiles.form.CopyeditingUserForm');
		$copyeditingUserForm = new CopyeditingUserForm($monograph);
		if ($copyeditingUserForm->isLocaleResubmit()) {
			$copyeditingUserForm->readInputData();
		} else {
			$copyeditingUserForm->initData($args, &$request);
		}

		$json = new JSON('true', $copyeditingUserForm->fetch($request));
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
		import('controllers.grid.files.copyeditingFiles.form.CopyeditingUserForm');
		$copyeditingUserForm = new CopyeditingUserForm($monograph);
		$copyeditingUserForm->readInputData();
		if ($copyeditingUserForm->validate()) {
			$copyeditingUserForm->execute();

			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFiles =& $monographFileDao->getByMonographId($monograph->getId(), MONOGRAPH_FILE_COPYEDIT);
			$data = array();
			foreach ($monographFiles as $monographFile) {
				$data[$monographFile->getFileId()] = $monographFile;
			}
			$this->setData($data);
			$this->initialize($request);

			// Pass to modal.js to reload the grid with the new content
			// NB: We must use a custom template to put the categories in since
			//  the category grid handler is designed to replace only one tbody at a time
			$gridBodyParts = $this->_renderCategoriesInternally($request);
			$renderedGridRows = implode($gridBodyParts);

			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('renderedGridRows', $renderedGridRows);
			$templateMgr->assign('grid', $this);
			$columns =& $this->getColumns();
			$templateMgr->assign('numColumns', count($columns));
			$templateMgr->assign('columns', $columns);

			$json = new JSON('true', $templateMgr->fetch('controllers/grid/files/copyeditingFiles/copyeditingGrid.tpl'));
		} else {
			$json = new JSON('false', Locale::translate('editor.monograph.addUserError'));
		}

		return $json->getString();
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
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$stageUsers =& $signoffDao->getAllBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monograph->getId(), null, WORKFLOW_STAGE_ID_EDITING);

		$itemList = array();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$userDao =& DAORegistry::getDAO('UserDAO');
		while($stageUser =& $stageUsers->next()) {
			$userGroup =& $userGroupDao->getById($stageUser->getUserGroupId());
			// Disallow if the user's user group is a reviewer role
			if ($userGroup->getRoleId() != ROLE_ID_REVIEWER) {
				$user =& $userDao->getUser($stageUser->getUserId());
				$itemList[] = array('id' => $user->getId(),
									'name' => $user->getFullName(),
								 	'abbrev' => $userGroup->getLocalizedName(),
									'userGroupId' => $stageUser->getUserGroupId());
			}
		}

		import('lib.pkp.classes.core.JSON');
		$sourceJson = new JSON('true', null, 'false', 'local');
		$sourceContent = array();
		foreach ($itemList as $i => $item) {
			// The autocomplete code requires the JSON data to use 'label' as the array key for labels, and 'value' for the id
			$additionalAttributes = array(
				'label' =>  sprintf('%s (%s)', $item['name'], $item['abbrev']),
				'value' => $item['id'] . "-" . $item['userGroupId']
		 	);
			$itemJson = new JSON('true', '', 'false', null, $additionalAttributes);
			$sourceContent[] = $itemJson->getString();

			unset($itemJson);
		}
		$sourceJson->setContent('[' . implode(',', $sourceContent) . ']');

		echo $sourceJson->getString();
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

		import('controllers.grid.files.copyeditingFiles.form.CopyeditingFileForm');
		$copyeditingFileForm = new CopyeditingFileForm($monograph, $signoffId);

		if ($copyeditingFileForm->isLocaleResubmit()) {
			$copyeditingFileForm->readInputData();
		} else {
			$copyeditingFileForm->initData($args, $request);
		}

		$json = new JSON('true', $copyeditingFileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function uploadCopyeditedFile($args, &$request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$signoffId = (int) $request->getUserVar('signoffId');
		assert(!empty($signoffId));

		import('controllers.grid.files.copyeditingFiles.form.CopyeditingFileForm');
		$copyeditingFileForm = new CopyeditingFileForm($monograph, $signoffId);
		$copyeditingFileForm->readInputData();

		if ($copyeditingFileForm->validate()) {
			$copyeditedFileId = $copyeditingFileForm->uploadFile($args, $request);;

			$router =& $request->getRouter();
			$additionalAttributes = array(
				'deleteUrl' => $router->url($request, null, null, 'deleteFile', null, array('fileId' => $copyeditedFileId))
			);
			$json = new JSON('true', Locale::translate('submission.uploadSuccessful'), 'false', $copyeditedFileId, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('common.uploadFailed'));
		}

		echo $json->getString();
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
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($signoffId);
			$row->setData($signoff);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false', Locale::translate('common.uploadFailed'));
		}

		return $json->getString();
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

		$sessionManager =& SessionManager::getManager();
		$session =& $sessionManager->getUserSession();
		$user =& $session->getUser();
		$viewsDao =& DAORegistry::getDAO('ViewsDAO');
		$viewsDao->recordView(ASSOC_TYPE_MONOGRAPH_FILE, $fileId, $user->getId());

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monograph->getId());
		$monographFileManager->downloadFile($fileId);
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
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
			$monographFileDao->deleteMonographFileById($fileId);

			// Fetch the updated row
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($signoffId);
			$row->setData($signoff);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false');
		}
		return $json->getString();
	}

	/**
	 * Delete a user's signoff
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteUser($args, &$request) {
		$signoffId = (int) $request->getUserVar('signoffId');

		if($signoffId) {
			// Remove the signoff
			$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
			$signoffDao->deleteObjectById($signoffId);

			$json = new JSON('true');
		} else {
			$json = new JSON('false', 'manager.setup.errorDeletingItem');
		}
		return $json->getString();
	}
}