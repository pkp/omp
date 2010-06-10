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
 * @brief Parent class for file/submission information center handlers. 
 */

import('classes.handler.Handler');
import('lib.pkp.classes.core.JSON');
import('classes.monograph.log.MonographEventLogEntry');

class InformationCenterHandler extends Handler {
	/**
	 * Constructor
	 */
	function InformationCenterHandler() {
		parent::Handler();
	}
	
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array('viewInformationCenter', 'viewNotes', 'saveNote', 'deleteNote', 'viewNotify', 'sendNotification', 'viewHistory');
	}
	

	/**
	 * Display the main information center modal.
	 * NB: sub-classes must implement this method.
	 */
	function viewInformationCenter(&$args, &$request) {
		assert(false);
	}

	/**
	 * Display the notes tab.
	 */
	function viewNotes(&$args, &$request) {
		assert('false');
	}
	
	/**
	 * Save a note.
	 */
	function saveNote(&$args, &$request) {
		// FIXME: assocId and assocType should not be specified in request
		$assocId = Request::getUserVar('assocId');
		$assocType = Request::getUserVar('assocType');
		$this->validate($assocId);
		$this->setupTemplate(true);

		import('controllers.informationCenter.form.InformationCenterNotesForm');
		$notesForm = new InformationCenterNotesForm($assocId, $assocType);		
		$notesForm->readInputData();

		if ($notesForm->validate()) {
			$noteId = $notesForm->execute();

			// Success--Return a JSON string indicating so
			$templateMgr =& TemplateManager::getManager();
			$noteDao =& DAORegistry::getDAO('NoteDAO');
			$templateMgr->assign('note', $noteDao->getNoteById($noteId));
			$json = new JSON('true', $templateMgr->fetch('controllers/informationCenter/note.tpl'), 'false', $noteId);		
			
			// Save to event log
			$user =& $request->getUser();
			$userId = $user->getId();
			$this->_logEvent($assocId, MONOGRAPH_LOG_NOTE_POSTED, $userId);
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
		$assocId = Request::getUserVar('assocId');
		$this->validate($assocId);

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$noteDao->deleteById($noteId);

		$additionalAttributes = array('script' => "$('#note-$noteId').hide('slow')");
		$json = new JSON('true', '', 'true', null, $additionalAttributes);
		
		return $json->getString();
	}
	
	/**
	 * Display the notify tab.
	 */
	function viewNotify (&$args, &$request) {
		$assocId = Request::getUserVar('assocId');
		$this->validate($assocId);
		$this->setupTemplate(true);

		import('controllers.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm($assocId);
		$notifyForm->initData();

		$json = new JSON('true', $notifyForm->fetch($request));
		return $json->getString();
	}
	
	/**
	 * Send a notification from the notify tab.
	 */
	function sendNotification (&$args, &$request) {
		$assocId = Request::getUserVar('assocId');
		$this->validate($assocId);
		$this->setupTemplate(true);

		
		import('controllers.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm($assocId);		
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
	 * NB: sub-classes must implement this method.
	 */
	function viewHistory(&$args, &$request) {
		assert(false);
	}
	
	/**
	 * Log an event for this item.
	 * NB: sub-classes must implement this method.
	 */
	function _logEvent ($assocId, $eventType, $userId) {
		assert('false');
	}

	//
	// Validation
	//

	/**
	 * Validate that the user is the authorized to view the file.
	 */
	function validate($assocId) {
		parent::validate();

		// FIXME: Implement validation
		
		return true;
	}
}
?>
