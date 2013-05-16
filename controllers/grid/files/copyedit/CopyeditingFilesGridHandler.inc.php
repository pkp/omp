<?php

/**
 * @file controllers/grid/files/copyedit/CopyeditingFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingFilesGridHandler
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief Subclass of file editor/auditor grid for copyediting files.
 */

// import grid signoff files grid base classes
import('controllers.grid.files.signoff.SignoffFilesGridHandler');

// Import monograph file class which contains the SUBMISSION_FILE_* constants.
import('lib.pkp.classes.submission.SubmissionFile');

// Import SUBMISSION_EMAIL_* constants.
import('classes.mail.MonographMailTemplate');

class CopyeditingFilesGridHandler extends SignoffFilesGridHandler {
	/**
	 * Constructor
	 */
	function CopyeditingFilesGridHandler() {
		parent::SignoffFilesGridHandler(
			WORKFLOW_STAGE_ID_EDITING,
			SUBMISSION_FILE_COPYEDIT,
			'SIGNOFF_COPYEDITING',
			SUBMISSION_EMAIL_COPYEDIT_NOTIFY_AUTHOR
		);

		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT),
			array(
				'approveCopyedit'
			)
		);
	}

	/**
	 * @see SignoffFilesGridHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		// Approve copyediting file needs monograph access policy.
		$router = $request->getRouter();
		if ($router->getRequestedOp($request) == 'approveCopyedit') {
			import('classes.security.authorization.SubmissionFileAccessPolicy');
			$this->addPolicy(new SubmissionFileAccessPolicy($request, $args, $roleAssignments, SUBMISSION_FILE_ACCESS_MODIFY));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}


	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize($request) {
		parent::initialize($request);

		$this->setTitle('submission.copyediting');
		$this->setInstructions('editor.monograph.editorial.copyeditingDescription');

		// Basic grid configuration
		$this->setId('copyeditingFiles');
	}


	//
	// Public methods
	//
	/**
	 * Approve/disapprove the copyediting file, changing its visibility.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function approveCopyedit($args, $request) {
		$monographFile =& $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		if ($monographFile->getViewable()) {

			// No longer expose the file to be sent to next stage.
			$monographFile->setViewable(false);
		} else {

			// Expose the file.
			$monographFile->setViewable(true);
		}

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$submissionFileDao->updateObject($monographFile);

		return DAO::getDataChangedEvent($monographFile->getId());
	}
}

?>
