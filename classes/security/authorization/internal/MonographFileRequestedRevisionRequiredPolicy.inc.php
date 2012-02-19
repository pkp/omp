<?php
/**
 * @file classes/security/authorization/internal/MonographFileRequestedRevisionRequiredPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileRequestedRevisionRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Monograph file policy to ensure we have a viewable file that is part of
 * a review round with the requested revision decision.
 *
 */

import('classes.security.authorization.internal.MonographFileBaseAccessPolicy');

class MonographFileRequestedRevisionRequiredPolicy extends MonographFileBaseAccessPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographFileRequestedRevisionRequiredPolicy(&$request, $fileIdAndRevision = null) {
		parent::MonographFileBaseAccessPolicy($request, $fileIdAndRevision);
	}


	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		$request =& $this->getRequest();
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */

		// Get the monograph file.
		$monographFile =& $this->getMonographFile($request);
		if (!is_a($monographFile, 'MonographFile')) return AUTHORIZATION_DENY;

		// Make sure the file belongs to the monograph in request.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;
		if ($monograph->getId() != $monographFile->getSubmissionId()) return AUTHORIZATION_DENY;

		// Make sure the file is visible.
		$reviewRound =& $reviewRoundDao->getByMonographFileId($monographFile->getFileId());
		import('classes.workflow.EditorDecisionActionsManager');
		if (!EditorDecisionActionsManager::getEditorTakenActionInReviewRound($reviewRound)) {
			return AUTHORIZATION_DENY;
		}
		if(!$monographFile->getViewable()) return AUTHORIZATION_DENY;

		// Make sure that it's in the review stage.
		$reviewRound =& $reviewRoundDao->getByMonographFileId($monographFile->getFileId());
		if (!is_a($reviewRound, 'ReviewRound')) return AUTHORIZATION_DENY;

		// Make sure review round stage is the same of the current stage in request.
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		if ($reviewRound->getStageId() != $stageId) return AUTHORIZATION_DENY;

		// Make sure that the last review round editor decision is request revisions.
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO'); /* @var $seriesEditorSubmissionDao SeriesEditorSubmissionDAO */
		$reviewRoundDecisions = $seriesEditorSubmissionDao->getEditorDecisions($monographFile->getMonographId(), $reviewRound->getStageId(), $reviewRound->getRound());
		if (empty($reviewRoundDecisions)) return AUTHORIZATION_DENY;
		$lastEditorDecision = array_pop($reviewRoundDecisions);
		if ($lastEditorDecision['decision'] != SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS) return AUTHORIZATION_DENY;

		// Made it through -- permit access.
		return AUTHORIZATION_PERMIT;
	}
}

?>
