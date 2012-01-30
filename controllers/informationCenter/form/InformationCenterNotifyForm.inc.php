<?php

/**
 * @file controllers/informationCenter/form/InformationCenterNotifyForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationCenterNotifyForm
 * @ingroup informationCenter_form
 *
 * @brief Form to notify a user regarding a file
 */



import('lib.pkp.classes.form.Form');

class InformationCenterNotifyForm extends Form {
	/** @var int The file/monograph ID this form is for */
	var $itemId;

	/** @var int The type of item the form is for (used to determine which email template to use) */
	var $itemType;

	/**
	 * Constructor.
	 */
	function InformationCenterNotifyForm($itemId, $itemType) {
		parent::Form('controllers/informationCenter/notify.tpl');
		$this->itemId = $itemId;
		$this->itemType = $itemType;

		$this->addCheck(new FormValidatorListbuilder($this, 'users', 'informationCenter.notify.warning'));
		$this->addCheck(new FormValidator($this, 'message', 'required', 'informationCenter.notify.warning'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		if($this->itemType == ASSOC_TYPE_MONOGRAPH) {
			$monographId = $this->itemId;
		} else {
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$monographFile =& $submissionFileDao->getLatestRevision($this->itemId);
			$monographId = $monographFile->getMonographId();
		}

		$templateMgr->assign_by_ref('monographId', $monographId);
		$templateMgr->assign_by_ref('itemId', $this->itemId);

		// All stages can choose the default template
		$templateKeys = array('NOTIFICATION_CENTER_DEFAULT');

		// template keys indexed by stageId
		$stageTemplates = array(
			WORKFLOW_STAGE_ID_SUBMISSION => array(),
			WORKFLOW_STAGE_ID_INTERNAL_REVIEW => array(),
			WORKFLOW_STAGE_ID_EXTERNAL_REVIEW => array(),
			WORKFLOW_STAGE_ID_EDITING => array(),
			WORKFLOW_STAGE_ID_PRODUCTION => array()
		);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getById($monographId);
		$currentStageId = $monograph->getStageId();

		$templateKeys = array_merge($templateKeys, $stageTemplates[$currentStageId]);

		import('classes.mail.MonographMailTemplate');

		foreach ($templateKeys as $templateKey) {
			$template = new MonographMailTemplate($monograph, $templateKey);
			$templates[$templateKey] = $template->getSubject();
			unset($templateKey);
		}

		unset($templateKeys);
		$templateMgr->assign_by_ref('templates', $templates);

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('message', 'users', 'template'));
		import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
		$userData = $this->getData('users');
		ListbuilderHandler::unpack($request, $userData);
	}

	/**
	 * Sends a a notification.
	 * @see Form::execute()
	 */
	function execute(&$request) {
		return parent::execute($request);
	}

	/**
	 * Prepare an email for each user and send
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertEntry(&$request, $newRowId) {

		$userDao =& DAORegistry::getDAO('UserDAO');
		$application =& Application::getApplication();
		$request =& $application->getRequest(); // need to do this because the method version is null.
		$fromUser =& $request->getUser();

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		import('classes.mail.MonographMailTemplate');

		if($this->itemType == ASSOC_TYPE_MONOGRAPH) {
			$monographId = $this->itemId;
		} else {
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$monographFile =& $submissionFileDao->getLatestRevision($this->itemId);
			$monographId = $monographFile->getMonographId();
		}

		$template = $this->getData('template');

		$email = new MonographMailTemplate($monographDao->getById($monographId), $template);
		$email->setFrom($fromUser->getEmail(), $fromUser->getFullName());

		foreach ($newRowId as $id) {
			$user = $userDao->getUser($id);
			$email->addRecipient($user->getEmail(), $user->getFullName());
			$email->setBody($this->getData('message'));
			$email->sendWithParams(array());
		}
	}

	/**
	 * Delete a signoff
	 * (It was throwing a warning when this was not specified. We just want
	 * client side delete.)
	 */
	function deleteEntry(&$request, $rowId) {
		return true;
	}
}

?>
