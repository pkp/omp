<?php

/**
 * @file SubmissionInformationCenterHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionInformationCenterHandler
 * @ingroup controllers_informationCenterHandler
 *
 * @brief Handle requests to view the information center for a submission.
 */

import('controllers.informationCenter.InformationCenterHandler');
import('lib.pkp.classes.core.JSON');
import('classes.monograph.log.MonographEventLogEntry');

class SubmissionInformationCenterHandler extends InformationCenterHandler {
	/**
	 * Constructor
	 */
	function SubmissionInformationCenterHandler() {
		parent::InformationCenterHandler();
	}

	/**
	 * Display the main information center modal.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewInformationCenter($args, &$request) {
		$this->setupTemplate();

		// Fetch the monograph to display information about
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Get the latest history item to display in the header
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$monographEvents =& $monographEventLogDao->getMonographLogEntries($monograph->getId());
		$lastEvent =& $monographEvents->next();

		// Assign variables to the template manager and display
		$templateMgr =& TemplateManager::getManager();
		if(isset($lastEvent)) {
			$templateMgr->assign_by_ref('lastEvent', $lastEvent);

			// Get the user who posted the last note
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($lastEvent->getUserId());
			$templateMgr->assign_by_ref('lastEventUser', $user);
		}

		$json = new JSON(true, $templateMgr->fetch('controllers/informationCenter/informationCenter.tpl'));
		return $json->getString();
	}

	/**
	 * Display the notes tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewNotes($args, &$request) {
		// Fetch the monograph to display information about
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$this->setupTemplate();

		import('controllers.informationCenter.form.NewMonographNoteForm');
		$notesForm = new NewMonographNoteForm($monograph->getId());
		$notesForm->initData();

		$json = new JSON(true, $notesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Display the list of existing notes.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function listNotes($args, &$request) {
		// Fetch the monograph to display information about
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$this->setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$templateMgr->assign('notes', $noteDao->getByAssoc(ASSOC_TYPE_MONOGRAPH, $monograph->getId()));
		$templateMgr->assign('monographId', $monograph->getId());
		$json = new JSON(true, $templateMgr->fetch('controllers/informationCenter/notesList.tpl'));

		return $json->getString();
	}

	/**
	 * Save a note.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveNote($args, &$request) {
		// Fetch the monograph to display information about
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$this->setupTemplate();

		import('controllers.informationCenter.form.NewMonographNoteForm');
		$notesForm = new NewMonographNoteForm($monograph->getId());
		$notesForm->readInputData();

		if ($notesForm->validate()) {
			$notesForm->execute();
			$json = new JSON(true);

			// Save to event log
			$user =& $request->getUser();
			$userId = $user->getId();
			$this->_logEvent($monograph->getId(), MONOGRAPH_LOG_NOTE_POSTED, $userId);
		} else {
			// Return a JSON string indicating failure
			$json = new JSON(false);
		}

		return $json->getString();
	}

	/**
	 * Delete a note.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function deleteNote($args, &$request) {
		// Fetch the monograph to display information about
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$noteId = (int) $request::getUserVar('noteId');
		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$note =& $noteDao->getById($noteId);
		assert ($note && $note->getAssocType == ASSOC_TYPE_MONOGRAPH && $note->getAssocId() == $monograph->getId());
		$noteDao->deleteById($noteId);

		$additionalAttributes = array('script' => "$('#note-$noteId').hide('slow')");
		$json = new JSON(true, '', true, null, $additionalAttributes);

		return $json->getString();
	}

	/**
	 * Display the notify tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewNotify ($args, &$request) {
		// Fetch the monograph to display information about
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$this->setupTemplate();

		import('controllers.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm($monograph->getId(), ASSOC_TYPE_MONOGRAPH);
		$notifyForm->initData();

		$json = new JSON(true, $notifyForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Send a notification from the notify tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function sendNotification ($args, &$request) {
		// Fetch the monograph to display information about
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$this->setupTemplate();

		import('controllers.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm($monograph->getId(), ASSOC_TYPE_MONOGRAPH);
		$notifyForm->readInputData();

		if ($notifyForm->validate()) {
			$noteId = $notifyForm->execute($request);

			// Return a JSON string indicating success
			// (will clear the form on return)
			$json = new JSON(true);
		} else {
			// Return a JSON string indicating failure
			$json = new JSON(false, Locale::translate('informationCenter.notify.warning'));
		}

		return $json->getString();
	}

	/**
	 * Display the history tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewHistory($args, &$request) {
		// Fetch the monograph to display information about
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$this->setupTemplate();

		// Get all monograph events
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$fileEvents =& $monographEventLogDao->getMonographLogEntries($monograph->getId());

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('eventLogEntries', $fileEvents);

		$json = new JSON(true, $templateMgr->fetch('controllers/informationCenter/history.tpl'));
		return $json->getString();
	}

	/**
	 * Log an event for this file
	 */
	function _logEvent ($monographId, $eventType, $userId) {
		assert(!empty($monographId) && !empty($eventType) && !empty($userId));

		// Get the log event message
		switch($eventType) {
			case MONOGRAPH_LOG_NOTE_POSTED:
				$logMessage = 'informationCenter.history.notePosted';
				break;
			case MONOGRAPH_LOG_MESSAGE_SENT:
				$logMessage = 'informationCenter.history.messageSent';
				break;
		}

		$entry = new MonographEventLogEntry();
		$entry->setMonographId($monographId);
		$entry->setUserId($userId);
		$entry->setDateLogged(Core::getCurrentDate());
		$entry->setEventType($eventType);
		$entry->setLogMessage($logMessage);

		import('classes.monograph.log.MonographLog');
		MonographLog::logEventEntry($monographId, $entry);
	}

	/**
	 * Get an array representing link parameters that subclasses
	 * need to have passed to their various handlers (i.e. monograph ID to
	 * the delete note handler). Subclasses should implement.
	 */
	function getLinkParams() {
		// Fetch the monograph to display information about
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		return array('monographId' => $monograph->getId());
	}
}

?>
