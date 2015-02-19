<?php
/**
 * @file classes/security/authorization/internal/SeriesEditorSubmissionRequiredPolicy.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorSubmissionRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Policy that ensures that the request contains a valid series
 *  editor submission.
 */

import('lib.pkp.classes.security.authorization.DataObjectRequiredPolicy');

class SeriesEditorSubmissionRequiredPolicy extends DataObjectRequiredPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $submissionParameterName string
	 */
	function SeriesEditorSubmissionRequiredPolicy($request, &$args, $submissionParameterName = 'submissionId', $operations = null) {
		parent::DataObjectRequiredPolicy($request, $args, $submissionParameterName, 'user.authorization.invalidSeriesEditorSubmission', $operations);
	}

	//
	// Implement template methods from DataObjectRequiredPolicy
	//
	/**
	 * @see DataObjectAuthorizationPolicy::dataObjectEffect()
	 */
	function dataObjectEffect() {
		// Get the monograph id.
		$monographId = $this->getDataObjectId();
		if ($monographId === false) return AUTHORIZATION_DENY;

		// Validate the monograph id.
		$seriesEditorSubmissionDao = DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmission = $seriesEditorSubmissionDao->getById($monographId);
		if (!is_a($seriesEditorSubmission, 'SeriesEditorSubmission')) return AUTHORIZATION_DENY;

		// Validate that this monograph belongs to the current press.
		$press = $this->_request->getPress();
		if ($press->getId() !== $seriesEditorSubmission->getPressId()) return AUTHORIZATION_DENY;

		// Save the monograph to the authorization context.
		$this->addAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH, $seriesEditorSubmission);
		return AUTHORIZATION_PERMIT;
	}
}

?>
