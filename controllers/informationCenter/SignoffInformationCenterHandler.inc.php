<?php

/**
 * @file controllers/informationCenter/SignoffInformationCenterHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffInformationCenterHandler
 * @ingroup controllers_informationCenter
 *
 * @brief Handle requests to view the information center for a file.
 */

import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class SignoffInformationCenterHandler extends Handler {
	/** @var $signoff object */
	var $signoff;

	/** @var $monograph object */
	var $monograph;

	/** @var $monograph int */
	var $stageId;

	/**
	 * Constructor
	 */
	function SignoffInformationCenterHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(
				ROLE_ID_AUTHOR,
				ROLE_ID_SERIES_EDITOR,
				ROLE_ID_PRESS_MANAGER,
				ROLE_ID_PRESS_ASSISTANT
			),
			array('viewSignoffHistory', 'viewNotes', 'getUserSignoffs', 'fetchNotesForm', 'saveNote', 'listNotes', 'uploadFile')
		);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args = null) {
		parent::initialize($request, $args);

		// Fetch the monograph and file to display information about
		$this->monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$this->signoff =& $this->getAuthorizedContextObject(ASSOC_TYPE_SIGNOFF);
		$this->stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize($request, $args, $roleAssignments) {

		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));

		import('classes.security.authorization.OmpSignoffAccessPolicy');
		$router =& $request->getRouter();
		$mode = SIGNOFF_ACCESS_READ;
		if ($router->getRequestedOp($request) == 'saveNote') {
			$mode = SIGNOFF_ACCESS_MODIFY;
		}

		$router =& $request->getRouter();
		$requestedOp = $router->getRequestedOp($request);
		$stageId = $request->getUserVar('stageId');
		if ($request->getUserVar('signoffId')) {
			$this->addPolicy(new OmpSignoffAccessPolicy($request, $args, $roleAssignments, $mode, $stageId));
		} else if ($requestedOp == 'viewNotes' || $requestedOp == 'getUserSignoffs') {
			import('classes.security.authorization.internal.WorkflowStageRequiredPolicy');
			$this->addPolicy(new WorkflowStageRequiredPolicy($stageId));
		} else {
			return AUTHORIZATION_DENY;
		}

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_SUBMISSION);
		parent::setupTemplate();
	}

	/**
	 * Display a modal containing history for the signoff.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function viewSignoffHistory($args, &$request) {
		$this->setupTemplate();
		$user =& $request->getUser();

		$signoff =& $this->getAuthorizedContextObject(ASSOC_TYPE_SIGNOFF);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('signoff', $signoff);

		return $templateMgr->fetchJson('controllers/informationCenter/signoffHistory.tpl');
	}

	/**
	 * Fetch the signoff notes modal content.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function viewNotes($args, &$request) {
		$this->setupTemplate($request);
		$signoff =& $this->getAuthorizedContextObject(ASSOC_TYPE_SIGNOFF);
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monograph->getId());
		$templateMgr->assign('stageId', $stageId);
		if ($signoff) {
			$templateMgr->assign('signoffId', $signoff->getId());
		}
		return $templateMgr->fetchJson('controllers/informationCenter/signoffNotes.tpl');
	}

	/**
	 * Get the available signoffs associated with the user in request.
	 * @param $args
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function getUserSignoffs($args, &$request) {
		$user =& $request->getUser();
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$monographFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $monographFileDao SubmissionFileDAO */
		$signoffsFactory =& $signoffDao->getByUserId($user->getId());

		$signoffs = array();
		while ($signoff =& $signoffsFactory->next()) { /* @var $signoff Signoff */
			if (!$signoff->getDateCompleted() && $signoff->getAssocType() == ASSOC_TYPE_MONOGRAPH_FILE) {
				$monographFile =& $monographFileDao->getLatestRevision($signoff->getAssocId()); /* @var $monographFile MonographFile */
				assert(is_a($monographFile, 'MonographFile'));
				$signoffs[$signoff->getId()] = $monographFile->getLocalizedName();
			}
		}

		$json = new JSONMessage(true, $signoffs);
		return $json->getString();
	}

	/**
	 * Fetch the signoff notes form.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function fetchNotesForm($args, &$request) {
		$this->setupTemplate($request);
		$signoff =& $this->getAuthorizedContextObject(ASSOC_TYPE_SIGNOFF);
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		import('controllers.grid.files.fileSignoff.form.NewSignoffNoteForm');
		$notesForm = new NewSignoffNoteForm($signoff->getId(), $monograph->getId(), $signoff->getSymbolic(), $this->stageId);
		$notesForm->initData();

		$json = new JSONMessage(true, $notesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save a signoff note.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveNote($args, &$request) {
		$this->setupTemplate($request);
		$signoff =& $this->signoff;
		$monograph =& $this->monograph;
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

		import('controllers.grid.files.fileSignoff.form.NewSignoffNoteForm');
		$notesForm = new NewSignoffNoteForm($signoff->getId(), $monograph->getId(), $signoff->getSymbolic(), $this->stageId);
		$notesForm->readInputData();

		if ($notesForm->validate()) {
			$notesForm->execute($request, $userRoles);
			$json = new JSONMessage(true);
		} else {
			// Return a JSON string indicating failure
			$json = new JSONMessage(false);
		}

		return $json->getString();
	}

	/**
	 * List signoff notes.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function listNotes($args, &$request) {
		$this->setupTemplate($request);
		$signoff =& $this->signoff;
		$monograph =& $this->monograph;

		$templateMgr =& TemplateManager::getManager();
		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$notesFactory =& $noteDao->getByAssoc(ASSOC_TYPE_SIGNOFF, $signoff->getId());
		$notes = $notesFactory->toAssociativeArray();
		// Get any note files.
		$noteFilesDownloadLink = array();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /** @var $submissionFileDao SubmissionFileDAO */
		import('controllers.api.file.linkAction.DownloadFileLinkAction');
		foreach ($notes as $noteId => $note) {
			$file =& $submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_NOTE, $noteId, $monograph->getId(), MONOGRAPH_FILE_NOTE);
			// We don't expect more than one file per note
			$file = current($file);

			// Get the download file link action.
			if ($file) {
				$noteFilesDownloadLink[$noteId] = new DownloadFileLinkAction($request, $file, $this->stageId);
			}
		}

		$user =& $request->getUser();

		import('lib.pkp.classes.core.ArrayItemIterator');
		$templateMgr->assign('notes', new ArrayItemIterator($notes));
		$templateMgr->assign('noteFilesDownloadLink', $noteFilesDownloadLink);
		$templateMgr->assign('notesListId', 'notesList');
		$templateMgr->assign('currentUserId', $user->getId());
		$templateMgr->assign('notesDeletable', false);

		$json = new JSONMessage(true, $templateMgr->fetch('controllers/informationCenter/notesList.tpl'));
		$json->setEvent('dataChanged');
		return $json->getString();
	}

	/**
	 * Upload a file and render the modified upload wizard.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function uploadFile($args, &$request) {
		$user =& $request->getUser();

		import('classes.file.TemporaryFileManager');
		$temporaryFileManager = new TemporaryFileManager();
		$temporaryFile = $temporaryFileManager->handleUpload('uploadedFile', $user->getId());
		if ($temporaryFile) {
			$json = new JSONMessage(true);
			$json->setAdditionalAttributes(array(
				'temporaryFileId' => $temporaryFile->getId()
			));
		} else {
			$json = new JSONMessage(false, __('common.uploadFailed'));
		}

		return $json->getString();
	}
}

?>
