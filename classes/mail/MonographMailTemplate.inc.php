<?php

/**
 * @file classes/mail/MonographMailTemplate.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographMailTemplate
 * @ingroup mail
 *
 * @brief Subclass of MailTemplate for sending emails related to monographs.
 *
 * This allows for monograph-specific functionality like logging, etc.
 */



import('classes.mail.MailTemplate');
import('classes.log.MonographEmailLogEntry'); // Bring in log constants

class MonographMailTemplate extends MailTemplate {

	/** @var object the associated monograph */
	var $monograph;

	/** @var object the associated press */
	var $press;

	/** @var int Event type of this email for logging purposes */
	var $logEventType;

	/**
	 * Constructor.
	 * @param $monograph object
	 * @param $emailKey string optional
	 * @param $locale string optional
	 * @param $enableAttachments boolean optional
	 * @param $press object optional
	 * @param $includeSignature boolean optional
	 * @see MailTemplate::MailTemplate()
	 */
	function MonographMailTemplate($monograph, $emailKey = null, $locale = null, $enableAttachments = null, $press = null, $includeSignature = true) {
		parent::MailTemplate($emailKey, $locale, $enableAttachments, $press, $includeSignature);
		$this->monograph = $monograph;
	}

	function assignParams($paramArray = array()) {
		$monograph =& $this->monograph;
		$press = isset($this->press)?$this->press:Request::getPress();

		$paramArray['monographTitle'] = strip_tags($monograph->getLocalizedTitle());
		$paramArray['monographId'] = $monograph->getId();
		$paramArray['pressName'] = strip_tags($press->getLocalizedName());
		$paramArray['seriesName'] = strip_tags($monograph->getSeriesTitle());
		$paramArray['monographAbstract'] = String::html2text($monograph->getLocalizedAbstract());
		$paramArray['authorString'] = strip_tags($monograph->getAuthorString());

		parent::assignParams($paramArray);
	}

	/**
	 * @see parent::send()
	 * @param $request PKPRequest optional (used for logging purposes)
	 */
	function send($request = null) {
		if (parent::send(false)) {
			if (!isset($this->skip) || !$this->skip) $this->log($request);
			$user =& Request::getUser();
			if ($this->attachmentsEnabled) $this->_clearAttachments($user->getId());
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @see parent::sendWithParams()
	 */
	function sendWithParams($paramArray) {
		$savedSubject = $this->getSubject();
		$savedBody = $this->getBody();

		$this->assignParams($paramArray);

		$ret = $this->send();

		$this->setSubject($savedSubject);
		$this->setBody($savedBody);

		return $ret;
	}

	/**
	 * Add logging properties to this email.
	 * @param $eventType int
	 */
	function setEventType($eventType) {
		$this->logEventType = $eventType;
	}

	/**
	 * Set the press this message is associated with.
	 * @param $press object
	 */
	function setPress($press) {
		$this->press = $press;
	}

	/**
	 * Save the email in the monograph email log.
	 */
	function log($request = null) {
		import('classes.log.MonographLog');
		$entry = new MonographEmailLogEntry();
		$monograph =& $this->monograph;

		// Event data
		$entry->setEventType($this->logEventType);
		$entry->setAssocType(ASSOC_TYPE_MONOGRAPH);
		$entry->setAssocId($monograph->getId());
		$entry->setDateSent(Core::getCurrentDate());

		// User data
		if ($request) {
			$user =& $request->getUser();
			$entry->setSenderId($user == null ? 0 : $user->getId());
			$entry->setIPAddress($request->getRemoteAddr());
		} else {
			// No user supplied -- this is e.g. a cron-automated email
			$entry->setSenderId(0);
		}

		// Email data
		$entry->setSubject($this->getSubject());
		$entry->setBody($this->getBody());
		$entry->setFrom($this->getFromString(false));
		$entry->setRecipients($this->getRecipientString());
		$entry->setCcs($this->getCcString());
		$entry->setBccs($this->getBccString());

		// Add log entry
		$logDao =& DAORegistry::getDAO('MonographEmailLogDAO');
		$logEntryId = $logDao->insertObject($entry);

		// Add attachments
		import('classes.file.MonographFileManager');
		foreach ($this->getAttachmentFiles() as $attachment) {
			MonographFileManager::temporaryFileToMonographFile(
				$monograph->getId(),
				$attachment,
				MONOGRAPH_FILE_ATTACHMENT,
				ASSOC_TYPE_MONOGRAPH_EMAIL_LOG_ENTRY,
				$logEntryId
			);
		}
	}

	/**
	 *  Send this email to all assigned series editors
	 * @param $monographId int
	 */
	function toAssignedSeriesEditors($monographId) {
		return $this->_addUsers($monographId, ROLE_ID_SERIES_EDITOR, 'addRecipient');
	}

	/*
	 *  CC this email to all assigned series editors
	 * @param $monographId int
	 * @return array of Users (note, this differs from OxS which returns EditAssignment objects)
	 */
	function ccAssignedSeriesEditors($monographId) {
		return $this->_addUsers($monographId, ROLE_ID_SERIES_EDITOR, 'addCc');
	}

	/**
	 *  BCC this email to all assigned series editors
	 * @param $monographId int
	 */
	function bccAssignedSeriesEditors($monographId) {
		return $this->_addUsers($monographId, ROLE_ID_SERIES_EDITOR, 'addBcc');
	}

	/**
	 * Private method to fetch the requested users and add to the email
	 * @param $monographId int
	 * @param $roleId int
	 * @param $method string one of addRecipient, addCC, or addBCC
	 * @return array of Users (note, this differs from OxS which returns EditAssignment objects)
	 */
	function _addUsers($monographId, $roleId, $method) {
		assert(in_array($method, array('addRecipient', 'addCc', 'addBcc')));

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByRoleId($this->press->getId(), $roleId);

		$returner = array();
		// Cycle through all the userGroups for this role
		while ( $userGroup =& $userGroups->next() ) {
			$userStageAssignmentDao =& DAORegistry::getDAO('UserStageAssignmentDAO');
			// FIXME: #6692# Should maybe be getting assignments for a specific stage only. (also, maybe user group?)
			$users =& $userStageAssignmentDao->getUsersBySubmissionAndStageId($monographId, null, $userGroup->getId());
			while ($user =& $users->next()) {
				$this->$method($user->getEmail(), $user->getFullName());
				$returner[] =& $user;
				unset($user);
			}
			unset($userGroup);
		}
		return $returner;
	}
}

?>
