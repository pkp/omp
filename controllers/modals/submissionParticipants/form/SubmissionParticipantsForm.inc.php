<?php

/**
 * @file controllers/modals/submissionParticipants/form/SubmissionParticipantsForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ResubmitForReviewForm
 * @ingroup controllers_modal_editorDecision_form
 *
 * @brief Displays a submission's metadata
 */

import('lib.pkp.classes.form.Form');

class SubmissionParticipantsForm extends Form {
	/** The monograph associated with the review assignment **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function SubmissionParticipantsForm($monographId) {
		parent::Form('controllers/modals/submissionParticipants/form/submissionParticipants.tpl');
		$this->_monographId = (int) $monographId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getMonograph() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		return $monographDao->getMonograph($this->_monographId);
	}

	//
	// Overridden template methods
	//
	/**
	* Initialize form data with the author name and the monograph id.
	* @param $args array
	* @param $request PKPRequest
	*/
	function initData($args, &$request) {
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_SUBMISSION));

		$this->_data = array(
			'monographId' => $this->_monographId,
			'monograph' => $this->getMonograph()
		);
	}
}

?>
