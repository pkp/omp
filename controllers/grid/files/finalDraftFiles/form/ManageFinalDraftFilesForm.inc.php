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
	var $_monograph;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 */
	function ManageFinalDraftFilesForm(&$monograph) {
		parent::Form('controllers/grid/files/finalDraftFiles/manageFinalDraftFiles.tpl');
		$this->_monograph =& $monograph;

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Setters and Getters
	//
	/**
	 * Get the monograph.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
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
		$monograph =& $this->getMonograph();
		$this->setData('monographId', $monograph->getId());
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
	 * @return array a list of all monograph files marked as "final".
	 */
	function &execute($args, &$request) {
		// Identify selected files.
		$selectedFiles = $this->getData('selectedFiles');
		if(empty($selectedFiles) || !is_array($selectedFiles)) {
			$selectedFiles = array();
		}

		// Retrieve all monograph files.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monograph =& $this->getMonograph();
		$allMonographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId());

		// Set the selected files to 'final', all other files to 'submission'.
		$finalMonographFiles = array();
		foreach($allMonographFiles as $monographFile) {
			$fileIdAndRevision = $monographFile->getFileId() . "-" . $monographFile->getRevision();
			if(in_array($fileIdAndRevision, $selectedFiles)) {
				$monographFile->setFileStage(MONOGRAPH_FILE_FINAL);
				$finalMonographFiles[] =& $monographFile;
			} else {
				$monographFile->setFileStage(MONOGRAPH_FILE_SUBMISSION);
			}
			$submissionFileDao->updateObject($monographFile);
		}

		// Return the files that are currently set for the review.
		return $finalMonographFiles;
	}
}

?>
