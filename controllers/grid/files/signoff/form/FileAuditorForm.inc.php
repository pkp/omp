<?php

/**
 * @file controllers/grid/files/copyedit/form/CopyeditingUserForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
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

	/** @var int */
	var $_fileStage;

	/** @var int */
	var $_stageId;

	/** @var string */
	var $_symbolic;

	/** @var string */
	var $_eventType;

	/** @var int */
	var $_assocId;

	/** @var int */
	var $_publicationFormatId;

	/** @var int */
	var $_signoffId;

	/** @var int */
	var $_fileId;

	/**
	 * Constructor.
	 */
	function FileAuditorForm($monograph, $fileStage, $stageId, $symbolic, $eventType, $assocId = null, $publicationFormatId = null) {
		parent::Form('controllers/grid/files/signoff/form/addAuditor.tpl');
		$this->_monograph = $monograph;
		$this->_fileStage = $fileStage;
		$this->_stageId = $stageId;
		$this->_symbolic = $symbolic;
		$this->_eventType = $eventType;
		$this->_assocId = $assocId;
		$this->_publicationFormatId = $publicationFormatId;

		$this->addCheck(new FormValidator($this, 'userId', 'required', 'editor.monograph.fileAuditor.form.userRequired'));
		$this->addCheck(new FormValidatorListBuilder($this, 'files', 'editor.monograph.fileAuditor.form.fileRequired'));
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
	 * Get the file stage.
	 * @return integer
	 */
	function getFileStage() {
		return $this->_fileStage;
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

	/**
	 * Get the assoc id
	 * @return int
	 */
	function getAssocId() {
		return $this->_assocId;
	}

	/**
	 * Get the publication format id
	 * @return int
	 */
	function getPublicationFormatId() {
		return $this->_publicationFormatId;
	}

	/**
	 * Get the signoff id that this form creates when executed.
	 * @return int
	 */
	function getSignoffId() {
		return $this->_signoffId;
	}

	/**
	 * Get the file id associated with the signoff
	 * created when this form is executed.
	 * @return int
	 */
	function getFileId() {
		return $this->_fileId;
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
		$this->setData('submissionId', $monograph->getId());
		$this->setData('fileStage', $this->getFileStage());
		$this->setData('assocId', $this->getAssocId());
		if ($this->getPublicationFormatId()) {
			$this->setData('publicationFormatId', $this->getPublicationFormatId());
		}
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'AUDITOR_REQUEST');
		$user = $request->getUser();
		// Intentionally omit {$auditorName} for now -- see bug #7090
		$email->assignParams(array(
			'editorialContactSignature' => $user->getContactSignature(),
			'monographTitle' => $monograph->getSeriesTitle(),
			'weekLaterDate' => strftime(
				Config::getVar('general', 'date_format_short'),
				time() + 604800 // 60 * 60 * 24 * 7 seconds
			),
		));

		$press = $request->getPress();
		$this->setData('personalMessage', $email->getBody() . "\n" . $press->getSetting('emailSignature'));
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('userId-GroupId', 'files', 'responseDueDate', 'personalMessage', 'skipEmail'));

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
		ListbuilderHandler::unpack($request, $this->getData('files'));

		// Send the message to the user
		$monograph = $this->getMonograph();
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'AUDITOR_REQUEST', null, null, null, false);
		$email->setBody($this->getData('personalMessage'));

		$userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
		// FIXME: How to validate user IDs?
		$user = $userDao->getById($this->getData('userId'));
		import('lib.pkp.controllers.grid.submissions.SubmissionsListGridCellProvider');
		list($page, $operation) = SubmissionsListGridCellProvider::getPageAndOperationByUserRoles($request, $monograph, $user->getId());

		$dispatcher = $request->getDispatcher();
		$auditUrl = $dispatcher->url($request, ROUTE_PAGE, null, $page, $operation, array('submissionId' => $monograph->getId()));

		// Other parameters assigned above; see bug #7090.
		$email->assignParams(array(
			'auditorName' => $user->getFullName(),
			'auditorUserName' => $user->getUsername(),
			'auditUrl' => $auditUrl,
		));

		$email->addRecipient($user->getEmail(), $user->getFullName());
		$email->setEventType($this->getEventType());
		if (!$this->getData('skipEmail')) {
			$email->send($request);
		}
	}

	/**
	 * Persist a signoff insertion
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertEntry(&$request, $newRowId) {
		// Fetch and validate the file ID
		$fileId = (int) $newRowId['name'];
		$monograph = $this->getMonograph();
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$monographFile = $submissionFileDao->getLatestRevision($fileId, null, $monograph->getId());
		assert($monographFile);

		// FIXME: How to validate user IDs?
		$userId = (int) $this->getData('userId');

		// Fetch and validate user group ID
		$userGroupId = (int) $this->getData('userGroupId');
		$press = $request->getPress();
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$userGroup = $userGroupDao->getById($userGroupId, $press->getId());

		// Build the signoff.
		$monographFileSignoffDao = DAORegistry::getDAO('MonographFileSignoffDAO');
		$signoff = $monographFileSignoffDao->build(
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

		$this->_signoffId = $signoff->getId();
		$this->_fileId = $signoff->getAssocId();

		$notificationMgr = new NotificationManager();
		$notificationMgr->updateNotification(
			$request,
			array(NOTIFICATION_TYPE_AUDITOR_REQUEST),
			array($signoff->getUserId()),
			ASSOC_TYPE_SIGNOFF,
			$signoff->getId()
		);

		// log the add auditor event.
		import('lib.pkp.classes.log.SubmissionFileLog');
		import('lib.pkp.classes.log.SubmissionFileEventLogEntry'); // constants
		$userDao = DAORegistry::getDAO('UserDAO');
		$user = $userDao->getById($userId);
		if (isset($user)) {
			SubmissionFileLog::logEvent($request, $monographFile, SUBMISSION_LOG_FILE_AUDITOR_ASSIGN, 'submission.event.fileAuditorAdded', array('file' => $monographFile->getOriginalFileName(), 'name' => $user->getFullName(), 'username' => $user->getUsername()));
		}

		$notificationMgr->updateNotification(
			$request,
			array(NOTIFICATION_TYPE_SIGNOFF_COPYEDIT, NOTIFICATION_TYPE_SIGNOFF_PROOF),
			array($signoff->getUserId()),
			ASSOC_TYPE_MONOGRAPH,
			$monograph->getId()
		);
	}

	/**
	 * Delete a signoff
	 * Noop: we just want client side delete.
	 */
	function deleteEntry(&$request, $rowId) {
		return true;
	}
}

?>
