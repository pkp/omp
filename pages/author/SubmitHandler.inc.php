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
	function submit(&$args, &$request) {
		$step = isset($args[0]) ? $args[0] : 1;
		$monographId = $request->getUserVar('monographId');
		$this->validate($request, $monographId, $step, 'author.submit.authorSubmitLoginMessage');

		$monograph =& $this->monograph;
		$this->setupTemplate(true);

		if ( $step < 4 ) {
			$formClass = "AuthorSubmitStep{$step}Form";
			import("classes.author.form.submit.$formClass");

			$submitForm = new $formClass($monograph);
			if ($submitForm->isLocaleResubmit()) {
				$submitForm->readInputData();
			} else {
				$submitForm->initData();
			}
			$submitForm->display();
		} else {
			$press =& $request->getPress();
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('press', $press);
			// If this is an editor and there is a
			// submission file, monograph can be expedited.
			if (Validation::isEditor($press->getId()) && $monograph->getSubmissionFileId()) {
				$templateMgr->assign('canExpedite', true);
			}

			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign('submitStep', $step);
			$templateMgr->assign('submissionProgress', $this->monograph->getSubmissionProgress());

			$templateMgr->assign('helpTopicId','submission.index');
			$templateMgr->display('author/submit/complete.tpl');
		}
	}

	/**
	 * Save a submission step.
	 * @param $args array first parameter is the step being saved
	 */
	function saveSubmit(&$args, &$request) {
		$step = isset($args[0]) ? $args[0] : 1;
		$monographId = $request->getUserVar('monographId');

		$this->validate($request, $monographId, $step);
		$this->setupTemplate(true);
		$monograph =& $this->monograph;

		$formClass = "AuthorSubmitStep{$step}Form";
		import("classes.author.form.submit.$formClass");

		$submitForm = new $formClass($monograph);
		$submitForm->readInputData();

		if (!HookRegistry::call('SubmitHandler::saveSubmit', array($step, &$monograph, &$submitForm))) {
			if ($submitForm->validate()) {
				$monographId = $submitForm->execute();

				if ($step == 3) {
					// Send a notification to associated users
					import('lib.pkp.classes.notification.NotificationManager');
					$notificationManager = new NotificationManager();
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
						$url = $request->url(null, 'editor', 'submission', $monographId);
						$notificationManager->createNotification(
							$userRole['id'], 'notification.type.monographSubmitted',
							$monograph->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED
						);
					}
				}
				$request->redirect(null, null, 'submit', $step+1, array('monographId' => $monographId));
			} else {
				$submitForm->display();
			}
		}
	}

	function expediteSubmission(&$args, &$request) {
		$monographId = (int) $request->getUserVar('monographId');
		$this->validate($request, $monographId, $step);
		$press =& $request->getPress();
		$monograph =& $this->monograph;

		// The author must also be an editor to perform this task.
		if (Validation::isEditor($press->getId()) && $monograph->getSubmissionFileId()) {
			import('classes.submission.editor.EditorAction');
			EditorAction::expediteSubmission($monograph);
			$request->redirect(null, 'editor', 'submissionEditing', array($monograph->getId()));
		}

		$request->redirect(null, null, 'track');
	}

	/**
	 * Validation check for submission.
	 * Checks that monograph ID is valid, if specified.
	 * @param $monographId int
	 */
	function validate($request, $monographId = null, $step = false, $reason = null) {
		parent::validate($reason);
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$user =& $request->getUser();
		$press =& $request->getPress();

		if ($step !== false && ($step < 1 || $step > 4 || (!isset($monographId) && $step != 1))) {
			$request->redirect(null, null, 'submit', array(1));
		}

		$monograph = null;

		// Check that monograph exists for this press and user and that submission is incomplete
		if (isset($monographId)) {
			$monograph =& $monographDao->getMonograph((int) $monographId);
			if (!$monograph || $monograph->getUserId() !== $user->getId() || $monograph->getPressId() !== $press->getId()) {
				$request->redirect(null, null, 'submit');
			}
			// if the submission is complete, redirect to the submission complete tab
			// submission progress == 0 means complete
			if ( $step !== false && $step != 4 && $monograph->getSubmissionProgress() == 0 ) {
				$request->redirect(null, null, 'submit', 4, array('monographId' => $monographId));
			}
			// do not go beyond the current submission progress
			if ($step !== false && $monograph->getSubmissionProgress() != 0 && $step > $monograph->getSubmissionProgress()) {
				$request->redirect(null, null, 'submit', $monograph->getSubmissionProgress(), array('monographId' => $monographId));
			}
		}
		$this->monograph =& $monograph;
		return true;
	}
}
?>
