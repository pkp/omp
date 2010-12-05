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

		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidator($this, 'message', 'required', 'common.required'));
		$this->addCheck(new FormValidator($this, 'message', 'required', 'common.required'));
		$this->addCheck(new FormValidatorArray($this, 'selected-listbuilder-users-notifyuserslistbuilder', 'required', 'common.required'));
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
		$this->readUserVars(array(
			'message', 'selected-listbuilder-users-notifyuserslistbuilder'
		));

	}

	/**
	 * Register a new user.
	 * @return userId int
	 */
	function execute(&$request) {
		$user =& $request->getUser();
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();

		$paramArray = array('sender' => $user->getFullName(),
				'monographDetailsUrl' => $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'submission', $this->itemId),
				'message' => $this->getData('message')
			);

		switch ($this->itemType) {
			case ASSOC_TYPE_MONOGRAPH_FILE:
				$emailTemplate = 'NOTIFY_FILE';

				$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
				$monographFile =& $submissionFileDao->getLatestRevision($this->itemId);
				$monographId = $monographFile->getMonographId();
				$paramArray['fileName'] = $monographFile->getLocalizedName();
				break;
			default:
				$emailTemplate = 'NOTIFY_SUBMISSION';
				$monographId = $this->itemId;
				break;
		}

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monographDao->getMonograph($monographId), $emailTemplate);
		$email->assignParams($paramArray);

		$userDao =& DAORegistry::getDAO('UserDAO');
		foreach($this->getData('selected-listbuilder-users-notifyuserslistbuilder') as $recipientId) {
			$user =& $userDao->getUser($recipientId);
			$email->addRecipient($user->getEmail(), $user->getFullName());
		}

		$email->send();
	}
}

?>
