<?php

/**
 * @file controllers/informationCenter/form/InformationCenterNotifyForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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

		// FIXME: create locale keys for these messages
		$this->addCheck(new FormValidator($this, 'users', 'required', 'You must select at least one user.'));
		$this->addCheck(new FormValidator($this, 'message', 'required', 'You must enter a message.'));
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

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('message', 'users'));

	}

	/**
	 * Register a new user.
	 * @return userId int
	 */
	function execute(&$request) {
		import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
		$userData = $this->getData('users');
		ListBuilderHandler::unpack($request, $userData);
	}

	/**
	 * Prepare an email for each user and send
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertEntry(&$request, $newRowId) {
		$user =& $request->getUser();
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();

		// FIXME:? Are these the right params?
		$paramArray = array('sender' => $user->getFullName(),
				'monographDetailsUrl' => $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'submission', $this->itemId),
				'message' => $this->getData('message')
			);

		switch ($this->itemType) {
			case ASSOC_TYPE_MONOGRAPH_FILE:
				// FIXME: template to come from selection
				$emailTemplate = 'NOTIFY_FILE';

				$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
				$monographFile =& $submissionFileDao->getLatestRevision($this->itemId);
				$monographId = $monographFile->getMonographId();
				$paramArray['fileName'] = $monographFile->getLocalizedName();
				break;
			default:
				// FIXME: template to come from selection
				$emailTemplate = 'NOTIFY_SUBMISSION';
				$monographId = $this->itemId;
				break;
		}

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monographDao->getMonograph($monographId), $emailTemplate);
		$email->assignParams($paramArray);

		$userDao =& DAORegistry::getDAO('UserDAO');
		$recepientUser =& $userDao->getUser($newRowId);
		$email->addRecipient($recepientUser->getEmail(), $recepientUser->getFullName());

		$email->send($request);
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
