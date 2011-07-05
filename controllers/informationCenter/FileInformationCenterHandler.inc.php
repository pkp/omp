<?php

/**
 * @file controllers/informationCenter/FileInformationCenterHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
		if (!$this->monograph || !$this->monographFile || $this->monograph->getId() !== $this->monographFile->getMonographId()) fatalError('Unknown or invalid monograph!');
	}

	/**
	 * Display the main information center modal.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewInformationCenter($args, &$request) {
		$this->setupTemplate();

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

		return $templateMgr->fetchJson('controllers/informationCenter/informationCenter.tpl');
	}

	/**
	 * Display the notes tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewNotes($args, &$request) {
		$this->setupTemplate();

		import('controllers.informationCenter.form.NewFileNoteForm');
		$notesForm = new NewFileNoteForm($this->monographFile->getFileId());
		$notesForm->initData();

		$json = new JSONMessage(true, $notesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save a note.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveNote($args, &$request) {
		$this->setupTemplate();

		import('controllers.informationCenter.form.NewFileNoteForm');
		$notesForm = new NewFileNoteForm($this->monographFile->getFileId());
		$notesForm->readInputData();

		if ($notesForm->validate()) {
			$notesForm->execute();
			$json = new JSONMessage(true);

			// Save to event log
			$this->_logEvent($request, MONOGRAPH_LOG_NOTE_POSTED);
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
		$this->setupTemplate();

		import('controllers.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm(ASSOC_TYPE_MONOGRAPH_FILE, $this->monographFile->getFileId());
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
		$this->setupTemplate();

		import('controllers.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm(ASSOC_TYPE_MONOGRAPH_FILE, $this->monographFile->getItemId());
		$notifyForm->readInputData();

		if ($notifyForm->validate()) {
			$noteId = $notifyForm->execute($request);

			// Success--Return a JSON string indicating so (will clear the form on return, and indicate success)
			$json = new JSONMessage(true);
		} else {
			// Failure--Return a JSON string indicating so
			$json = new JSONMessage(false, __('informationCenter.notify.warning'));
		}

		return $json->getString();
	}

	/**
	 * Display the history tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewHistory($args, &$request) {
		$this->setupTemplate();

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
			'monographId' => $this->monograph->getId()
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
