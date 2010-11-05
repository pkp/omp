<?php

/**
 * @file controllers/grid/files/finalDraftFiles/form/ManageFinalDraftFilesForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageFinalDraftFilesForm
 * @ingroup controllers_grid_files_finalDraftFiles
 *
 * @brief Form to add files to the final draft files grid
 */

import('lib.pkp.classes.form.Form');

class ManageFinalDraftFilesForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function ManageFinalDraftFilesForm($monographId) {
		parent::Form('controllers/grid/files/finalDraftFiles/manageFinalDraftFiles.tpl');
		$this->_monographId = (int) $monographId;

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Overridden template methods
	//
	/**
	 * Initialize variables
	 * @param $args array
	 * @param $request PKPRequest
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
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function &execute($args, &$request) {
		$monographId = $this->_monographId;
		$reviewType = (integer)$this->getData('reviewType');
		$round = (integer)$this->getData('round');
		if($this->getData('selectedFiles')) {
			$selectedFiles = $this->getData('selectedFiles');
		} else {
			$selectedFiles = array();
		}

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$allMonographFiles =& $monographFileDao->getByMonographId($monographId);

		// Set the selected files to final, all other files to submission
		foreach($allMonographFiles as $monographFile) {
			$fileIdAndRevision = $monographFile->getFileId() . "-" . $monographFile->getRevision();
			if(in_array($fileIdAndRevision, $selectedFiles)) {
				$monographFile->setType('final');
			} else {
				$monographFile->setType('submission');
			}
			$monographFileDao->updateMonographFile($monographFile);
		}

		// Return the files that are currently set for the review
		$finalMonographFiles =& $monographFileDao->getByMonographId($monographId, 'final');
		return $finalMonographFiles;
	}
}

?>
