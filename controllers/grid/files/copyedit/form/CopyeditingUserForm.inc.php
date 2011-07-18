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

class CopyeditingUserForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monograph;

	/**
	 * Set the monograph
	 * @param $monograph Monograph
	 */
	function setMonograph(&$monograph) {
		$this->_monograph =& $monograph;
	}

	/**
	 * Get the monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Constructor.
	 */
	function CopyeditingUserForm($monograph) {
		parent::Form('controllers/grid/files/copyedit/addCopyeditingUser.tpl');
		$this->setMonograph($monograph);

		$this->addCheck(new FormValidator($this, 'userId', 'required', 'editor.monograph.copyediting.form.userRequired'));
		$this->addCheck(new FormValidator($this, 'files', 'required', 'editor.monograph.copyediting.form.fileRequired'));
		$this->addCheck(new FormValidator($this, 'personalMessage', 'required', 'editor.monograph.copyediting.form.messageRequired'));
		$this->addCheck(new FormValidatorPost($this));
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
		// Split the selected user value; index 0 is the user id, index 1 is the user groupID


		// Decode the "files" list
		import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
		$changedFileData = $this->getData('files');
		ListBuilderHandler::unpack($request, $changedFileData, null, array(&$this, 'insertSignoff'), null);

		// Send the message to the user
		$monograph =& $this->getMonograph();
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph);
		$email->setBody($this->getData('personalMessage'));

		$userDao =& DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */

		// FIXME: Bug #6199: How to validate user IDs?
		$user =& $userDao->getUser($this->getData('userId'));
		$email->addRecipient($user->getEmail(), $user->getFullName());
		$email->setEventType(MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_AUTHOR);
		$email->send($request);
	}

	/**
	 * Persist a signoff insertion
	 * @see Listbuilder::insertEntry
	 */
	function insertSignoff(&$request, $newRowId) {
		// Fetch and validate the file ID
		$fileId = (int) $newRowId;
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), MONOGRAPH_FILE_COPYEDIT);
		$monographFile = null;
		foreach ($monographFiles as $potentialFile) {
			if ($potentialFile->getFileId() == $fileId) $monographFile =& $potentialFile;
		}

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
			'SIGNOFF_COPYEDITING',
			$monographFile->getId(),
			$userId, $userGroup->getId()
		); /* @var $signoff Signoff */

		// Set the date notified
		$signoff->setDateNotified(Core::getCurrentDate());

		// Set the date response due (stored as date underway in signoffs table)
		$dueDateParts = explode('-', $this->getData('responseDueDate'));
		$signoff->setDateUnderway(date('Y-m-d H:i:s', mktime(0, 0, 0, $dueDateParts[0], $dueDateParts[1], $dueDateParts[2])));
		$monographFileSignoffDao->updateObject($signoff);
	}
}

?>
