<?php

/**
 * @filecontrollers/grid/files/editorReviewFileSelection/ReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileGridHandler
 * @ingroup controllers_grid_file
 *
 * @brief Handle the editor review file selection grid (selects which files to send to review)
 */

import('lib.pkp.classes.controllers.grid.GridHandler');

// import validation classes
import('classes.handler.validation.HandlerValidatorPress');
import('lib.pkp.classes.handler.validation.HandlerValidatorRoles');

class ReviewFilesGridHandler extends GridHandler {
	/** the FileType for this grid */
	var $fileType;

	/** Boolean flag if grid is selectable **/
	var $_isSelectable;

	/** Boolean flag for showing role columns **/
	var $_showRoleColumns;

	/**
	 * Constructor
	 */
	function ReviewFilesGridHandler() {
		parent::GridHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('downloadFile'));
	}

	/**
	 * Set the selectable flag
	 * @param $isSelectable bool
	 */
	function setIsSelectable($isSelectable) {
	 $this->_isSelectable = $isSelectable;
	}

	/**
	 * Get the selectable flag
	 * @return bool
	 */
	function getIsSelectable() {
	 return $this->_isSelectable;
	}

	/**
	 * Set the show role columns flag
	 * @param $showRoleColumns bool
	 */
	function setShowRoleColumns($showRoleColumns) {
	 $this->_showRoleColumns = $showRoleColumns;
	}

	/**
	 * Get the show role columns flag
	 * @return bool
	 */
	function getShowRoleColumns() {
	 return $this->_showRoleColumns;
	}


	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic grid configuration
		$monographId = $request->getUserVar('monographId');
		$this->setId('editorReviewFileSelection');
		$this->setTitle('reviewer.monograph.reviewFiles');

		// Set the Is Selectable boolean flag
		$isSelectable = $request->getUserVar('isSelectable');
		$this->setIsSelectable($isSelectable);

		// Set the show role columns boolean flag
		$showRoleColumns = $request->getUserVar('showRoleColumns');
		$this->setShowRoleColumns($showRoleColumns);

		$reviewType = (int) $request->getUserVar('reviewType');
		$round = (int) $request->getUserVar('round');

		// Grab the files that are currently set for the review
		$reviewAssignmentDAO =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$selectedFiles =& $reviewAssignmentDAO->getReviewFilesByRound($monographId);

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		// Do different initialization if this is a selectable grid or if its a display only version of the grid.
		if ( $isSelectable ) {
			// Load a different grid template
			$this->setTemplate('controllers/grid/files/reviewFiles/grid.tpl');

			// Set the files to all the available files to allow selection.
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFiles =& $monographFileDao->getByMonographId($monographId);
			$this->setData($monographFiles);

			// Set the already selected elements of the grid
			$templateMgr =& TemplateManager::getManager();
			if(!empty($selectedFiles)) $templateMgr->assign('selectedFileIds', array_keys($selectedFiles[$reviewType][$round]));
		} else {
			// set the grid data to be only the files that have already been selected
			$this->setData($selectedFiles[$reviewType][$round]);
		}

		import('controllers.grid.files.reviewFiles.ReviewFilesGridCellProvider');
		$cellProvider =& new ReviewFilesGridCellProvider();
		// Columns
		if ( $this->getIsSelectable() ) {
			$this->addColumn(new GridColumn('select',
				'common.select',
				null,
				'controllers/grid/files/reviewFiles/gridRowSelectInput.tpl',
				$cellProvider)
			);
		}

		$this->addColumn(new GridColumn('name',
			'common.file',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider)
		);

		// either show the role columns or show the file type
		if ( $this->getShowRoleColumns() ) {
			$session =& $request->getSession();
			$actingAsUserGroupId = $session->getSessionVar('actingAsUserGroupId');
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$actingAsUserGroup =& $userGroupDao->getById($actingAsUserGroupId);

			// add a column for the role the user is acting as
			$this->addColumn(
				new GridColumn(
					$actingAsUserGroupId,
					null,
					$actingAsUserGroup->getLocalizedAbbrev(),
					'controllers/grid/common/cell/roleCell.tpl',
					$cellProvider
				)
			);

			// Add another column for the submitter's role
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monograph =& $monographDao->getMonograph($monographId);
			$uploaderUserGroup =& $userGroupDao->getById($monograph->getUserGroupId());
			$this->addColumn(
				new GridColumn(
					$uploaderUserGroup->getId(),
					null,
					$uploaderUserGroup->getLocalizedAbbrev(),
					'controllers/grid/common/cell/roleCell.tpl',
					$cellProvider
				)
			);
		} else {
			$this->addColumn(new GridColumn('type',
				'common.type',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider)
			);
		}
	}

	//
	// Overridden methods from GridHandler
	//

	/**
	 * Validate that the user is the assigned author for the monograph
	 * Raises a fatal error if validation fails.
	 * @param $requiredContexts array
	 * @param $request PKPRequest
	 * @return boolean
	 */
	function validate($requiredContexts, $request) {
		// Retrieve the request context
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$user =& $request->getUser();

		// 1) Ensure we're in a press
		$this->addCheck(new HandlerValidatorPress($this, false, 'No press in context!'));

		// 2) Only Authors may access
		$this->addCheck(new HandlerValidatorRoles($this, false, 'Insufficient privileges!', null, array(ROLE_ID_EDITOR)));

		// Execute standard checks
		if (!parent::validate($requiredContexts, $request)) return false;

		return true;

	}


	//
	// public methods
	//
	/**
	 * Download the monograph file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function downloadFile(&$args, &$request) {
		//FIXME: add validation
		$monographId = $request->getUserVar('monographId');
		$fileId = $request->getUserVar('fileId');
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);
		$monographFileManager->downloadFile($fileId);
	}
}