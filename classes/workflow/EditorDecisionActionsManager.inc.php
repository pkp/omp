<?php

/**
 * @file classes/workflow/EditorDecisionActionsManager.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorDecisionActionsManager
 * @ingroup classes_workflow
 *
 * @brief Wrapper class for create and assign editor decisions actions to template manager.
 */

// Submission stage decision actions.
define('SUBMISSION_EDITOR_DECISION_INITIATE_REVIEW', 1);

// Review stage decisions actions.
define('SUBMISSION_EDITOR_DECISION_ACCEPT', 2);
define('SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW', 3);
define('SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS', 4);
define('SUBMISSION_EDITOR_DECISION_RESUBMIT', 5);
define('SUBMISSION_EDITOR_DECISION_DECLINE', 6);

// Copyediting stage decision actions.
define('SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION', 7);

class EditorDecisionActionsManager {

	/**
	* Create actions for editor decisions and assign them to the template.
	* @param $request Request
	* @param $decisionsFunctionName string the name of the class method
	*  that will return the decision configuration.
	* @param $actionArgs array action arguments
	*/
	function assignDecisionsToTemplate(&$request, $decisionsFunctionName, $actionArgs) {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR));

		// Retrieve the editor decisions.
		$decisions = EditorDecisionActionsManager::$decisionsFunctionName();

		// Iterate through the editor decisions and create a link action for each decision.
		$dispatcher =& $request->getDispatcher();
		import('classes.linkAction.request.AjaxModal');
		foreach($decisions as $decision => $action) {
			$actionArgs['decision'] = $decision;
			$editorActions[] = new LinkAction(
				$action['name'],
				new AjaxModal(
					$dispatcher->url(
						$request, ROUTE_COMPONENT, null,
						'modals.editorDecision.EditorDecisionHandler',
						$action['operation'], null, $actionArgs
					),
					__($action['title'])
				),
				__($action['title']),
				(isset($action['image']) ? $action['image'] : null)
			);
		}
		// Assign the actions to the template.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('editorActions', $editorActions);
	}

	/**
	 * Get decision actions labels.
	 * @param $decisions
	 * @return array
	 */
	function getActionLabels($decisions) {
		$allDecisionsData = array_merge(
				EditorDecisionActionsManager::_submissionStageDecisions(),
				EditorDecisionActionsManager::_internalReviewStageDecisions(),
				EditorDecisionActionsManager::_externalReviewStageDecisions(),
				EditorDecisionActionsManager::_copyeditingStageDecisions()
		);

		$actionLabels = array();
		foreach($decisions as $decision) {
			if ($allDecisionsData[$decision]['title']) {
				$actionLabels[$decision] = $allDecisionsData[$decision]['title'];
			} else {
				assert(false);
			}
		}

		return $actionLabels;
	}


	//
	// Private helper methods.
	//
	/**
	 * Define and return editor decisions for the submission stage.
	 * @return array
	 */
	function _submissionStageDecisions() {
		static $decisions = array(
		SUBMISSION_EDITOR_DECISION_ACCEPT => array(
					'name' => 'accept',
					'operation' => 'promote',
					'title' => 'editor.monograph.decision.accept',
					'image' => 'promote'
		),
		SUBMISSION_EDITOR_DECISION_DECLINE => array(
					'name' => 'decline',
					'operation' => 'sendReviews',
					'title' => 'editor.monograph.decision.decline',
					'image' => 'decline'
		),
		SUBMISSION_EDITOR_DECISION_INITIATE_REVIEW => array(
					'name' => 'initiateReview',
					'operation' => 'initiateReview',
					'title' => 'editor.monograph.initiateReview',
					'image' => 'advance'
		)
		);

		return $decisions;
	}

	/**
	 * Define and return editor decisions for the review stage.
	 * @return array
	 */
	function _internalReviewStageDecisions() {
		static $decisions = array(
		SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => array(
					'operation' => 'sendReviews',
					'name' => 'requestRevisions',
					'title' => 'editor.monograph.decision.requestRevisions',
					'image' => 'revisions'
		),
		SUBMISSION_EDITOR_DECISION_RESUBMIT => array(
					'operation' => 'sendReviews',
					'name' => 'resubmit',
					'title' => 'editor.monograph.decision.resubmit',
					'image' => 'resubmit'
		),
		SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => array(
					'operation' => 'promoteInReview',
					'name' => 'externalReview',
					'title' => 'editor.monograph.decision.externalReview',
					'image' => 'advance'
		),
		SUBMISSION_EDITOR_DECISION_ACCEPT => array(
					'operation' => 'promoteInReview',
					'name' => 'accept',
					'title' => 'editor.monograph.decision.accept',
					'image' => 'promote'
		),
		SUBMISSION_EDITOR_DECISION_DECLINE => array(
					'operation' => 'sendReviews',
					'name' => 'decline',
					'title' => 'editor.monograph.decision.decline',
					'image' => 'decline'
		)
		);

		return $decisions;
	}

	/**
	 * Define and return editor decisions for the review stage.
	 * @return array
	 */
	function _externalReviewStageDecisions() {
		static $decisions = array(
		SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => array(
					'operation' => 'sendReviews',
					'name' => 'requestRevisions',
					'title' => 'editor.monograph.decision.requestRevisions'
		),
		SUBMISSION_EDITOR_DECISION_RESUBMIT => array(
					'operation' => 'sendReviews',
					'name' => 'resubmit',
					'title' => 'editor.monograph.decision.resubmit'
		),
		SUBMISSION_EDITOR_DECISION_ACCEPT => array(
					'operation' => 'promoteInReview',
					'name' => 'accept',
					'title' => 'editor.monograph.decision.accept',
					'image' => 'approve'
		),
		SUBMISSION_EDITOR_DECISION_DECLINE => array(
					'operation' => 'sendReviews',
					'name' => 'decline',
					'title' => 'editor.monograph.decision.decline',
					'image' => 'delete'
		)
		);

		return $decisions;
	}


	/**
	 * Define and return editor decisions for the copyediting stage.
	 * @return array
	 */
	function _copyeditingStageDecisions() {
		static $decisions = array(
		SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION => array(
					'operation' => 'promote',
					'name' => 'sendToProduction',
					'title' => 'editor.monograph.decision.sendToProduction',
					'image' => 'approve'
		)
		);

		return $decisions;
	}
}

?>
