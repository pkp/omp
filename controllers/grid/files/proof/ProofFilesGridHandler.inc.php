<?php

/**
 * @file controllers/grid/files/proof/ProofFilesGridHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofFilesGridHandler
 * @ingroup controllers_grid_files_proof
 *
 * @brief Subclass of file editor/auditor grid for proof files.
 */

import('lib.pkp.controllers.grid.files.fileList.FileListGridHandler');

class ProofFilesGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 */
	function ProofFilesGridHandler() {
		import('lib.pkp.controllers.grid.files.proof.ProofFilesGridDataProvider');
		parent::FileListGridHandler(
			new ProofFilesGridDataProvider(),
			WORKFLOW_STAGE_ID_PRODUCTION,
			FILE_GRID_ADD|FILE_GRID_MANAGE|FILE_GRID_DELETE|FILE_GRID_VIEW_NOTES|FILE_GRID_EDIT
		);

		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER, ROLE_ID_ASSISTANT),
			array(
				'fetchGrid', 'fetchRow',
				'addFile', 'selectFiles',
				'downloadFile',
				'deleteFile',
			)
		);
		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER),
			array(
				'setApproval',
			)
		);
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize($request) {
		parent::initialize($request);

		$router = $request->getRouter();

		// Add a "view document library" action
		$this->addAction(
			new LinkAction(
				'viewLibrary',
				new AjaxModal(
					$router->url($request, null, null, 'viewLibrary', null, $this->getRequestArgs()),
					__('grid.action.viewLibrary'),
					'modal_information'
				),
				__('grid.action.viewLibrary'),
				'more_info'
			)
		);

		// Basic grid configuration
		$representation = $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);
		$this->setId('proofFiles-' . $representation->getId());
		$this->setTitle('submission.pageProofs');
		$this->setInstructions('monograph.proofReadingDescription');

		import('controllers.grid.files.proof.ProofFilesGridCellProvider');
		$cellProvider = new ProofFilesGridCellProvider();
		$this->addColumn(new GridColumn(
			'approved',
			'editor.signoff.approved',
			null,
			'controllers/grid/common/cell/statusCell.tpl',
			$cellProvider
		));
	}

	/**
	 * Authorize the request.
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 * @return boolean
	 */
	function authorize($request, $args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
		$this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));

		import('lib.pkp.classes.security.authorization.internal.RepresentationRequiredPolicy');
		$this->addPolicy(new RepresentationRequiredPolicy($request, $args));

		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Show the form to allow the user to select files from previous stages
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function selectFiles($args, $request) {
		$submission = $this->getSubmission();
		$publicationFormat = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);

		import('lib.pkp.controllers.grid.files.proof.form.ManageProofFilesForm');
		$manageProofFilesForm = new ManageProofFilesForm($submission->getId(), $publicationFormat->getId());
		$manageProofFilesForm->initData($args, $request);
		return new JSONMessage(true, $manageProofFilesForm->fetch($request));
	}

	/**
	 * Set the approval status for a file.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function setApproval($args, $request) {
		$submission = $this->getSubmission();
		$publicationFormat = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		import('lib.pkp.classes.submission.SubmissionFile'); // Constants
		$submissionFile = $submissionFileDao->getRevision(
			$request->getUserVar('fileId'),
			$request->getUserVar('revision'),
			SUBMISSION_FILE_PROOF,
			$submission->getId()
		);
		if ($submissionFile && $submissionFile->getAssocType()==ASSOC_TYPE_REPRESENTATION && $submissionFile->getAssocId()==$publicationFormat->getId()) {
			// Update the approval flag
			$submissionFile->setViewable($request->getUserVar('approval')?1:0);
			$submissionFileDao->updateObject($submissionFile);

			// Log the event
			import('lib.pkp.classes.log.SubmissionFileLog');
			import('lib.pkp.classes.log.SubmissionFileEventLogEntry'); // constants
			$user = $request->getUser();
			SubmissionFileLog::logEvent($request, $submissionFile, SUBMISSION_LOG_FILE_SIGNOFF_SIGNOFF, 'submission.event.signoffSignoff', array('file' => $submissionFile->getOriginalFileName(), 'name' => $user->getFullName(), 'username' => $user->getUsername()));

			return DAO::getDataChangedEvent($submissionFile->getFileId());
		}
		return new JSONMessage(false);
	}
}

?>
