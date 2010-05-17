<?php

/**
 * @file classes/informationCenter/form/InformationCenterNotifyForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationCenterNotifyForm
 * @ingroup informationCenter_form
 *
 * @brief Form to notify a user regarding a file
 */

// $Id$


import('lib.pkp.classes.form.Form');

class InformationCenterNotifyForm extends Form {
	/** @var int The file this form is for */
	var $fileId;

	/**
	 * Constructor.
	 */
	function InformationCenterNotifyForm($fileId) {
		parent::Form('controllers/informationCenter/notify.tpl');
		$this->fileId = $fileId;
		
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Display the form.
	 */
	function display(&$request, $fetch = true) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('fileId', $this->fileId);

		return parent::display($request, $fetch);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'note'
		));

	}

	/**
	 * Register a new user.
	 * @return userId int
	 */
	function execute() {
		//FIXME: SEND NOTIFICATION
	}
}

?>
