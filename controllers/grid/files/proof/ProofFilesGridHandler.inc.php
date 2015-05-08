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

// import grid signoff files grid base classes
import('controllers.grid.files.signoff.SignoffFilesGridHandler');

// Import monograph file class which contains the SUBMISSION_FILE_* constants.
import('classes.monograph.MonographFile');

// Import SUBMISSION_EMAIL_* constants.
import('classes.mail.MonographMailTemplate');

class ProofFilesGridHandler extends SignoffFilesGridHandler {
	/**
	 * Constructor
	 */
	function ProofFilesGridHandler() {
		parent::SignoffFilesGridHandler(
			WORKFLOW_STAGE_ID_PRODUCTION,
			SUBMISSION_FILE_PROOF,
			'SIGNOFF_PROOFING',
			SUBMISSION_EMAIL_PROOFREAD_NOTIFY_AUTHOR,
			ASSOC_TYPE_PUBLICATION_FORMAT
		);
		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER),
			array('selectFiles')
		);

		$this->setEmptyCategoryRowText('grid.noAuditors');
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize($request) {
		$publicationFormat =& $this->getPublicationFormat();
		$this->setAssocId($publicationFormat->getId());

		parent::initialize($request);

		$router = $request->getRouter();

		// Add a "select files" action for editors / subeditors
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);
		if (array_intersect(array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR), $userRoles)) {
			import('lib.pkp.controllers.grid.files.fileList.linkAction.SelectFilesLinkAction');
			$this->addAction(new SelectFilesLinkAction(
				$request,
				$this->getRequestArgs(),
				__('editor.submission.selectFiles')
			));
		}

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
		$this->setId('proofFiles-' . $this->getAssocId());
		$this->setTitle('submission.pageProofs');
		$this->setInstructions('monograph.proofReadingDescription');
	}

	/**
	 * @see SignoffFilesGridHandler::getRowInstance()
	 */
	function getRowInstance() {
		$row = parent::getRowInstance();
		$row->setRequestArgs($this->getRequestArgs());
		return $row;
	}

	/**
	 * @see GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		return array_merge(
			parent::getRequestArgs(),
			array('representationId' => $this->getAssocId())
		);
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
}

?>
