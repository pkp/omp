<?php

/**
 * @file SubmitHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmitHandler
 * @ingroup pages_submission
 *
 * @brief Handle requests for submission monograph submission.
 */

// $Id$

import('pages.submission.SubmissionHandler');

class SubmitHandler extends SubmissionHandler {
	/** monograph associated with the request **/
	var $monograph;

	/**
	 * Constructor
	 **/
	function SubmitHandler() {
		parent::SubmissionHandler();
		$this->addRoleAssignment(ROLE_ID_AUTHOR,
				$authorOperations = array('wizard', 'saveStep'));
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array_merge($authorOperations, array('expediteSubmission')));

	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionWizardStepsPolicy');
		$this->addPolicy(new OmpSubmissionWizardStepsPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Display submission monograph submission.
	 * Displays submission index page if a valid step is not specified.
	 * @param $args array optional, if set the first parameter is the step to display
	 */
	function wizard(&$args, &$request) {
		$step = isset($args[0]) ? (int) $args[0] : 1;
		// FIXME: bug #5626. should get press from AuthorizedContextObject
		// $press =& $this->getAuthorizedContextObject(ASSOC_TYPE_PRESS);
		$press =& $request->getContext();
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$this->setupTemplate(true);

		if ( $step < 4 ) {
			$formClass = "SubmissionSubmitStep{$step}Form";
			import("classes.submission.form.submit.$formClass");

			$submitForm = new $formClass($monograph);
			if ($submitForm->isLocaleResubmit()) {
				$submitForm->readInputData();
			} else {
				$submitForm->initData();
			}
			$submitForm->display();
		} else {

			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('press', $press);
			// If this is an editor and there is a
			// submission file, monograph can be expedited.
			if (Validation::isEditor($press->getId()) && $monograph->getSubmissionFileId()) {
				$templateMgr->assign('canExpedite', true);
			}

			$templateMgr->assign('monographId', $monograph->getId());
			$templateMgr->assign('submitStep', $step);
			$templateMgr->assign('submissionProgress', $monograph->getSubmissionProgress());

			$templateMgr->assign('helpTopicId','submission.index');
			$templateMgr->display('submission/form/submit/complete.tpl');
		}
	}

	/**
	 * Save a submission step.
	 * @param $args array first parameter is the step being saved
	 */
	function saveStep(&$args, &$request) {
		$step = isset($args[0]) ? (int) $args[0] : 1;
		// FIXME: bug #5626. should get press from AuthorizedContextObject
		// $press =& $this->getAuthorizedContextObject(ASSOC_TYPE_PRESS);
		$press =& $request->getContext();
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$this->setupTemplate(true);

		$formClass = "SubmissionSubmitStep{$step}Form";
		import("classes.submission.form.submit.$formClass");

		$submitForm = new $formClass($monograph);
		$submitForm->readInputData();

		if (!HookRegistry::call('SubmitHandler::saveSubmit', array($step, &$monograph, &$submitForm))) {
			if ($submitForm->validate()) {
				$monographId = $submitForm->execute();

				if ($step == 3) {
					// Send a notification to associated users
					import('lib.pkp.classes.notification.NotificationManager');
					$notificationManager = new NotificationManager();
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
				$request->redirect(null, null, 'wizard', $step+1, array('monographId' => $monographId));
			} else {
				$submitForm->display();
			}
		}
	}

	function expediteSubmission(&$args, &$request) {
		// FIXME: bug #5626. should get press from AuthorizedContextObject
		// $press =& $this->getAuthorizedContextObject(ASSOC_TYPE_PRESS);
		$press =& $request->getContext();
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// The author must also be an editor to perform this task.
		if (Validation::isEditor($press->getId()) && $monograph->getSubmissionFileId()) {
			import('classes.submission.editor.EditorAction');
			EditorAction::expediteSubmission($monograph);
			$request->redirect(null, 'editor', 'submissionEditing', array($monograph->getId()));
		}

		$request->redirect(null, null, 'track');
	}
}
?>
