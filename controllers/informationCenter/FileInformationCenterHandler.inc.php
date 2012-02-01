<?php

/**
 * @file controllers/informationCenter/FileInformationCenterHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileInformationCenterHandler
 * @ingroup controllers_informationCenter
 *
 * @brief Handle requests to view the information center for a file.
 */

import('controllers.informationCenter.InformationCenterHandler');
import('lib.pkp.classes.core.JSONMessage');
import('classes.log.MonographEventLogEntry');

class FileInformationCenterHandler extends InformationCenterHandler {
	/** @var $monographFile object */
	var $monographFile;

	/** @var $monograph object */
	var $monograph;

	/**
	 * Constructor
	 */
	function FileInformationCenterHandler() {
		parent::InformationCenterHandler();

		$this->addRoleAssignment(
			array(
				ROLE_ID_AUTHOR,
				ROLE_ID_SERIES_EDITOR,
				ROLE_ID_PRESS_MANAGER,
				ROLE_ID_PRESS_ASSISTANT
			),
			array('listPastNotes')
		);
	}

	/**
	 * Fetch and store away objects
	 */
	function initialize(&$request, $args = null) {
		parent::initialize($request, $args);

		// Fetch the monograph and file to display information about
		$this->monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$this->monographFile =& $submissionFileDao->getLatestRevision($request->getUserVar('fileId'));

		// Ensure data integrity.
		if (!$this->monograph || !$this->monographFile || $this->monograph->getId() != $this->monographFile->getMonographId()) fatalError('Unknown or invalid monograph or monograph file!');
	}

	/**
	 * Display the main information center modal.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewInformationCenter($args, &$request) {
		// Get the latest history item to display in the header
		$monographEventLogDao =& DAORegistry::getDAO('MonographFileEventLogDAO');
		$fileEvents =& $monographEventLogDao->getByFileId($this->monographFile->getFileId());
		$lastEvent =& $fileEvents->next();

		// Assign variables to the template manager and display
		$templateMgr =& TemplateManager::getManager();
		$fileName = (($s = $this->monographFile->getLocalizedName()) != '') ? $s : __('common.untitled');
		if (($i = $this->monographFile->getRevision()) > 1) $fileName .= " ($i)"; // Add revision number to label
		if (empty($fileName) ) $fileName = __('common.untitled');
		$templateMgr->assign_by_ref('title', $fileName);
		if(isset($lastEvent)) {
			$templateMgr->assign_by_ref('lastEvent', $lastEvent);

			// Get the user who posted the last note
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($lastEvent->getUserId());
			$templateMgr->assign_by_ref('lastEventUser', $user);
		}

		return parent::viewInformationCenter($request);
	}

	/**
	 * Display the notes tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewNotes($args, &$request) {
		$this->setupTemplate($request);

		// Provide access to notes from past revisions/file IDs
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('showPastNotesLinks', true);

		import('controllers.informationCenter.form.NewFileNoteForm');
		$notesForm = new NewFileNoteForm($this->monographFile->getFileId());
		$notesForm->initData();

		$json = new JSONMessage(true, $notesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Display the list of existing notes from prior files.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function listPastNotes($args, &$request) {
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();
		$noteDao =& DAORegistry::getDAO('NoteDAO');

		$monographFile = $this->monographFile;
		$notes = array();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
		while (true) {
			$monographFile = $submissionFileDao->getRevision($monographFile->getSourceFileId(), $monographFile->getSourceRevision());
			if (!$monographFile) break;

			$iterator =& $noteDao->getByAssoc($this->_getAssocType(), $monographFile->getFileId());
			$notes += $iterator->toArray();

			unset($iterator);
		}
		import('lib.pkp.classes.core.ArrayItemIterator');
		$templateMgr->assign('notes', new ArrayItemIterator($notes));

		$user =& $request->getUser();
		$templateMgr->assign('currentUserId', $user->getId());

		return $templateMgr->fetchJson('controllers/informationCenter/notesList.tpl');
	}

	/**
	 * Save a note.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveNote($args, &$request) {
		$this->setupTemplate($request);

		import('controllers.informationCenter.form.NewFileNoteForm');
		$notesForm = new NewFileNoteForm($this->monographFile->getFileId());
		$notesForm->readInputData();

		if ($notesForm->validate()) {
			$notesForm->execute($request);
			$json = new JSONMessage(true);

			// Save to event log
			$this->_logEvent($request, MONOGRAPH_LOG_NOTE_POSTED);

			$user =& $request->getUser();
			NotificationManager::createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.addedNote')));
		} else {
			// Return a JSON string indicating failure
			$json = new JSONMessage(false);
		}

		return $json->getString();
	}

	/**
	 * Display the notify tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewNotify ($args, &$request) {
		$this->setupTemplate($request);

		import('controllers.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm($this->monographFile->getFileId(), ASSOC_TYPE_MONOGRAPH_FILE);
		$notifyForm->initData();

		$json = new JSONMessage(true, $notifyForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Send a notification from the notify tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function sendNotification ($args, &$request) {
		$this->setupTemplate($request);

		import('controllers.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm($this->monographFile->getFileId(), ASSOC_TYPE_MONOGRAPH_FILE);
		$notifyForm->readInputData();

		if ($notifyForm->validate()) {
			$noteId = $notifyForm->execute($request);

			$user =& $request->getUser();
			NotificationManager::createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.sentNotification')));

			// Success--Return a JSON string indicating so (will clear the form on return, and indicate success)
			$json = new JSONMessage(true);
		} else {
			// Failure--Return a JSON string indicating so
			$json = new JSONMessage(false);
		}

		return $json->getString();
	}

	/**
	 * Display the history tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewHistory($args, &$request) {
		$this->setupTemplate($request);

		// Get all monograph file events
		$monographFileEventLogDao =& DAORegistry::getDAO('MonographFileEventLogDAO');
		$fileEvents =& $monographFileEventLogDao->getByFileId(
			$this->monographFile->getFileId()
		);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('eventLogEntries', $fileEvents);

		return $templateMgr->fetchJson('controllers/informationCenter/history.tpl');
	}

	/**
	 * Log an event for this file
	 * @param $request PKPRequest
	 * @param $eventType int MONOGRAPH_LOG_...
	 */
	function _logEvent ($request, $eventType) {
		// Get the log event message
		switch($eventType) {
			case MONOGRAPH_LOG_NOTE_POSTED:
				$logMessage = 'informationCenter.history.notePosted';
				break;
			case MONOGRAPH_LOG_MESSAGE_SENT:
				$logMessage = 'informationCenter.history.messageSent';
				break;
			default:
				assert(false);
		}

		import('classes.log.MonographFileLog');
		MonographFileLog::logEvent($request, $this->monographFile, $eventType, $logMessage);
	}

	/**
	 * Get an array representing link parameters that subclasses
	 * need to have passed to their various handlers (i.e. monograph ID to
	 * the delete note handler). Subclasses should implement.
	 */
	function _getLinkParams() {
		return array(
			'fileId' => $this->monographFile->getFileId(),
			'monographId' => $this->monograph->getId(),
			'stageId' => $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE)
		);
	}

	/**
	 * Get the association ID for this information center view
	 * @return int
	 */
	function _getAssocId() {
		return $this->monographFile->getFileId();
	}

	/**
	 * Get the association type for this information center view
	 * @return int
	 */
	function _getAssocType() {
		return ASSOC_TYPE_MONOGRAPH_FILE;
	}
}

?>
