<?php

/**
 * @file InformationCenterHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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

		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER), array(
			'viewInformationCenter', // Information Center
			'viewNotes', 'listNotes', 'saveNote', 'deleteNote', // Notes tab
			'viewNotify', 'sendNotification', // Notify tab
			'viewHistory' // History tab
		));
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public operations
	//
	/**
	 * Display the main information center modal.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewInformationCenter($args, &$request) {
		assert(false);
	}

	/**
	 * Display the notes tab.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewNotes($args, &$request) {
		assert(false);
	}

	/**
	 * Display the list of existing notes.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function listNotes($args, &$request) {
		assert(false);
	}

	/**
	 * Save a note.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveNote($args, &$request) {
		assert(false);
	}

	/**
	 * Delete a note.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function deleteNote($args, &$request) {
		assert(false);
	}

	/**
	 * Display the notify tab.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewNotify ($args, &$request) {
		assert(false);
	}

	/**
	 * Send a notification from the notify tab.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function sendNotification ($args, &$request) {
		assert(false);
	}

	/**
	 * Display the history tab.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewHistory($args, &$request) {
		assert(false);
	}

	/**
	 * Log an event for this item.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function _logEvent ($itemId, $eventType, $userId) {
		assert(false);
	}

	/**
	 * Get an array representing link parameters that subclasses
	 * need to have passed to their various handlers (i.e. monograph ID to
	 * the delete note handler). Subclasses should implement.
	 */
	function getLinkParams() {
		assert(false);
	}

	function setupTemplate() {
		// Fetch the monograph to display information about
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('linkParams', $this->getLinkParams());

		parent::setupTemplate();
	}
}

?>
