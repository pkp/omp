<?php

/**
 * @file controllers/grid/files/final/form/ManageFinalDraftFilesForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageFinalDraftFilesForm
 * @ingroup controllers_grid_files_finalDraftFiles
 *
 * @brief Form to add files to the final draft files grid
 */

import('lib.pkp.classes.form.Form');

class ManageFinalDraftFilesForm extends Form {
	/* @var int */
	var $_monographId;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 */
	function ManageFinalDraftFilesForm($monographId) {
		parent::Form('controllers/grid/files/final/manageFinalDraftFiles.tpl');
		$this->_monographId = (int)$monographId;

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Setters and Getters
	//
	/**
	 * Get the monograph.
	 * @return Monograph
	 */
	function getMonographId() {
		return $this->_monographId;
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
		$this->setData('monographId', $this->getMonographId());
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('selectedFiles'));
	}

	/**
	 * Save Selection of Final Draft files
	 * @param $args array
	 * @param $request PKPRequest
	 * @return array a list of all monograph files marked as "final".
	 */
	function execute($args, &$request) {
		// Identify selected files.
		$selectedFiles = $this->getData('selectedFiles');
		if(empty($selectedFiles) || !is_array($selectedFiles)) {
			$selectedFiles = array();
		}

		// Retrieve all monograph files.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$allMonographFiles =& $submissionFileDao->getLatestRevisions($this->getMonographId());

		// Set the selected files to 'final', all other files to 'submission'.
		foreach($allMonographFiles as $monographFile) {
			if ($monographFile->getFileStage() != MONOGRAPH_FILE_FINAL) continue;
			$fileIdAndRevision = $monographFile->getFileId() . '-' . $monographFile->getRevision();
			$monographFile->setViewable(in_array($fileIdAndRevision, $selectedFiles));
			$submissionFileDao->updateObject($monographFile);
		}
	}
}

?>
