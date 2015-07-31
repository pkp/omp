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
