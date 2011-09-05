<?php

/**
 * @file controllers/grid/files/copyedit/form/CopyeditingUserForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingUserForm
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief Form to add files to the final draft files grid
 */

import('lib.pkp.classes.form.Form');

class FileAuditorForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monograph;

	/* @var int */
	var $_stageId;

	/* @var string */
	var $_symbolic;

	/* @var string */
	var $_eventType;

	/**
	 * Constructor.
	 */
	function FileAuditorForm($monograph, $stageId, $symbolic, $eventType) {
		parent::Form('controllers/grid/files/signoff/form/addAuditor.tpl');
		$this->_monograph =& $monograph;
		$this->_stageId = $stageId;
		$this->_symbolic = $symbolic;
		$this->_eventType = $eventType;

		$this->addCheck(new FormValidator($this, 'userId', 'required', 'editor.monograph.fileAuditor.form.userRequired'));
		$this->addCheck(new FormValidator($this, 'files', 'required', 'editor.monograph.fileAuditor.form.fileRequired'));
		$this->addCheck(new FormValidator($this, 'personalMessage', 'required', 'editor.monograph.fileAuditor.form.messageRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Get the monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the workflow stage id.
	 * @return integer
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the signoff's symbolic
	 * @return string
	 */
	function getSymbolic() {
		return $this->_symbolic;
	}

	/**
	 * Get the email key
	 */
	function getEventType() {
		return $this->_eventType;
	}

	//
	// Overridden template methods
	//
	/**
	 * Initialize variables
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$monograph = $this->getMonograph();
		$this->setData('monographId', $monograph->getId());
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'AUDITOR_REQUEST');
		$this->setData('personalMessage', $email->getBody());
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('userId-GroupId', 'files', 'responseDueDate', 'personalMessage'));

		list($userId, $userGroupId) = explode('-', $this->getData('userId-GroupId'));
		$this->setData('userId', $userId);
		$this->setData('userGroupId', $userGroupId);
	}

	/**
	 * Assign user to copyedit the selected files
	 * @see Form::execute()
	 */
	function execute(&$request) {
		// Decode the "files" list
		import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
		$changedFileData = $this->getData('files');
		ListBuilderHandler::unpack($request, $changedFileData);

		// Send the message to the user
		$monograph =& $this->getMonograph();
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'AUDITOR_REQUEST');
		$email->setBody($this->getData('personalMessage'));

		$dateFormatShort = Config::getVar('general', 'date_format_short');
		$weekLaterDate = time() + 604800;
		$weekLaterDate = strftime($dateFormatShort, $weekLaterDate);

		$userDao =& DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
		$user =& $request->getUser();
		$contactSignature = $user->getContactSignature();
		// FIXME: Bug #6199: How to validate user IDs?
		$user =& $userDao->getUser($this->getData('userId'));
		$paramArray = array(
			'auditorName' => $user->getFullName(),
			'editorialContactSignature' => $contactSignature,
			'monographTitle' => $monograph->getSeriesTitle(),
			'weekLaterDate' => $weekLaterDate
		);

		$email->assignParams($paramArray);

		$email->addRecipient($user->getEmail(), $user->getFullName());
		$email->setEventType($this->getEventType());
		$email->send($request);
	}

	/**
	 * Persist a signoff insertion
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertEntry(&$request, $newRowId) {
		// Fetch and validate the file ID
		$fileId = (int) $newRowId['name'];
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
		$monographFile =& $submissionFileDao->getLatestRevision($fileId, null, $monograph->getId());
		assert($monographFile);

		// FIXME: Bug #6199: How to validate user IDs?
		$userId = (int) $this->getData('userId');

		// Fetch and validate user group ID
		$userGroupId = (int) $this->getData('userGroupId');
		$press =& $request->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroup =& $userGroupDao->getById($userGroupId, $press->getId());

		// Build the signoff.
		$monographFileSignoffDao =& DAORegistry::getDAO('MonographFileSignoffDAO');
		$signoff =& $monographFileSignoffDao->build(
			$this->getSymbolic(),
			$monographFile->getFileId(),
			$userId, $userGroup->getId()
		); /* @var $signoff Signoff */

		// Set the date notified
		$signoff->setDateNotified(Core::getCurrentDate());

		// Set the date response due (stored as date underway in signoffs table)
		$dueDateParts = explode('-', $this->getData('responseDueDate'));
		$signoff->setDateUnderway(date('Y-m-d H:i:s', mktime(0, 0, 0, $dueDateParts[0], $dueDateParts[1], $dueDateParts[2])));
		$monographFileSignoffDao->updateObject($signoff);

		// Set the task notification to user.
		$notificationMgr = new NotificationManager();
		$notificationMgr->createNotification(
			$request,
			$userId,
			NOTIFICATION_TYPE_AUDITOR_REQUEST,
			$press->getId(),
			ASSOC_TYPE_SIGNOFF,
			$signoff->getId(),
			NOTIFICATION_LEVEL_TASK
		);

		// Update NOTIFICATION_TYPE_COPYEDIT_SIGNOFF if this is a copyedit symbolic.
		if ($this->getSymbolic() == 'SIGNOFF_COPYEDITING') {
			$notificationMgr->updateCopyeditSignoffNotification($signoff->getUserId(), $request);
		}
	}

	/**
	 * Delete a signoff
	 * FIXME: it was throwing a warning when this was not specified. We just want client side delete.
	 */
	function deleteEntry(&$request, $rowId) {
		return true;
	}
}

?>
