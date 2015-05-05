<?php

/**
 * @file controllers/grid/files/proof/ManageProofFilesGridHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageProofFilesGridHandler
 * @ingroup controllers_grid_files_proof
 *
 * @brief Handle the editor's proof files selection grid (selects which files to include)
 */

import('lib.pkp.controllers.grid.files.SelectableSubmissionFileListCategoryGridHandler');

class ManageProofFilesGridHandler extends SelectableSubmissionFileListCategoryGridHandler {
	/**
	 * Constructor
	 */
	function ManageProofFilesGridHandler() {
		import('controllers.grid.files.proof.ProofFilesCategoryGridDataProvider');
		parent::SelectableSubmissionFileListCategoryGridHandler(
			new ProofFilesCategoryGridDataProvider(),
			WORKFLOW_STAGE_ID_PRODUCTION,
			0
		);

		$this->addRoleAssignment(
			array(
				ROLE_ID_SUB_EDITOR,
				ROLE_ID_MANAGER,
				ROLE_ID_ASSISTANT
			),
			array(
				'fetchGrid', 'fetchCategory', 'fetchRow',
				'addFile',
				'downloadFile',
				'deleteFile',
				'updateProofFiles'
			)
		);

		// Set the grid title.
		$this->setTitle('submission.pageProofs');
	}

	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.internal.SubmissionRequiredPolicy');
		$this->addPolicy(new SubmissionRequiredPolicy($request, $args, 'submissionId'));

		import('classes.security.authorization.internal.PublicationFormatRequiredPolicy');
		$this->addPolicy(new PublicationFormatRequiredPolicy($request, $args));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Save 'manage proof files' form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateProofFiles($args, $request) {
		$submission = $this->getSubmission();
		$publicationFormat = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);

		import('controllers.grid.files.proof.form.ManageProofFilesForm');
		$manageProofFilesForm = new ManageProofFilesForm($submission->getId(), $publicationFormat->getId());
		$manageProofFilesForm->readInputData();

		if ($manageProofFilesForm->validate()) {
			$dataProvider = $this->getDataProvider();
			$manageProofFilesForm->execute($args, $request, $dataProvider->loadCategoryData($request, $this->getStageId()));

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent();
		} else {
			return new JSONMessage(false);
		}
	}
}

?>
