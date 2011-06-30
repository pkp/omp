<?php

/**
 * @file controllers/grid/files/copyedit/AuthorCopyeditingFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorCopyeditingFilesGridHandler
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief Handle the grid for the files the author needs to copyedit
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

// import copyediting grid specific classes
import('controllers.grid.files.copyedit.AuthorCopyeditingFilesGridCellProvider');

class AuthorCopyeditingFilesGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function AuthorCopyeditingFilesGridHandler() {
		parent::GridHandler();

		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER), array(
			'fetchGrid'
		));
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
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

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

		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Grid Columns
		$cellProvider = new AuthorCopyeditingFilesGridCellProvider($monograph);

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

		// Add a column to show whether the author uploaded a copyedited version of the file
		$this->addColumn(
			new GridColumn(
				'responded',
				'submission.response',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function loadData(&$request, $filter) {
		// Grab the copyediting files to display as categories
		$user =& $request->getUser();
		$monographFileSignoffDao =& DAORegistry::getDAO('MonographFileSignoffDAO');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$signoffs =& $monographFileSignoffDao->getAllByMonograph('SIGNOFF_COPYEDITING', $monograph->getId(), $user->getId());
		return $signoffs;
	}
}

?>
