<?php

/**
 * @file SubmitHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmitHandler
 * @ingroup pages_author
 *
 * @brief Handle requests for author monograph submission.
 */

// $Id$

import('pages.author.AuthorHandler');

class SubmitHandler extends AuthorHandler {
	/** monograph associated with the request **/
	var $monograph;

	/**
	 * Constructor
	 **/
	function SubmitHandler() {
		parent::AuthorHandler();
	}

	/**
	 * Display author monograph submission.
	 * Displays author index page if a valid step is not specified.
	 * @param $args array optional, if set the first parameter is the step to display
	 */
	function submit($args) {
		$step = isset($args[0]) ? $args[0] : 0;
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, 'author.submit.authorSubmitLoginMessage');

		$monograph =& $this->monograph;
		$this->setupTemplate(true);

		$formClass = "AuthorSubmitStep{$step}Form";
		import("author.form.submit.$formClass");

		$submitForm = new $formClass($monograph);
		if ($submitForm->isLocaleResubmit()) {
			$submitForm->readInputData();
		} else {
			$submitForm->initData();
		}
		$submitForm->display();
	}

	/**
	 * Save a submission step.
	 * @param $args array first parameter is the step being saved
	 */
	function saveSubmit($args) {
		$step = isset($args[0]) ? $args[0] : 0;
		$monographId = Request::getUserVar('monographId');

		$this->validate($monographId);
		$this->setupTemplate(true);
		$monograph =& $this->monograph;

		$formClass = "AuthorSubmitStep{$step}Form";
		import("author.form.submit.$formClass");

		$submitForm = new $formClass($monograph);
		$submitForm->readInputData();

		if (!HookRegistry::call('SubmitHandler::saveSubmit', array($step, &$monograph, &$submitForm))) {

			// Check for any special cases before trying to save
			switch ($step) {
				case 2:
					if (Request::getUserVar('uploadSubmissionFile')) {
						$submitForm->uploadSubmissionFile('submissionFile');
						$editData = true;
					}
					break;

				case 4:
					if (Request::getUserVar('submitUploadSuppFile')) {
						SubmitHandler::submitUploadSuppFile();
						return;
					}
					break;
			}

			if (!isset($editData) && $submitForm->validate()) {
				$monographId = $submitForm->execute();

				if ($step == 5) {
					// Send a notification to associated users
					import('notification.Notification');
					$monographDao =& DAORegistry::getDAO('MonographDAO');
					$monograph =& $monographDao->getMonograph($monographId);
					$roleDao =& DAORegistry::getDAO('RoleDAO');
					$notificationUsers = array();
					$pressManagers = $roleDao->getUsersByRoleId(ROLE_ID_PRESS_MANAGER);
					$allUsers = $pressManagers->toArray();
					$editors = $roleDao->getUsersByRoleId(ROLE_ID_EDITOR);
					array_merge($allUsers, $editors->toArray());
					foreach ($allUsers as $user) {
						$notificationUsers[] = array('id' => $user->getId());
					}
					foreach ($notificationUsers as $userRole) {
						$url = Request::url(null, 'editor', 'submission', $monographId);
						Notification::createNotification($userRole['id'], "notification.type.monographSubmitted",
							$monograph->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED);
					}

					$press =& Request::getPress();
					$templateMgr =& TemplateManager::getManager();
					$templateMgr->assign_by_ref('press', $press);
					// If this is an editor and there is a
					// submission file, monograph can be expedited.
					if (Validation::isEditor($press->getId()) && $monograph->getSubmissionFileId()) {
						$templateMgr->assign('canExpedite', true);
					}
					$templateMgr->assign('monographId', $monographId);
					$templateMgr->assign('helpTopicId','submission.index');
					$templateMgr->display('author/submit/complete.tpl');

				} else {
					Request::redirect(null, null, 'submit', $step+1, array('monographId' => $monographId));
				}

			} else {
				Request::redirect(null, null, 'submit', $sequence->getNextStep(), array('monographId' => $monographId));
			}

		} else {

			$submitForm->display();
		}
	}

	function expediteSubmission() {
		$monographId = (int) Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& Request::getPress();
		$monograph =& $this->monograph;

		// The author must also be an editor to perform this task.
		if (Validation::isEditor($press->getId()) && $monograph->getSubmissionFileId()) {
			import('submission.editor.EditorAction');
			EditorAction::expediteSubmission($monograph);
			Request::redirect(null, 'editor', 'submissionEditing', array($monograph->getMonographId()));
		}

		Request::redirect(null, null, 'track');
	}

	/**
	 * Validation check for submission.
	 * Checks that monograph ID is valid, if specified.
	 * @param $monographId int
	 */
	function validate($monographId = null, $reason = null) {
		parent::validate($reason);
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$user =& Request::getUser();
		$press =& Request::getPress();

		$monograph = null;

		// Check that monograph exists for this press and user and that submission is incomplete
		if (isset($monographId)) {
			$monograph =& $monographDao->getMonograph((int) $monographId);
			if (!$monograph || $monograph->getUserId() !== $user->getId() || $monograph->getPressId() !== $press->getId()) {
				Request::redirect(null, null, 'submit');
			}
		}

		$this->monograph =& $monograph;
		return true;
	}
}
?>
