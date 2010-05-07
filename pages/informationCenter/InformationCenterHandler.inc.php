<?php

/**
 * @file InformationCenterHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationCenterHandler
 * @ingroup pages_seriesEditor
 *
 * @brief Handle requests to view the information center for a file. 
 */

import('classes.handler.Handler');
import('lib.pkp.classes.core.JSON');
import('classes.monograph.log.MonographEventLogEntry');

class InformationCenterHandler extends Handler {
	var $comment;

	/**
	 * Constructor
	 */
	function InformationCenterHandler() {
		parent::Handler();
	}

	/**
	 * Display the main information center modal.
	 */
	function viewInformationCenter(&$args, &$request) {
		$fileId = Request::getUserVar('fileId');
		$this->validate($fileId);
		$this->setupTemplate(true);
		
		// Get the file in question
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);
		
		// Get the latest history item to display in the header
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$fileEvents =& $monographEventLogDao->getMonographLogEntriesByAssoc($monographFile->getMonographId(), ASSOC_TYPE_MONOGRAPH_FILE, $fileId);
		$lastEvent =& $fileEvents->next(); 

		// Assign variables to the template manager and display
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('monographFile', $monographFile);
		$templateMgr->assign_by_ref('fileId', $fileId);
		if(isset($lastEvent)) {
			$templateMgr->assign_by_ref('lastEvent', $lastEvent);

			// Get the user who posted the last note
			$userId = $lastEvent->getUserId();
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($userId);
			$templateMgr->assign_by_ref('lastEventUser', $user);
		}
		
		$json = new JSON('true', $templateMgr->fetch('informationCenter/informationCenter.tpl'));
		return $json->getString();
	}

	/**
	 * Display the notes tab.
	 */
	function viewNotes(&$args, &$request) {
		$fileId = Request::getUserVar('fileId');
		$this->validate($fileId);
		$this->setupTemplate(true);

		import('classes.informationCenter.form.InformationCenterNotesForm');
		$notesForm = new InformationCenterNotesForm($fileId);
		$notesForm->initData();
		$notesForm->display(); // FIXME: Should be wrapped in JSON: jQueryUI tabs needs to be modified to accept JSON
	}
	
	/**
	 * Save a note.
	 */
	function saveNote(&$args, &$request) {
		$fileId = Request::getUserVar('fileId');
		$this->validate($fileId);
		$this->setupTemplate(true);

		import('classes.informationCenter.form.InformationCenterNotesForm');
		$notesForm = new InformationCenterNotesForm($fileId);		
		$notesForm->readInputData();

		if ($notesForm->validate()) {
			$noteId = $notesForm->execute();

			// Success--Return a JSON string indicating so
			$templateMgr =& TemplateManager::getManager();
			$noteDao =& DAORegistry::getDAO('NoteDAO');
			$templateMgr->assign('note', $noteDao->getNoteById($noteId));
			$json = new JSON('true', $templateMgr->fetch('informationCenter/note.tpl'), 'false', $noteId);		
			
			// Save to event log
			$user =& $request->getUser();
			$userId = $user->getId();
			$this->logFileEvent($fileId, MONOGRAPH_LOG_FILE_NOTE_POSTED, $userId);
		} else {
			// Failure--Return a JSON string indicating so
			$json = new JSON('false');					
		}
		
		return $json->getString();	
	}
	
	/**
	 * Delete a note.
	 */
	function deleteNote(&$args, &$request) {
		$noteId = Request::getUserVar('noteId');
		$fileId = Request::getUserVar('fileId');
		$this->validate($fileId);

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$noteDao->deleteNoteById($noteId);

		$additionalAttributes = array('script' => "$('#note-$noteId').hide('slow')");
		$json = new JSON('true', '', 'true', null, $additionalAttributes);
		
		return $json->getString();
	}
	
	/**
	 * Display the notify tab.
	 */
	function viewNotify (&$args, &$request) {
		$fileId = Request::getUserVar('fileId');
		$this->validate($fileId);
		$this->setupTemplate(true);

		
		import('classes.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm($fileId);
		$notifyForm->initData();
		$notifyForm->display(); // FIXME: Should be wrapped in JSON: jQueryUI tabs needs to be modified to accept JSON
	}
	
	/**
	 * Send a notification from the notify tab.
	 */
	function sendNotification (&$args, &$request) {
		$fileId = Request::getUserVar('fileId');
		$this->validate($fileId);
		$this->setupTemplate(true);

		
		import('classes.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm($fileId);		
		$notifyForm->readInputData();

		if ($notifyForm->validate()) {
			$noteId = $notifyForm->execute();

			// Success--Return a JSON string indicating so (will clear the form on return, and indicate success)
			$json = new JSON('true');		
		} else {
			// Failure--Return a JSON string indicating so
			$json = new JSON('false');					
		}
		
		return $json->getString();	
	}
	
	/**
	 * Display the history tab.
	 */
	function viewHistory(&$args, &$request) {
		$fileId = Request::getUserVar('fileId');
		$this->validate($fileId);
		$this->setupTemplate(true);
		
		// Get the file in question to get the monograph Id
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);
		
		// Get all monograph file events
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$fileEvents =& $monographEventLogDao->getMonographLogEntriesByAssoc($monographFile->getMonographId(), ASSOC_TYPE_MONOGRAPH_FILE, $fileId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('eventLogEntries', $fileEvents);
		$templateMgr->display('informationCenter/history.tpl'); // FIXME: Should be wrapped in JSON: jQueryUI tabs needs to be modified to accept JSON
	}
	
	/**
	 * Log an event for this file
	 */
	function logFileEvent ($fileId, $eventType, $userId) {
		assert(!empty($fileId) && !empty($eventType) && !empty($userId));
		
		// Get the log event message
		switch($eventType) {
			case MONOGRAPH_LOG_FILE_NOTE_POSTED:
				$logMessage = 'informationCenter.history.notePosted';
				break;
			case MONOGRAPH_LOG_FILE_MESSAGE_SENT:
				$logMessage = 'informationCenter.history.messageSent';
				break;
		}
		
		// Get the file in question to get the monograph Id
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);
		
		$entry = new MonographEventLogEntry();
		$entry->setMonographId($monographFile->getMonographId());
		$entry->setUserId($userId);
		$entry->setDateLogged(Core::getCurrentDate());
		$entry->setEventType($eventType);
		$entry->setLogMessage($logMessage);
		$entry->setAssocType(ASSOC_TYPE_MONOGRAPH_FILE);
		$entry->setAssocId($fileId);

		import('classes.monograph.log.MonographLog');
		MonographLog::logEventEntry($monographFile->getMonographId(), $entry);
	}


	//
	// Validation
	//

	/**
	 * Validate that the user is the authorized to view the file.
	 */
	function validate($fileId) {
		parent::validate();

		// FIXME: Implement validation
		
		return true;
	}
}
?>
