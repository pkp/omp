<?php

/**
 * @file SubmissionInformationCenterHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
		$itemId = Request::getUserVar('itemId');
		$this->setupTemplate(true);

		// Get the file in question
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($itemId);

		// Get the latest history item to display in the header
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$monographEvents =& $monographEventLogDao->getMonographLogEntries($itemId);
		$lastEvent =& $monographEvents->next();

		// Assign variables to the template manager and display
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('title', $monograph->getLocalizedTitle());
		$templateMgr->assign_by_ref('monographId', $monograph->getId());
		$templateMgr->assign_by_ref('itemId', $itemId);
		if(isset($lastEvent)) {
			$templateMgr->assign_by_ref('lastEvent', $lastEvent);

			// Get the user who posted the last note
			$userId = $lastEvent->getUserId();
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($userId);
			$templateMgr->assign_by_ref('lastEventUser', $user);
		}

		$json = new JSON('true', $templateMgr->fetch('controllers/informationCenter/informationCenter.tpl'));
		return $json->getString();
	}
	/**
	 * Display the notes tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewNotes($args, &$request) {
		$itemId = Request::getUserVar('itemId');
		$this->setupTemplate(true);

		import('controllers.informationCenter.form.InformationCenterNotesForm');
		$notesForm = new InformationCenterNotesForm($itemId, ASSOC_TYPE_MONOGRAPH);
		$notesForm->initData();

		$json = new JSON('true', $notesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Display the notify tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewNotify ($args, &$request) {
		$itemId = Request::getUserVar('itemId');
		$this->setupTemplate(true);

		import('controllers.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm($itemId, ASSOC_TYPE_MONOGRAPH);
		$notifyForm->initData();

		$json = new JSON('true', $notifyForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Send a notification from the notify tab.
	 */
	function sendNotification ($args, &$request) {
		$itemId = Request::getUserVar('itemId');
		$this->setupTemplate(true);

		import('controllers.informationCenter.form.InformationCenterNotifyForm');
		$notifyForm = new InformationCenterNotifyForm($itemId, ASSOC_TYPE_MONOGRAPH);
		$notifyForm->readInputData();

		if ($notifyForm->validate()) {
			$noteId = $notifyForm->execute($request);

			// Success--Return a JSON string indicating so (will clear the form on return, and indicate success)
			$json = new JSON('true');
		} else {
			// Failure--Return a JSON string indicating so
			$json = new JSON('false', Locale::translate("informationCenter.notify.warning"));
		}

		return $json->getString();
	}

	/**
	 * Display the history tab.
	 */
	function viewHistory($args, &$request) {
		$itemId = Request::getUserVar('itemId');
		$this->setupTemplate(true);

		// Get all monograph events
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$fileEvents =& $monographEventLogDao->getMonographLogEntries($itemId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('eventLogEntries', $fileEvents);

		$json = new JSON('true', $templateMgr->fetch('controllers/informationCenter/history.tpl'));
		return $json->getString();
	}

	/**
	 * Log an event for this file
	 */
	function _logEvent ($itemId, $eventType, $userId) {
		assert(!empty($itemId) && !empty($eventType) && !empty($userId));

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
		$entry->setMonographId($itemId);
		$entry->setUserId($userId);
		$entry->setDateLogged(Core::getCurrentDate());
		$entry->setEventType($eventType);
		$entry->setLogMessage($logMessage);

		import('classes.monograph.log.MonographLog');
		MonographLog::logEventEntry($itemId, $entry);
	}

}
?>
