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
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize($request) {
		$this->setupTemplate();
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
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_SUBMISSION);

		// Identify the stage, if we have one.
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);

		// Form handling
		$submissionMetadataViewForm = $this->getFormInstance($monograph->getId(), $stageId, $params);

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
		$submissionMetadataViewForm = $this->getFormInstance($monographId);

		$json = new JSONMessage();

		// Try to save the form data.
		$submissionMetadataViewForm->readInputData($request);
		if($submissionMetadataViewForm->validate()) {
			$submissionMetadataViewForm->execute($request);
			// Create trivial notification.
			$notificationManager = new NotificationManager();
			$user =& $request->getUser();
			$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.savedSubmissionMetadata')));
		} else {
			$json->setStatus(false);
		}

		return $json->getString();
	}

	/**
	 * Get an instance of the metadata form to be used by this handler.
	 * @param $monographId int
	 * @return Form
	 */
	function getFormInstance($monographId, $stageId = null, $params = null) {
		import('controllers.modals.submissionMetadata.form.CatalogEntrySubmissionReviewForm');
		return new CatalogEntrySubmissionReviewForm($monographId, $stageId, $params);
	}
}

?>
