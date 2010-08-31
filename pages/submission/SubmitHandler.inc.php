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

import('classes.handler.Handler');

class SubmitHandler extends Handler {
	/**
	 * Constructor
	 */
	function SubmitHandler() {
		parent::Handler();
		$this->addRoleAssignment(ROLE_ID_AUTHOR, $authorOperations = array('wizard', 'saveStep'));
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array_merge($authorOperations, array('expediteSubmission')));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		// The policy for the submission handler depends on the
		// step currently requested.
		$step = isset($args[0]) ? (int) $args[0] : 1;
		if ($step<1 || $step>4) return false;

		// Do we have a monograph present in the request?
		$monographId = (int)$request->getUserVar('monographId');

		// Are we in step one without a monograph present?
		if ($step === 1 && $monographId === 0) {
			// Authorize submission creation.
			import('classes.security.authorization.OmpPressAccessPolicy');
			$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		} else {
			// Authorize editing of incomplete submissions.
			import('classes.security.authorization.OmpSubmissionAccessPolicy');
			$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments, 'monographId'));
		}

		// Do policy checking.
		if (!parent::authorize($request, $args, $roleAssignments)) return false;

		// Execute additional checking of the step.
		// NB: Move this to its own policy for reuse when required in other places.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Permit if there is no monograph set, but request is for initial step.
		if (!is_a($monograph, 'Monograph') && $step == 1) return true;

		// In all other cases we expect an authorized monograph due to the
		// submission access policy above.
		assert(is_a($monograph, 'Monograph'));

		// FIXME: What happens when returning to a prior step? See #5813.
		// FIXME: What happens when returning to an incomplete submission? See #5752.
		// Deny if submission is complete (==0 means complete) and at
		// any step other than the "complete" step (=4)
		if ($monograph->getSubmissionProgress() == 0 && $step != 4 ) return false;

		// Deny if trying to access a step greater than the current progress
		if ($monograph->getSubmissionProgress() != 0 && $step > $monograph->getSubmissionProgress()) return false;

		return true;
	}


	//
	// Public Handler Methods
	//
	/**
	 * Display submission monograph submission.
	 * Displays submission index page if a valid step is not specified.
	 * @param $args array
	 * @param $request Request
	 */
	function wizard(&$args, &$request) {
		$step = isset($args[0]) ? (int) $args[0] : 1;

		$router =& $request->getRouter();
		$press =& $router->getContext($request);
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
		} elseif($step == 4) {
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
	 * @param $request Request
	 */
	function saveStep(&$args, &$request) {
		$step = isset($args[0]) ? (int) $args[0] : 1;

		$router =& $request->getRouter();
		$press =& $router->getContext($request);
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


	/**
	 * FIXME: missing method doc, calls inexistent
	 * method EditorAction::expediteSubmission(), not part
	 * of the authorized methods, see #5824.
	 * @param $args array
	 * @param $request Request
	 */
	function expediteSubmission(&$args, &$request) {
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// The author must also be an editor to perform this task.
		if (Validation::isEditor($press->getId()) && $monograph->getSubmissionFileId()) {
			import('classes.submission.editor.EditorAction');
			// FIXME: EditorAction::expediteSubmission() must be implemented, see #5824.
			EditorAction::expediteSubmission($monograph);
			$request->redirect(null, 'editor', 'submissionEditing', array($monograph->getId()));
		}

		$request->redirect(null, null, 'track');
	}


	//
	// Protected helper methods
	//
	/**
	 * Setup common template variables.
	 * FIXME: Put this method in a common base class with the SubmissionHandler? Can we clean it up? See #5844.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 * @param $monographId integer
	 * @param $parentPage string name of submission component
	 */
	function setupTemplate($subclass = false, $monographId = 0, $parentPage = null) {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION));
		$templateMgr =& TemplateManager::getManager();

		$pageHierarchy = $subclass ? array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'author'), 'user.role.author'), array(Request::url(null, 'author'), 'manuscript.submissions'))
			: array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'author'), 'user.role.author'));

		import('classes.submission.seriesEditor.SeriesEditorAction');
		$submissionCrumb = SeriesEditorAction::submissionBreadcrumb($monographId, $parentPage, 'author');
		if (isset($submissionCrumb)) {
			$pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
		}

		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}
}
?>
