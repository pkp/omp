<?php

/**
 * @file FileInformationCenterHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileInformationCenterHandler
 * @ingroup controllers_informationCenter
 *
 * @brief Handle requests to view the information center for a file.
 */

import('controllers.informationCenter.InformationCenterHandler');
import('lib.pkp.classes.core.JSON');
import('classes.monograph.log.MonographEventLogEntry');

class FileInformationCenterHandler extends InformationCenterHandler {
	/**
	 * Constructor
	 */
	function FileInformationCenterHandler() {
		parent::InformationCenterHandler();
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		// FIXME: #5600 - Distribute access differently to reviewers and editor roles
		/*import('classes.security.authorization.OmpWorkflowStagePolicy');
		$this->addPolicy(new OmpWorkflowStagePolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);*/
		return true;
	}

	/**
	 * Display the main information center modal.
	 */
	function viewInformationCenter(&$args, &$request) {
		$assocId = Request::getUserVar('assocId');
		$this->validate($assocId);
		$this->setupTemplate(true);

		// Get the file in question
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($assocId);

		// Get the latest history item to display in the header
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$fileEvents =& $monographEventLogDao->getMonographLogEntriesByAssoc($monographFile->getMonographId(), ASSOC_TYPE_MONOGRAPH_FILE, $assocId);
		$lastEvent =& $fileEvents->next();

		// Assign variables to the template manager and display
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('monographFile', $monographFile);
		$templateMgr->assign_by_ref('assocId', $assocId);
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
	 */
	function viewNotes(&$args, &$request) {
		$assocId = Request::getUserVar('assocId');
		$this->validate($assocId);
		$this->setupTemplate(true);

		import('controllers.informationCenter.form.InformationCenterNotesForm');
		$notesForm = new InformationCenterNotesForm($assocId, ASSOC_TYPE_MONOGRAPH_FILE);
		$notesForm->initData();

		$json = new JSON('true', $notesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Display the history tab.
	 */
	function viewHistory(&$args, &$request) {
		$assocId = Request::getUserVar('assocId');
		$this->validate($assocId);
		$this->setupTemplate(true);

		// Get the file in question to get the monograph Id
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($assocId);

		// Get all monograph file events
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$fileEvents =& $monographEventLogDao->getMonographLogEntriesByAssoc($monographFile->getMonographId(), ASSOC_TYPE_MONOGRAPH_FILE, $assocId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('eventLogEntries', $fileEvents);
		$templateMgr->display('controllers/informationCenter/history.tpl'); // FIXME: Should be wrapped in JSON: jQueryUI tabs needs to be modified to accept JSON
	}

	/**
	 * Log an event for this file
	 */
	function _logEvent ($assocId, $eventType, $userId) {
		assert(!empty($assocId) && !empty($eventType) && !empty($userId));

		// Get the log event message
		switch($eventType) {
			case MONOGRAPH_LOG_NOTE_POSTED:
				$logMessage = 'informationCenter.history.notePosted';
				break;
			case MONOGRAPH_LOG_MESSAGE_SENT:
				$logMessage = 'informationCenter.history.messageSent';
				break;
		}

		// Get the file in question to get the monograph Id
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($assocId);

		$entry = new MonographEventLogEntry();
		$entry->setMonographId($monographFile->getMonographId());
		$entry->setUserId($userId);
		$entry->setDateLogged(Core::getCurrentDate());
		$entry->setEventType($eventType);
		$entry->setLogMessage($logMessage);
		$entry->setAssocType(ASSOC_TYPE_MONOGRAPH_FILE);
		$entry->setAssocId($assocId);

		import('classes.monograph.log.MonographLog');
		MonographLog::logEventEntry($monographFile->getMonographId(), $entry);
	}

}
?>
