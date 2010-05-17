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
		parent::Form('controllers/grid/submissions/pressEditor/approveAndReview.tpl');
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
		$reviewType = (int) $args['reviewType'];
		$round = (int) $args['round'];

		$this->setData('monographId', $this->_monographId);
		$this->setData('reviewType', $reviewType);
		$this->setData('round', $round);
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

		// TODO:
		// 1. Accept review
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		// FIXME: what do do about reviewRevision? being set to 1 for now.
		// Create a review round if it doesn't exist
		if ( !$reviewRoundDao->reviewRoundExists($this->_monographId, $reviewType, $round)) {
			$reviewRoundDao->createReviewRound($this->_monographId, $reviewType, $round, 1);
		}

		// 2. Get selected files and put in DB somehow
		$selectedFiles = $this->getData('selectedFiles');
		$reviewAssignmentDAO =& DAORegistry::getDAO('ReviewAssignmentDAO');

		$reviewAssignmentDAO->setFilesForReview($this->_monographId, $reviewType, $round, $selectedFiles);
		// 3. Send Personal message to author
	}
}

?>
