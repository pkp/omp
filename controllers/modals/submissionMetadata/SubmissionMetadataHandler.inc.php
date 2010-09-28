<?php

/**
 * @file controllers/modals/submissionMetadata/SubmissionMetadataHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionMetadataHandler
 * @ingroup controllers_modals_submissionMetadata
 *
 * @brief Handle requests for editors to make a decision
 */

import('classes.handler.Handler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSON');

class SubmissionMetadataHandler extends Handler {
	/**
	 * Constructor.
	 */
	function SubmissionMetadataHandler() {
		parent::Handler();
	}


	/**
	 * Display the submission's metadata
	 * @return JSON
	 */
	function fetch($args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION));

		// Form handling
		import('controllers.modals.submissionMetadata.form.SubmissionMetadataForm');
		$submissionMetadataForm = new SubmissionMetadataForm($monographId);
		$submissionMetadataForm->initData($args, $request);

		$json = new JSON('true', $submissionMetadataForm->fetch($request));
		return $json->getString();
	}


}
?>