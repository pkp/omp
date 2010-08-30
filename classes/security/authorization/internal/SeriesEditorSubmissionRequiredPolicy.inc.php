<?php
/**
 * @file classes/security/authorization/SeriesEditorSubmissionRequiredPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorSubmissionRequiredPolicy
 * @ingroup security_authorization
 *
 * @brief Policy that ensures that the request contains a valid series
 *  editor submission.
 */

import('lib.pkp.classes.security.authorization.SubmissionRequiredPolicy');

class SeriesEditorSubmissionRequiredPolicy extends SubmissionRequiredPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function SeriesEditorSubmissionRequiredPolicy(&$request, &$args, $submissionParameterName = 'monographId') {
		parent::SubmissionRequiredPolicy($request, $args, $submissionParameterName, 'Invalid series editor submission or no series editor submission requested!');
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		// Get the monograph id.
		$monographId = $this->getSubmissionId();
		if ($monographId === false) return AUTHORIZATION_DENY;

		// Validate the monograph id.
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($monographId);
		if (!is_a($seriesEditorSubmission, 'SeriesEditorSubmission')) return AUTHORIZATION_DENY;

		// Save the monograph to the authorization context.
		$this->addAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH, $seriesEditorSubmission);
		return AUTHORIZATION_PERMIT;
	}
}

?>
