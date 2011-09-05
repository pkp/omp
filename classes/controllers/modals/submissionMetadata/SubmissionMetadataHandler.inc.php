<?php

/**
 * @file classes/controllers/modals/submissionMetadata/SubmissionMetadataHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionMetadataHandler
 * @ingroup classes_controllers_modals_submissionMetadata
 *
 * @brief Base class for submission metadata view/edit operations
 */

import('classes.handler.Handler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSONMessage');

class SubmissionMetadataHandler extends Handler {
	/**
	 * Constructor.
	 */
	function SubmissionMetadataHandler() {
		parent::Handler();
	}


	//
	// Public handler methods
	//
	/**
	 * Display the submission's metadata
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function fetch(&$request, $args, $params = null) {
		// Identify the submission
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION));

		// Identify the stage, if we have one.
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);

		// Form handling
		import('controllers.modals.submissionMetadata.form.SubmissionMetadataViewForm');
		$submissionMetadataViewForm = new SubmissionMetadataViewForm($monograph->getId(), $stageId, $params);
		$submissionMetadataViewForm->initData($args, $request);

		$json = new JSONMessage(true, $submissionMetadataViewForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the submission metadata form.
	 * @param $args array
	 * @param $request Request
	 */
	function saveForm($args, &$request) {
		$monographId = $request->getUserVar('monographId');

		// Form handling
		import('controllers.modals.submissionMetadata.form.SubmissionMetadataViewForm');
		$submissionMetadataViewForm = new SubmissionMetadataViewForm($monographId);

		$json = new JSONMessage();

		// Try to save the form data.
		$submissionMetadataViewForm->readInputData($request);
		if($submissionMetadataViewForm->validate()) {
			$submissionMetadataViewForm->execute($request);
			// Create trivial notification.
			$notificationManager = new NotificationManager();
			$user =& $request->getUser();
			$notificationManager->createTrivialNotification($user->getId(), 'notification.savedSubmissionMetadata');
		} else {
			$json->setStatus(false);
		}

		return $json->getString();
	}
}

?>
