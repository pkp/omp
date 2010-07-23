<?php

/**
 * @file ReviewHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for submission tracking.
 */

// $Id$

import('classes.handler.Handler');
import('lib.pkp.classes.core.JSON');
import('classes.submission.common.Action');

class ReviewHandler extends Handler {
	/**
	 * Constructor
	 **/
	function ReviewHandler() {
		parent::Handler();
	}

	function review(&$args, &$request) {
		$this->setupTemplate();
		$monographId = array_shift($args);
		$selectedRound = array_shift($args);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);

		$templateMgr =& TemplateManager::getManager();
		// Get the review round currently being looked at
		$currentReviewType = $monograph->getCurrentReviewType();
		$currentRound = $monograph->getCurrentRound();
		if($selectedRound <= $currentRound) {
			$selectedRound = $currentRound; // Make sure round is not higher than the monograph's latest round
		}

		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRound =& $reviewRoundDao->build($monographId, $currentReviewType, $selectedRound);

		// Set allRounds to an array of all values > 0 and less than currentRound--This will determine the tabs to show
		$allRounds = array();
		for ($i = 1; $i <= $currentRound; $i++) $allRounds[] = $i;
		$templateMgr->assign('rounds', $allRounds);

		// Grid actions
		$actionArgs = array('monographId' => $monographId,
							'reviewType' => $currentReviewType,
							'round' => $currentRound);

		// import action class
		import('linkAction.LinkAction');
		$dispatcher =& $this->getDispatcher();

		$actionArgs['decision'] = SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS;
		$requestRevisionsAction =& new LinkAction(
			'requestRevisions',
			LINK_ACTION_MODE_MODAL,
			null,
			$dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.editorDecision.EditorDecisionHandler', 'sendReviews', null, $actionArgs),
			'editor.monograph.decision.requestRevisions'
		);

		$actionArgs['decision'] = SUBMISSION_EDITOR_DECISION_RESUBMIT;
		$resubmitAction =& new LinkAction(
			'resubmit',
			LINK_ACTION_MODE_MODAL,
			null,
			$dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.editorDecision.EditorDecisionHandler', 'sendReviews', null, $actionArgs),
			'editor.monograph.decision.resubmit'
		);

		$actionArgs['decision'] = SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW;
		$externalReviewAction =& new LinkAction(
			'externalReview',
			LINK_ACTION_MODE_MODAL,
			null,
			$dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.editorDecision.EditorDecisionHandler', 'promote', null, $actionArgs),
			'editor.monograph.decision.externalReview'
		);

		$actionArgs['decision'] = SUBMISSION_EDITOR_DECISION_ACCEPT;
		$acceptAction =& new LinkAction(
			'accept',
			LINK_ACTION_MODE_MODAL,
			null,
			$dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.editorDecision.EditorDecisionHandler', 'promote', null, $actionArgs),
			'editor.monograph.decision.accept'
		);

		$actionArgs['decision'] = SUBMISSION_EDITOR_DECISION_DECLINE;
		$declineAction =& new LinkAction(
			'decline',
			LINK_ACTION_MODE_MODAL,
			null,
			$dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.editorDecision.EditorDecisionHandler', 'sendReviews', null, $actionArgs),
			'editor.monograph.decision.decline'
		);

		$editorActions = array($requestRevisionsAction,
								$resubmitAction,
								$externalReviewAction,
								$acceptAction,
								$declineAction
								);

		$templateMgr->assign('editorActions', $editorActions);
		$templateMgr->assign('currentReviewType', $currentReviewType);
		$templateMgr->assign('currentRound', $currentRound);
		$templateMgr->assign('selectedRound', $selectedRound);
		$templateMgr->assign('roundStatus', $reviewRound->getStatusKey());
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->display('seriesEditor/showReviewers.tpl');
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate() {
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR));
		parent::setupTemplate();
	}
}
?>
