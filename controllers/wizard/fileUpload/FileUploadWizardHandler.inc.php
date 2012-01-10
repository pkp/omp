<?php
/**
 * @defgroup controllers_wizard_fileUpload
 */

/**
 * @file controllers/wizard/fileUpload/FileUploadWizardHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileUploadWizardHandler
 * @ingroup controllers_wizard_fileUpload
 *
 * @brief A controller that handles basic server-side
 *  operations of the file upload wizard.
 */

// Import the base handler.
import('classes.file.FileManagementHandler');

// Import JSON class for use with all AJAX requests.
import('lib.pkp.classes.core.JSONMessage');

// The percentage of characters that the name of a file
// has to share with an existing file for it to be
// considered as a revision of that file.
define('SUBMISSION_MIN_SIMILARITY_OF_REVISION', 70);

class FileUploadWizardHandler extends FileManagementHandler {
	/** @var integer */
	var $_fileStage;

	/** @var array */
	var $_uploaderRoles;

	/** @var boolean */
	var $_revisionOnly;

	/** @var int */
	var $_reviewRound;

	/** @var integer */
	var $_revisedFileId;

	/** @var integer */
	var $_assocType;

	/** @var integer */
	var $_assocId;


	/**
	 * Constructor
	 */
	function FileUploadWizardHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_REVIEWER, ROLE_ID_PRESS_ASSISTANT),
			array(
				'startWizard', 'displayFileUploadForm',
				'uploadFile', 'confirmRevision',
				'editMetadata', 'saveMetadata',
				'finishFileSubmission'
			)
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		parent::initialize($request, $args);
		// Configure the wizard with the authorized monograph and file stage.
		// Validated in authorize.
		$this->_fileStage = (int)$request->getUserVar('fileStage');

		// Set the uploader roles (if given).
		$uploaderRoles = $request->getUserVar('uploaderRoles');
		if (!is_null($uploaderRoles)) {
			$this->_uploaderRoles = array();
			$uploaderRoles = explode('-', $uploaderRoles);
			foreach($uploaderRoles as $uploaderRole) {
				if (!is_numeric($uploaderRole)) fatalError('Invalid uploader role!');
				$this->_uploaderRoles[] = (int)$uploaderRole;
			}
		}

		// Do we allow revisions only?
		$this->_revisionOnly = (boolean)$request->getUserVar('revisionOnly');
		$reviewRound =& $this->getReviewRound();
		$this->_assocType = $request->getUserVar('assocType') ? (int)$request->getUserVar('assocType') : null;
		$this->_assocId = $request->getUserVar('assocId') ? (int)$request->getUserVar('assocId') : null;

		// The revised file will be non-null if we revise a single existing file.
		if ($this->getRevisionOnly() && $request->getUserVar('revisedFileId')) {
			// Validated in authorize.
			$this->_revisedFileId = (int)$request->getUserVar('revisedFileId');
		}

		// Load translations.
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_COMMON,
			LOCALE_COMPONENT_APPLICATION_COMMON
		);
	}

	function authorize($request, $args, $roleAssignments) {
		// This is validated in parent's authorization policy.
		$stageId = (int)$request->getUserVar('stageId');

		// Authorize review round id when this handler is used in review stages.
		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			import('classes.security.authorization.internal.ReviewRoundRequiredPolicy');
			$this->addPolicy(new ReviewRoundRequiredPolicy($request, $args));
		}

		// We validate file stage outside a policy because
		// we don't need to validate in another places.
		$fileStage = $request->getUserVar('fileStage');
		if ($fileStage) {
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
			$fileStages = $submissionFileDao->getAllFileStages();
			if (!in_array($fileStage, $fileStages)) {
				return false;
			}
		}

		$revisedFileId = $request->getUserVar('revisedFileId');
		if ($revisedFileId) {
			import('classes.security.authorization.OmpMonographFileAccessPolicy');
			$this->addPolicy(new OmpMonographFileAccessPolicy($request, $args, $roleAssignments, MONOGRAPH_FILE_ACCESS_READ, $revisedFileId));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the workflow stage file storage that
	 * we upload files to. One of the MONOGRAPH_FILE_*
	 * constants.
	 * @return integer
	 */
	function getFileStage() {
		return $this->_fileStage;
	}

	/**
	 * Get the uploader roles.
	 * @return array
	 */
	function getUploaderRoles() {
		return $this->_uploaderRoles;
	}

	/**
	 * Does this uploader only allow revisions and no new files?
	 * @return boolean
	 */
	function getRevisionOnly() {
		return $this->_revisionOnly;
	}

	/**
	 * Get review round object.
	 * @return ReviewRound
	 */
	function &getReviewRound() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);
	}

	/**
	 * Get the id of the file to be revised (if any).
	 * @return integer
	 */
	function getRevisedFileId() {
		return $this->_revisedFileId;
	}

	/**
	 * Get the assoc type (if any)
	 * @return integer
	 */
	function getAssocType() {
		return $this->_assocType;
	}

	/**
	 * Get the assoc id (if any)
	 * @return integer
	 */
	function getAssocId() {
		return $this->_assocId;
	}

	//
	// Public handler methods
	//
	/**
	 * Displays the file upload wizard.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function startWizard($args, &$request) {
		$templateMgr =& TemplateManager::getManager();

		// Assign the monograph.
		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());

		// Assign the workflow stage.
		$templateMgr->assign('stageId', $this->getStageId());

		// Assign the roles allowed to upload in the given context.
		$templateMgr->assign('uploaderRoles', implode('-', $this->getUploaderRoles()));

		// Assign the file stage.
		$templateMgr->assign('fileStage', $this->getFileStage());

		// Preserve the isReviewer flag
		$templateMgr->assign('isReviewer', $request->getUserVar('isReviewer'));

		// Configure the "revision only" feature.
		$templateMgr->assign('revisionOnly', $this->getRevisionOnly());
		$reviewRound =& $this->getReviewRound();
		if (is_a($reviewRound, 'ReviewRound')) {
			$templateMgr->assign('reviewRoundId', $reviewRound->getId());
		}
		$templateMgr->assign('revisedFileId', $this->getRevisedFileId());
		$templateMgr->assign('assocType', $this->getAssocType());
		$templateMgr->assign('assocId', $this->getAssocId());

		// Render the file upload wizard.
		return $templateMgr->fetchJson('controllers/wizard/fileUpload/fileUploadWizard.tpl');
	}

	/**
	 * Render the file upload form in its initial state.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function displayFileUploadForm($args, &$request) {
		// Instantiate, configure and initialize the form.
		import('controllers.wizard.fileUpload.form.SubmissionFilesUploadForm');
		$monograph =& $this->getMonograph();
		$fileForm = new SubmissionFilesUploadForm(
			$request, $monograph->getId(), $this->getStageId(), $this->getUploaderRoles(), $this->getFileStage(),
			$this->getRevisionOnly(), $this->getReviewRound(), $this->getRevisedFileId(),
			$this->getAssocType(), $this->getAssocId()
		);
		$fileForm->initData($args, $request);

		// Render the form.
		$json = new JSONMessage(true, $fileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a file and render the modified upload wizard.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function uploadFile($args, &$request, $fileModifyCallback = null) {
		// Instantiate the file upload form.
		$monograph =& $this->getMonograph();
		import('controllers.wizard.fileUpload.form.SubmissionFilesUploadForm');
		$uploadForm = new SubmissionFilesUploadForm(
			$request, $monograph->getId(), $this->getStageId(), null, $this->getFileStage(),
			$this->getRevisionOnly(), $this->getReviewRound(), null, $this->getAssocType(), $this->getAssocId()
		);
		$uploadForm->readInputData();

		// Validate the form and upload the file.
		if ($uploadForm->validate($request)) {
			if (is_a($uploadedFile =& $uploadForm->execute($request), 'MonographFile')) {
				// Retrieve file info to be used in a JSON response.
				$uploadedFileInfo = $this->_getUploadedFileInfo($uploadedFile);

				// If no revised file id was given then try out whether
				// the user maybe accidentally didn't identify this file as a revision.
				if (!$uploadForm->getRevisedFileId()) {
					$revisedFileId = $this->_checkForRevision($uploadedFile, $uploadForm->getMonographFiles());
					if ($revisedFileId) {
						// Instantiate the revision confirmation form.
						$reviewRound =& $this->getReviewRound();
						import('controllers.wizard.fileUpload.form.SubmissionFilesUploadConfirmationForm');
						$confirmationForm = new SubmissionFilesUploadConfirmationForm($request, $monograph->getId(), $this->getStageId(), $this->getFileStage(), $reviewRound, $revisedFileId, $this->getAssocType(), $this->getAssocId(), $uploadedFile);
						$confirmationForm->initData($args, $request);

						// Render the revision confirmation form.
						$json = new JSONMessage(true, $confirmationForm->fetch($request), '0', $uploadedFileInfo);
						return $json->getString();
					}
				}

				// Advance to the next step (i.e. meta-data editing).
				$json = new JSONMessage(true, '', '0', $uploadedFileInfo);
			} else {
				$json = new JSONMessage(false, __('common.uploadFailed'));
			}
		} else {
			$json = new JSONMessage(false, array_pop($uploadForm->getErrorsArray()));
		}
		return $json->getString();
	}

	/**
	 * Confirm that the uploaded file is a revision of an
	 * earlier uploaded file.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function confirmRevision($args, &$request) {
		// Instantiate the revision confirmation form.
		$monograph =& $this->getMonograph();
		import('controllers.wizard.fileUpload.form.SubmissionFilesUploadConfirmationForm');
		// FIXME?: need assocType and assocId? Not sure if they would be used, so not adding now.
		$reviewRound =& $this->getReviewRound();
		$confirmationForm = new SubmissionFilesUploadConfirmationForm(
			$request, $monograph->getId(), $this->getStageId(), $this->getFileStage(), $reviewRound
		);
		$confirmationForm->readInputData();

		// Validate the form and revise the file.
		if ($confirmationForm->validate($request)) {
			if (is_a($uploadedFile =& $confirmationForm->execute(), 'MonographFile')) {
				// Go to the meta-data editing step.
				$json = new JSONMessage(true, '', '0', $this->_getUploadedFileInfo($uploadedFile));
			} else {
				$json = new JSONMessage(false, __('common.uploadFailed'));
			}
		} else {
			$json = new JSONMessage(false, array_pop($confirmationForm->getErrorsArray()));
		}
		return $json->getString();
	}

	/**
	 * Edit the metadata of the latest revision of
	 * the requested submission file.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function editMetadata($args, &$request) {
		$metadataForm =& $this->_getMetadataForm($request);
		if ($metadataForm->isLocaleResubmit()) {
			$metadataForm->readInputData();
		} else {
			$metadataForm->initData($args, $request);
		}
		$json = new JSONMessage(true, $metadataForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the metadata of the latest revision of
	 * the requested submission file
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function saveMetadata($args, &$request) {
		$metadataForm =& $this->_getMetadataForm($request);
		$metadataForm->readInputData();
		if ($metadataForm->validate()) {
			$metadataForm->execute($args, $request);
			$submissionFile = $metadataForm->getSubmissionFile();
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSONMessage(false, $metadataForm->fetch($request));
		}
		return $json->getString();
	}

	/**
	 * Display the final tab of the modal
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function finishFileSubmission($args, &$request) {
		$monograph =& $this->getMonograph();

		// Validation not req'd -- just generating a JSON update message.
		$fileId = (int)$request->getUserVar('fileId');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monograph->getId());
		$templateMgr->assign('fileId', $fileId);

		return $templateMgr->fetchJson('controllers/wizard/fileUpload/form/fileSubmissionComplete.tpl');
	}


	//
	// Private helper methods
	//
	/**
	 * Retrieve the requested meta-data form.
	 * @param $request Request
	 * @return SubmissionFilesMetadataForm
	 */
	function &_getMetadataForm(&$request) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getMonograph();

		// Retrieve the latest revision of the requested monograph file.
		// FIXME Bug #6976: Validate file ID
		$fileId = (int)$request->getUserVar('fileId');
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFile =& $submissionFileDao->getLatestRevision($fileId, $this->getFileStage(), $monograph->getId());
		if (!is_a($submissionFile, 'MonographFile')) fatalError('Invalid file id!');

		// Import the meta-data form based on the file implementation.
		if (is_a($submissionFile, 'ArtworkFile')) {
			import('controllers.wizard.fileUpload.form.SubmissionFilesArtworkMetadataForm');
			$metadataForm = new SubmissionFilesArtworkMetadataForm($submissionFile, $this->getStageId(), $this->getReviewRound());
		} else {
			import('controllers.wizard.fileUpload.form.SubmissionFilesMetadataForm');
			$metadataForm = new SubmissionFilesMetadataForm($submissionFile, $this->getStageId(), $this->getReviewRound());
		}

		return $metadataForm;
	}

	/**
	 * Check if the uploaded file has a similar name to an existing
	 * file which would then be a candidate for a revised file.
	 * @param $uploadedFile MonographFile
	 * @param $monographFiles array a list of monograph files to
	 *  check the uploaded file against.
	 * @return integer the if of the possibly revised file or null
	 *  if no matches were found.
	 */
	function &_checkForRevision(&$uploadedFile, &$monographFiles) {
		// Get the file name.
		$uploadedFileName = $uploadedFile->getOriginalFileName();

		// Start with the minimal required similarity.
		$minPercentage = SUBMISSION_MIN_SIMILARITY_OF_REVISION;

		// Find out whether one of the files belonging to the current
		// file stage matches the given file name.
		$possibleRevisedFileId = null;
		$matchedPercentage = 0;
		foreach ($monographFiles as $monographFile) { /* @var $monographFile MonographFile */
			// Do not consider the uploaded file itself.
			if ($uploadedFile->getFileId() == $monographFile->getFileId()) continue;

			// Test whether the current monograph file is similar
			// to the uploaded file. (Transliterate to ASCII -- the
			// similar_text function can't handle UTF-8.)

			import('lib.pkp.classes.core.Transcoder');
			$transcoder = new Transcoder('UTF-8', 'ASCII', true);

			similar_text(
				$transcoder->trans($uploadedFileName),
				$transcoder->trans($monographFile->getOriginalFileName()),
				$matchedPercentage
			);
			if($matchedPercentage > $minPercentage) {
				// We found a file that might be a possible revision.
				$possibleRevisedFileId = $monographFile->getFileId();

				// Reset the min percentage to this comparison's precentage
				// so that only better matches will be considered from now on.
				$minPercentage = $matchedPercentage;
			}
		}

		// Return the id of the file that we found similar.
		return $possibleRevisedFileId;
	}

	/**
	 * Create an array that describes an uploaded file which can
	 * be used in a JSON response.
	 * @param MonographFile $uploadedFile
	 * @return array
	 */
	function &_getUploadedFileInfo(&$uploadedFile) {
		$uploadedFileInfo = array(
			'uploadedFile' => array(
				'fileId' => $uploadedFile->getFileId(),
				'revision' => $uploadedFile->getRevision()
			)
		);
		return $uploadedFileInfo;
	}
}

?>
