<?php

/**
 * @file controllers/informationCenter/SubmissionInformationCenterHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionInformationCenterHandler
 * @ingroup controllers_informationCenter
 *
 * @brief Handle requests to view the information center for a submission.
 */

import('controllers.informationCenter.InformationCenterHandler');
import('lib.pkp.classes.core.JSONMessage');
import('classes.log.MonographEventLogEntry');

class SubmissionInformationCenterHandler extends InformationCenterHandler {
	/** @var $monograph Monograph */
	var $monograph;

	/**
	 * Constructor
	 */
	function SubmissionInformationCenterHandler() {
		parent::InformationCenterHandler();
	}

	/**
	 * Fetch and store away objects
	 */
	function initialize(&$request, $args = null) {
		parent::initialize($request, $args);

		// Fetch the monograph to display information about
		$this->monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}

	/**
	 * Display the main information center modal.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewInformationCenter($args, &$request) {
		$this->setupTemplate();

		// Get the latest history item to display in the header
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$monographEvents =& $monographEventLogDao->getByMonographId($this->monograph->getId());
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

		return $templateMgr->fetchJson('controllers/informationCenter/informationCenter.tpl');
	}

	/**
	 * Display the notes tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewNotes($args, &$request) {
		$this->setupTemplate();

		import('controllers.informationCenter.form.NewMonographNoteForm');
		$notesForm = new NewMonographNoteForm($this->monograph->getId());
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

		import('controllers.informationCenter.form.NewMonographNoteForm');
		$notesForm = new NewMonographNoteForm($this->monograph->getId());
		$notesForm->readInputData();

		if ($notesForm->validate()) {
			$notesForm->execute();
			$json = new JSONMessage(true);

			// Save to event log
			$user =& $request->getUser();
			$userId = $user->getId();
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
		$notifyForm = new InformationCenterNotifyForm($this->monograph->getId(), ASSOC_TYPE_MONOGRAPH);
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
		$notifyForm = new InformationCenterNotifyForm($this->monograph->getId(), ASSOC_TYPE_MONOGRAPH);
		$notifyForm->readInputData();

		if ($notifyForm->validate()) {
			$noteId = $notifyForm->execute($request);

			// Return a JSON string indicating success
			// (will clear the form on return)
			$json = new JSONMessage(true);
		} else {
			// Return a JSON string indicating failure
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

		// Get all monograph events
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$fileEvents =& $monographEventLogDao->getByMonographId($this->monograph->getId());

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('eventLogEntries', $fileEvents);

		return $templateMgr->fetchJson('controllers/informationCenter/history.tpl');
	}

	/**
	 * Log an event for this file
	 * @param $request PKPRequest
	 * @param $eventType MONOGRAPH_LOG_...
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

		import('classes.log.MonographLog');
		MonographLog::logEvent($request, $this->monograph, $eventType, $logMessage);
	}

	/**
	 * Get an array representing link parameters that subclasses
	 * need to have passed to their various handlers (i.e. monograph ID to
	 * the delete note handler). Subclasses should implement.
	 */
	function _getLinkParams() {
		return array('monographId' => $this->monograph->getId());
	}

	/**
	 * Get the association ID for this information center view
	 * @return int
	 */
	function _getAssocId() {
		return $this->monograph->getId();
	}

	/**
	 * Get the association type for this information center view
	 * @return int
	 */
	function _getAssocType() {
		return ASSOC_TYPE_MONOGRAPH;
	}
}

?>
