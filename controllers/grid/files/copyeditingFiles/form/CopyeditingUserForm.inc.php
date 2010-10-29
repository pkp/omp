<?php

/**
 * @file controllers/grid/files/copyeditingFiles/form/CopyeditingUserForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingUserForm
 * @ingroup controllers_grid_files_copyeditingFiles
 *
 * @brief Form to add files to the final draft files grid
 */

import('lib.pkp.classes.form.Form');

class CopyeditingUserForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function CopyeditingUserForm($monographId) {
		parent::Form('controllers/grid/files/copyeditingFiles/addCopyeditingUser.tpl');
		$this->_monographId = (int) $monographId;

		$this->addCheck(new FormValidator($this, 'userId', 'required', 'editor.monograph.copyediting.form.userRequired'));
		$this->addCheck(new FormValidator($this, 'selected-listbuilder-files-copyeditingfileslistbuilder', 'required', 'editor.monograph.copyediting.form.fileRequired'));
		$this->addCheck(new FormValidator($this, 'personalMessage', 'required', 'editor.monograph.copyediting.form.messageRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Template methods from Form
	//
	/**
	 * Initialize variables
	 */
	function initData($args, &$request) {
		$this->setData('monographId', $this->_monographId);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('userId', 'selected-listbuilder-files-copyeditingfileslistbuilder', 'responseDueDate', 'personalMessage'));
	}

	/**
	 * Assign user to copyedit the selected files
	 */
	function execute() {
		$monographId = $this->_monographId;
		$userId = $this->getData('userId');

		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
		$userDao =& DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */

		$monograph =& $monographDao->getMonograph($monographId);
		if($this->getData('selected-listbuilder-files-copyeditingfileslistbuilder')) {
			$selectedFiles = $this->getData('selected-listbuilder-files-copyeditingfileslistbuilder');
		} else {
			$selectedFiles = array();
		}

		// Build copyediting signoff for each file
		foreach ($selectedFiles as $selectedFileId) {
			$signoff =& $signoffDao->build('SIGNOFF_COPYEDITING', ASSOC_TYPE_MONOGRAPH_FILE, $selectedFileId, $userId); /* @var $signoff Signoff */

			// Set the date notified
			$signoff->setDateNotified(Core::getCurrentDate());
			// Set the date response due (stored as date underway in signoffs table)
			$dueDateParts = explode('-', $this->getData('responseDueDate'));
			$signoff->setDateUnderway(date('Y-m-d H:i:s', mktime(0, 0, 0, $dueDateParts[0], $dueDateParts[1], $dueDateParts[2])));
			$signoffDao->updateObject($signoff);
		}

		// Send the message to the user
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph);
		$email->setBody($this->getData('personalMessage'));
		$user =& $userDao->getUser($userId);
		$email->addRecipient($user->getEmail(), $user->getFullName());
		$email->setAssoc(MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_AUTHOR, MONOGRAPH_EMAIL_TYPE_COPYEDIT, MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_AUTHOR);
		$email->send();
	}
}

?>
