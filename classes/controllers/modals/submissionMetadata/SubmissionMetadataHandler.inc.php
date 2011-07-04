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
	function fetch($args, &$request) {
		// Identify the submission
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION));

		// Form handling
		import('controllers.modals.submissionMetadata.form.SubmissionMetadataForm');
		$submissionMetadataForm = new SubmissionMetadataForm($monograph->getId());
		$submissionMetadataForm->initData($args, $request);

		$json = new JSONMessage(true, $submissionMetadataForm->fetch($request));
		return $json->getString();
	}
}

?>
