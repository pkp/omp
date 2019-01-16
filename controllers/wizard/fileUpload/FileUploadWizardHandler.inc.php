<?php

/**
 * @defgroup controllers_wizard_fileUpload File upload wizard
 */

/**
 * @file controllers/wizard/fileUpload/FileUploadWizardHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileUploadWizardHandler
 * @ingroup controllers_wizard_fileUpload
 *
 * @brief A controller that handles basic server-side
 *  operations of the file upload wizard.
 */

// Import the base handler.
import('lib.pkp.controllers.wizard.fileUpload.PKPFileUploadWizardHandler');

class FileUploadWizardHandler extends PKPFileUploadWizardHandler {
	//
	// Implement template methods from PKPHandler
	//
	function authorize($request, &$args, $roleAssignments) {
		// We validate file stage outside a policy because
		// we don't need to validate in another places.
		$fileStage = $request->getUserVar('fileStage');
		if ($fileStage) {
			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
			$fileStages = $submissionFileDao->getAllFileStages();
			if (!in_array($fileStage, $fileStages)) {
				return false;
			}
		}

		// Validate file ids. We have two cases where we might have a file id.
		// CASE 1: user is uploading a revision to a file, the revised file id
		// will need validation.
		$revisedFileId = (int)$request->getUserVar('revisedFileId');
		// CASE 2: user already have uploaded a file (and it's editing the metadata),
		// we will need to validate the uploaded file id.
		$fileId = (int)$request->getUserVar('fileId');
		// Get the right one to validate.
		$fileIdToValidate = null;
		if ($revisedFileId && !$fileId) {
			$fileIdToValidate = $revisedFileId;
		} else if ($fileId && !$revisedFileId) {
			$fileIdToValidate = $fileId;
		} else if ($revisedFileId && $fileId) {
			// Those two cases will not happen at the same time.
			return false;
		}
		if ($fileIdToValidate) {
			import('lib.pkp.classes.security.authorization.SubmissionFileAccessPolicy');
			$this->addPolicy(new SubmissionFileAccessPolicy($request, $args, $roleAssignments, SUBMISSION_FILE_ACCESS_READ, $fileIdToValidate));
		}

		// Allow both reviewers (if in review) and context roles.
		$stageId = (int)$request->getUserVar('stageId');
		import('lib.pkp.classes.security.authorization.ReviewStageAccessPolicy');
		$this->addPolicy(new ReviewStageAccessPolicy($request, $args, $roleAssignments, 'submissionId', $stageId));

		// Authorize review round id when this handler is used in review stages -- except
		// for query files, which belong to the stage rather than the review round.
		import('lib.pkp.classes.submission.SubmissionFile');
		if (($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) && !in_array($request->getUserVar('fileStage'), array(SUBMISSION_FILE_QUERY, SUBMISSION_FILE_DEPENDENT))) {
			import('lib.pkp.classes.security.authorization.internal.ReviewRoundRequiredPolicy');
			$this->addPolicy(new ReviewRoundRequiredPolicy($request, $args));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}
}


