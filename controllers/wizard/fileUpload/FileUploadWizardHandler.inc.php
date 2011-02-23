<?php
/**
 * @defgroup controllers_wizard_fileUpload
 */

/**
 * @file controllers/wizard/fileUpload/FileUploadWizardHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
import('lib.pkp.classes.core.JSON');

// The percentage of characters that the name of a file
// has to share with an existing file for it to be
// considered as a revision of that file.
define('SUBMISSION_MIN_SIMILARITY_OF_REVISION', 70);

class FileUploadWizardHandler extends FileManagementHandler {

	/** @var boolean */
	var $_revisionOnly;

	/** @var integer */
	var $_revisedFileId;


	/**
	 * Constructor
	 */
	function FileUploadWizardHandler() {
		parent::Handler();
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR),
				array('startWizard', 'displayFileUploadForm', 'uploadFile', 'confirmRevision',
						'editMetadata', 'saveMetadata', 'finishFileSubmission'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		parent::initialize($request, $args);

		// FIXME: How do we identify (and authorize) the "revision only" policy?
		$this->_revisionOnly = false;

		// The revised file will be non-null if we revise a single existing file.
		if ($this->getRevisionOnly() && $request->getUserVar('revisedFileId')) {
			$this->_revisedFileId = (int)$request->getUserVar('revisedFileId');
		}

		// Load translations.
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON));
	}


	//
	// Getters and Setters
	//
	/**
	 * Does this uploader only allow revisions and no new files?
	 * @return boolean
	 */
	function getRevisionOnly() {
		return $this->_revisionOnly;
	}

	/**
	 * Get the id of the file to be revised (if any).
	 * @return integer
	 */
	function getRevisedFileId() {
		return $this->_revisedFileId;
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

		// Assign the file stage.
		$templateMgr->assign('fileStage', $this->getFileStage());

		// Assign the pre-configured revised file id (if any).
		$templateMgr->assign('revisedFileId', $this->getRevisedFileId());

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
		$fileForm = new SubmissionFilesUploadForm($request, $monograph->getId(), $this->getFileStage(), $this->getRevisionOnly(), $this->getRevisedFileId());
		$fileForm->initData($args, $request);

		// Render the form.
		$json = new JSON(true, $fileForm->fetch($request));
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
		$uploadForm = new SubmissionFilesUploadForm($request, $monograph->getId(), $this->getFileStage(), $this->getRevisionOnly(), null);
		$uploadForm->readInputData();

		// Validate the form and upload the file.
		if ($uploadForm->validate($request)) {
			if (is_a($uploadedFile =& $uploadForm->execute(), 'MonographFile')) {
				// Retrieve file info to be used in a JSON response.
				$uploadedFileInfo = $this->_getUploadedFileInfo($uploadedFile);

				// If no revised file id was given then try out whether
				// the user maybe accidentally didn't identify this file as a revision.
				if (!$uploadForm->getRevisedFileId()) {
					$revisedFileId = $this->_checkForRevision($uploadedFile, $uploadForm->getMonographFiles());
					if ($revisedFileId) {
						// Instantiate the revision confirmation form.
						import('controllers.wizard.fileUpload.form.SubmissionFilesUploadConfirmationForm');
						$confirmationForm = new SubmissionFilesUploadConfirmationForm($request, $monograph->getId(), $this->getFileStage(), $revisedFileId, $uploadedFile);
						$confirmationForm->initData($args, $request);

						// Render the revision confirmation form.
						$json = new JSON(true, $confirmationForm->fetch($request), false, '0', $uploadedFileInfo);
						return $json->getString();
					}
				}

				// Advance to the next step (i.e. meta-data editing).
				$json = new JSON(true, '', false, '0', $uploadedFileInfo);
			} else {
				$json = new JSON(false, Locale::translate('common.uploadFailed'));
			}
		} else {
			$json = new JSON(false, array_pop($uploadForm->getErrorsArray()));
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
		$confirmationForm = new SubmissionFilesUploadConfirmationForm($request, $monograph->getId(), $this->getFileStage());
		$confirmationForm->readInputData();

		// Validate the form and revise the file.
		if ($confirmationForm->validate($request)) {
			if (is_a($uploadedFile =& $confirmationForm->execute(), 'MonographFile')) {
				// Go to the meta-data editing step.
				$json = new JSON(true, '', false, '0', $this->_getUploadedFileInfo($uploadedFile));
			} else {
				$json = new JSON(false, Locale::translate('common.uploadFailed'));
			}
		} else {
			$json = new JSON(false, array_pop($confirmationForm->getErrorsArray()));
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
		$json = new JSON(true, $metadataForm->fetch($request));
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
			return DAO::getDataChangedEvent($submissionFile->getFileId());
		} else {
			$json = new JSON(false, $metadataForm->fetch($request));
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
		$fileId = (int)$request->getUserVar('fileId');
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFile =& $submissionFileDao->getLatestRevision($fileId, $this->getFileStage(), $monograph->getId());
		if (!is_a($submissionFile, 'MonographFile')) fatalError('Invalid file id!');

		// Import the meta-data form based on the file implementation.
		if (is_a($submissionFile, 'ArtworkFile')) {
			import('controllers.wizard.fileUpload.form.SubmissionFilesArtworkMetadataForm');
			$metadataForm = new SubmissionFilesArtworkMetadataForm($submissionFile);
		} else {
			import('controllers.wizard.fileUpload.form.SubmissionFilesMetadataForm');
			$metadataForm = new SubmissionFilesMetadataForm($submissionFile);
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
			// to the uploaded file.
			similar_text($uploadedFileName, $monographFile->getOriginalFileName(), &$matchedPercentage);
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
