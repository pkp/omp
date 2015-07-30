<?php

/**
 * @file controllers/grid/files/fileSignoff/form/AuditorReminderForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuditorReminderForm
 * @ingroup controllers_grid_files_fileSignoff_form
 *
 * @brief Form for sending a singoff reminder to an auditor.
 */

import('lib.pkp.controllers.grid.files.fileSignoff.form.PKPAuditorReminderForm');

class AuditorReminderForm extends PKPAuditorReminderForm {

	/** The monograph id */
	var $_monographId;

	/** The current stage id */
	var $_stageId;

	/** The publication format id, if any */
	var $_representationId;

	/**
	 * Constructor.
	 */
	function AuditorReminderForm(&$signoff, $submissionId, $stageId, $representationId = null) {
		parent::PKPAuditorReminderForm($signoff, $submissionId, $stageId);
		$this->_representationId = $representationId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the publication format id.
	 * @return int
	 */
	function getPublicationFormatId() {
		return $this->_representationId;
	}


	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the associated author.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, $request) {
		parent::initData($args, $request);
		$this->setData('representationId', $this->getPublicationFormatId());
	}

	/**
	 * Return a context-specific instance of the mail template.
	 * @return MonographMailTemplate
	 */
	function _getMailTemplate($submission) {
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, 'REVIEW_REMIND', null, null, false);
		return $email;
	}
}

?>
