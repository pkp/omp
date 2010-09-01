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
		// FIXME: Validate that the user can view the file

		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('viewInformationCenter', 'viewNotes', 'saveNote', 'deleteNote', 'viewNotify',
				'sendNotification', 'viewHistory'));
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		$stageId = $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public operations
	//
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
		$itemId = Request::getUserVar('itemId');
		$itemType = Request::getUserVar('itemType');
		$this->setupTemplate(true);

		import('controllers.informationCenter.form.InformationCenterNotesForm');
		$notesForm = new InformationCenterNotesForm($itemId, $itemType);
		$notesForm->readInputData();

		if ($notesForm->validate()) {
			$noteId = $notesForm->execute();

			// Success--Return a JSON string indicating so
			$templateMgr =& TemplateManager::getManager();
			$noteDao =& DAORegistry::getDAO('NoteDAO');
			$templateMgr->assign('note', $noteDao->getById($noteId));
			$json = new JSON('true', $templateMgr->fetch('controllers/informationCenter/note.tpl'), 'false', $noteId);

			// Save to event log
			$user =& $request->getUser();
			$userId = $user->getId();
			$this->_logEvent($itemId, MONOGRAPH_LOG_NOTE_POSTED, $userId);
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
		$itemId = Request::getUserVar('itemId');

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
		assert(false);
	}

	/**
	 * Send a notification from the notify tab.
	 * NB: sub-classes must implement this method.
	 */
	function sendNotification (&$args, &$request) {
		assert(false);
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
	function _logEvent ($itemId, $eventType, $userId) {
		assert('false');
	}
}
?>
