<?php

/**
 * @file controllers/grid/submissions/pressEditor/form/AddReviewFileForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddReviewFileForm
 * @ingroup controllers_grid_submissions_pressEditor
 *
 * @brief Form for approving a submission
 */

import('lib.pkp.classes.form.Form');

class AddReviewFileForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function AddReviewFileForm($monographId) {
		parent::Form('controllers/grid/files/reviewFiles/addReviewFile.tpl');
		$this->_monographId = (int) $monographId;

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Template methods from Form
	//
	/**
	 * Initialize variables
	 */
	function initData(&$args, &$request) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($this->_monographId);

		$this->setData('monographId', $this->_monographId);
		$this->setData('reviewType', $monograph->getCurrentReviewType());
		$this->setData('round', $monograph->getCurrentRound());
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('reviewType', 'round', 'selectedFiles'));
	}

	/**
	 * Save submissionContributor
	 */
	function execute(&$args, &$request) {
		$reviewType = $this->getData('reviewType');
		$round = $this->getData('round');

		$selectedFiles = $this->getData('selectedFiles');
		$reviewAssignmentDAO =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignmentDAO->setFilesForReview($this->_monographId, $reviewType, $round, $selectedFiles);
	}
}

?>
