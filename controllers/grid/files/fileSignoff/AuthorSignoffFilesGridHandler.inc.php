<?php

/**
 * @file controllers/grid/files/fileSignoff/AuthorSignoffFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSignoffFilesGridHandler
 * @ingroup controllers_grid_files_fileSignoff
 *
 * @brief Display the files that the user has been asked to signoff on
 *
 * N.B. This grid does NOT extend the SubmissionFilesGrid, but uses the FileNameGridColumn
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('controllers.grid.files.fileSignoff.AuthorSignoffFilesGridRow');

class AuthorSignoffFilesGridHandler extends GridHandler {
	/* @var int */
	var $_stageId;

	/* @var string */
	var $_symbolic;

	/* @var User */
	var $_user;

	/**
	 * Constructor
	 * @param $symbolic string The signoff symbolic string
	 */
	function AuthorSignoffFilesGridHandler($stageId, $symbolic) {
		parent::GridHandler();
		$this->_stageId = $stageId;
		$this->_symbolic = $symbolic;

		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR),
			array('fetchGrid', 'fetchRow', 'downloadAllFiles', 'signOffFiles')
		);
	}
	//
	// Getters
	//
	/**
	 * Get the Stage Id.
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the Singoff Symbolic.
	 * @return string
	 */
	function getSymbolic() {
		return $this->_symbolic;
	}

	/**
	 * Get the User assosiated with the request
	 */
	function &getUser() {
		return $this->_user;
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $this->getStageId()));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_SUBMISSION));

		$this->_user =& $request->getUser();

		import('controllers.api.signoff.linkAction.AddSignoffFileLinkAction');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$this->addAction(new AddSignoffFileLinkAction(
								$request, $monograph->getId(),
								$this->getStageId(), $this->getSymbolic(), null,
								__('submission.upload.signoff'), __('submission.upload.signoff')
								));

		// The file name column is common to all file grid types.
		import('controllers.grid.files.FileNameGridColumn');
		$this->addColumn(new FileNameGridColumn());

		import('controllers.grid.files.fileSignoff.AuthorSignoffFilesGridCellProvider');
		$cellProvider = new AuthorSignoffFilesGridCellProvider($monograph, $this->getStageId());

		// Add a column to show whether the author uploaded a copyedited version of the file
		$this->addColumn(
			new GridColumn(
				'response',
				'submission.response',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		return array(
			'monographId' => $monograph->getId(),
			'stageId' => $this->getStageId()
		);
	}

	/**
	 * @see GridHandler::getRowInstance()
	 */
	function &getRowInstance() {
		$row = new AuthorSignoffFilesGridRow($this->getStageId());
		return $row;
	}

	/**
	 * @see GridHandler::loadData()
	 * N.B. This method formats data similar to SubmissionFile grids so that it can reuse the FileNameGridColumn
	 */
	function &loadData() {
		$user =& $this->getUser();
		$monographFileSignoffDao =& DAORegistry::getDAO('MonographFileSignoffDAO');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$signoffs =& $monographFileSignoffDao->getAllByMonograph($monograph->getId(), $this->getSymbolic(), $user->getId());

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		while ($signoff =& $signoffs->next()) {
			$monographFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId(), null, $monograph->getId());
			$preparedData[$signoff->getId()]['signoff'] =& $signoff;
			$preparedData[$signoff->getId()]['submissionFile'] =& $monographFile;
			unset($signoff, $monographFile);
		}

		return $preparedData;
	}
}

?>
