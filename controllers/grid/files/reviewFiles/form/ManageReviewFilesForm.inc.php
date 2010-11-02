<?php

/**
 * @file controllers/grid/submissions/pressEditor/form/ManageReviewFilesForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageReviewFilesForm
 * @ingroup controllers_grid_files_reviewFiles_form
 *
 * @brief Form for add or removing files from a review
 */

import('lib.pkp.classes.form.Form');

class ManageReviewFilesForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function ManageReviewFilesForm($monographId) {
		parent::Form('controllers/grid/files/reviewFiles/manageReviewFiles.tpl');
		$this->_monographId = (int) $monographId;

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Overridden template methods
	//
	/**
	 * Initialize variables
	 */
	function initData($args, &$request) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($this->_monographId);

		$this->setData('monographId', $this->_monographId);
		$this->setData('reviewType', $monograph->getCurrentReviewType());
		$this->setData('round', $monograph->getCurrentRound());
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('reviewType', 'round', 'selectedFiles'));
	}

	/**
	 * Save submissionContributor
	 */
	function &execute($args, &$request) {
		$reviewType = (integer)$this->getData('reviewType');
		$round = (integer)$this->getData('round');

		$selectedFiles = $this->getData('selectedFiles');
		$filesWithRevisions = array();
		if (!empty($selectedFiles)) {
			foreach ($selectedFiles as $selectedFile) {
				$filesWithRevisions[] = explode("-", $selectedFile);
			}
		}
		$reviewAssignmentDAO =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignmentDAO->setFilesForReview($this->_monographId, $reviewType, $round, $filesWithRevisions);

		// Return the files that are currently set for the review
		$reviewFilesByRound =& $reviewAssignmentDAO->getReviewFilesByRound($this->_monographId);
		if (isset($reviewFilesByRound[$reviewType][$round])) {
			return $reviewFilesByRound[$reviewType][$round];
		} else {
			$noFiles = array();
			return $noFiles;
		}
	}
}

?>
