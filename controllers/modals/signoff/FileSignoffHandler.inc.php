<?php

/**
 * @defgroup controllers_modal_signoff Signoff modal controller
 */

/**
 * @file controllers/modals/signoff/FileSignoffHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffHandler
 * @ingroup controllers_modal_signoff
 *
 * @brief A controller that handles basic server-side
 *  operations of the modal/form to signoff on a file.
 */

// Import the base handler.
import('lib.pkp.classes.file.FileManagementHandler');

// Import JSON class for use with all AJAX requests.
import('lib.pkp.classes.core.JSONMessage');


class FileSignoffHandler extends FileManagementHandler {

	/** @var string */
	var $_symbolic;

	/** @var int */
	var $_signoffId;


	/**
	 * Constructor
	 */
	function FileSignoffHandler() {
		parent::FileManagementHandler();
		// FIXME #6979: all roles can see readSignoff, but other ops require the user to own the signoff.
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_AUTHOR),
			array('displayFileUploadForm', 'uploadFile', 'signoff', 'readSignoff', 'signoffRead')
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize($request, $args) {
		parent::initialize($request, $args);

		// Already validated in authorize, if present.
		$this->_signoffId = $request->getUserVar('signoffId') ? (int) $request->getUserVar('signoffId') : null;
		$this->_symbolic = $request->getUserVar('symbolic')?$request->getUserVar('symbolic') : null;

		// Load translations.
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_COMMON,
			LOCALE_COMPONENT_APP_COMMON,
			LOCALE_COMPONENT_PKP_GRID
		);
	}


	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.SignoffAccessPolicy');

		// Check the operation to define the access mode.
		$router = $request->getRouter();
		$operation = $router->getRequestedOp($request);

		$mode = SIGNOFF_ACCESS_MODIFY;
		switch ($operation) {
			case 'readSignoff':
			case 'signoffRead':
				$mode = SIGNOFF_ACCESS_READ;
				break;
			default:
				break;
		}

		// If a signoff ID was specified, authorize it.
		if ($request->getUserVar('signoffId')) {
			// This will be authorized in WorkflowStageAccessPolicy
			$stageId = (int) $request->getUserVar('stageId');
			$this->addPolicy(new SignoffAccessPolicy($request, $args, $roleAssignments, $mode, $stageId));
		}
		$symbolic = $request->getUserVar('symbolic');
		if ($symbolic) {
			$signoffDao = DAORegistry::getDAO('SignoffDAO');
			$symbolics = $signoffDao->getAllSymbolics();
			if (!in_array($symbolic, $symbolics)) {
				return false;
			}
		}

		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the Symbolic of the signoff (if any)
	 * @return string
	 */
	function getSymbolic() {
		return $this->_symbolic;
	}


	function getSignoffId() {
		return $this->_signoffId;
	}


	//
	// Public handler methods
	//
	/**
	 * Render the file upload form in its initial state.
	 * @param $args array
	 * @param $request Request
	 * @return JSONMessage JSON object
	 */
	function readSignoff($args, $request) {
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$signoff = $this->getAuthorizedContextObject(ASSOC_TYPE_SIGNOFF);

		// Sanity check.
		if (!$signoff) {
			return new JSONMessage(false);
		}

		// Get related objects for the form to authenticate
		$monograph = $this->getSubmission();
		$stageId = $this->getStageId();
		if ($signoff->getAssocType() != ASSOC_TYPE_SUBMISSION_FILE) assert(false);
		$signoffFile = $submissionFileDao->getLatestRevision($signoff->getAssocId());

		// Set up the template
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('submissionId', $monograph->getId());
		$templateMgr->assign('stageId', $stageId);
		$templateMgr->assign('signoffId', $signoff->getId());
		$templateMgr->assign('signoffFileName', $signoffFile->getLocalizedName());

		// Check if there is a note and assign it for dispaly
		$noteDao = DAORegistry::getDAO('NoteDAO'); /* @var $noteDao NoteDAO */
		$notes = $noteDao->getByAssoc(ASSOC_TYPE_SIGNOFF, $signoff->getId());
		if (!$notes->wasEmpty()) {
			$lastNote = $notes->next();
			$templateMgr->assign('noteText', $lastNote->getContents());
		} else {
			$templateMgr->assign('noteText', '');
		}

		// Check if there is a response file and assign it for download
		if ($signoff->getFileId() && $signoff->getFileRevision()) {
			$responseFile = $submissionFileDao->getRevision($signoff->getFileId(), $signoff->getFileRevision());
			assert(is_a($responseFile, 'MonographFile'));

			import('lib.pkp.controllers.api.file.linkAction.DownloadFileLinkAction');
			$downloadFileAction = new DownloadFileLinkAction($request, $responseFile, $stageId);
			$templateMgr->assign('downloadSignoffResponseFileAction', $downloadFileAction);
		} else {
			$templateMgr->assign('downloadSignoffResponseFileAction', false);
		}

		return $templateMgr->fetchJson('controllers/modals/signoff/readSignoff.tpl');
	}


	/**
	 * Mark the signoff as viewed?
	 * For now, this is doing nothing (obviously).
	 * @param $args array
	 * @param $request Request
	 * @return JSONMessage JSON object
	 */
	function signoffRead($args, $request) {
		return new JSONMessage(true);
	}


	/**
	 * Render the file upload form in its initial state.
	 * @param $args array
	 * @param $request Request
	 * @return JSONMessage JSON object
	 */
	function displayFileUploadForm($args, $request) {
		$monograph = $this->getSubmission();

		import('controllers.modals.signoff.form.SignoffFileUploadForm');
		$fileForm = new SignoffFileUploadForm(
			$monograph->getId(), $this->getStageId(),
			$this->getSymbolic(), $this->getSignoffId()
		);

		$fileForm->initData($args, $request);

		// Render the form.
		return new JSONMessage(true, $fileForm->fetch($request));
	}


	/**
	 * Upload a file and render the modified upload wizard.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function uploadFile($args, $request) {
		$user = $request->getUser();

		import('lib.pkp.classes.file.TemporaryFileManager');
		$temporaryFileManager = new TemporaryFileManager();
		$temporaryFile = $temporaryFileManager->handleUpload('uploadedFile', $user->getId());
		if ($temporaryFile) {
			$json = new JSONMessage(true);
			$json->setAdditionalAttributes(array(
				'temporaryFileId' => $temporaryFile->getId()
			));
			return $json;
		}
		return new JSONMessage(false, __('common.uploadFailed'));
	}


	/**
	 * Copy the file to the right place (if any) and add the note
	 * @param $args
	 * @param $request
	 * @return JSONMessage JSON object
	 */
	function signoff($args, $request) {
		// Check for the case the form was displayed with no signoffs
		if ($request->getUserVar('noSignoffs')) {
			return new JSONMessage(true);
		}
		$monograph = $this->getSubmission();

		// Instantiate the file upload form.
		import('controllers.modals.signoff.form.SignoffFileUploadForm');
		$uploadForm = new SignoffFileUploadForm(
			$monograph->getId(), $this->getStageId(),
			$this->getSymbolic(), $this->getSignoffId()
		);
		$uploadForm->readInputData();

		// Validate the form and upload the file.
		if ($uploadForm->validate($request)) {
			$uploadForm->execute($request);

			// Create trivial notification.
			$user = $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.uploadedResponse')));

			// FIXME: this is being used for both category grids and file grids
			// if we return the AssocId() it works for category grids, but not file ones
			// if we return the signoffId() it works for file grids, but not the category ones.
			return DAO::getDataChangedEvent();
		} else {
			$errors = $uploadForm->getErrorsArray();
			return new JSONMessage(false, array_pop($errors));
		}
	}


	//
	// Private helper methods
	//
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
