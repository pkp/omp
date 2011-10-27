<?php

/**
 * @file pages/submission/SubmissionHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHandler
 * @ingroup pages_submission
 *
 * @brief Handle requests for monograph submission.
 */


import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class SubmissionHandler extends Handler {
	/**
	 * Constructor
	 */
	function SubmissionHandler() {
		parent::Handler();
		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('index', 'wizard', 'saveStep'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
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
	 * Redirect to the new submission wizard by default.
	 * @param $args array
	 * @param $request Request
	 */
	function index($args, &$request) {
		$request->redirect(null, null, 'wizard');
	}

	/**
	 * Display submission monograph submission.
	 * Displays submission index page if a valid step is not specified.
	 * @param $args array
	 * @param $request Request
	 */
	function wizard($args, &$request) {
		$step = isset($args[0]) ? (int) $args[0] : 1;

		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$this->setupTemplate($request);

		if ( $step < 4 ) {
			$formClass = "SubmissionSubmitStep{$step}Form";
			import("classes.submission.form.$formClass");

			$submitForm = new $formClass($press, $monograph);
			if ($submitForm->isLocaleResubmit()) {
				$submitForm->readInputData();
			} else {
				$submitForm->initData();
			}
			$submitForm->display($request);
		} elseif($step == 4) {
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('press', $press);

			$templateMgr->assign('monographId', $monograph->getId());
			$templateMgr->assign('submitStep', $step);
			$templateMgr->assign('submissionProgress', $monograph->getSubmissionProgress());

			$templateMgr->assign('helpTopicId','submission.index');
			$templateMgr->display('submission/form/complete.tpl');
		}
	}

	/**
	 * Save a submission step.
	 * @param $args array first parameter is the step being saved
	 * @param $request Request
	 */
	function saveStep($args, &$request) {
		$step = isset($args[0]) ? (int) $args[0] : 1;

		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$this->setupTemplate($request);

		$formClass = "SubmissionSubmitStep{$step}Form";
		import("classes.submission.form.$formClass");

		$submitForm = new $formClass($press, $monograph);
		$submitForm->readInputData();

		if (!HookRegistry::call('SubmissionHandler::saveSubmit', array($step, &$monograph, &$submitForm))) {
			if (!$submitForm->validate()) {
				$this->setupTemplate($request);
				$submitForm->display($request);
			} else {
				$monographId = $submitForm->execute($args, $request);
				return $request->redirectUrlJson($router->url($request, null, null, 'wizard', $step+1, array('monographId' => $monographId)));
			}
		}
	}


	//
	// Protected helper methods
	//
	/**
	 * Setup common template variables.
	 * @param $request Request
	 */
	function setupTemplate(&$request) {
		parent::setupTemplate();
		AppLocale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION));

		$router =& $request->getRouter();
		$pageHierarchy = array(
			array($router->url($request, null, 'user'), 'navigation.user'),
			array($router->url($request, null, 'submission'), 'manuscript.submissions')
		);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}
}
?>
