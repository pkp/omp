<?php

/**
 * @file controllers/wizard/fileUpload/form/SubmissionFilesUploadBaseForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadBaseForm
 * @ingroup controllers_wizard_fileUpload_form
 *
 * @brief Form for adding/editing a submission file
 */


import('lib.pkp.classes.form.Form');
import('classes.file.MonographFileManager');
import('classes.monograph.MonographFile');

class SubmissionFilesUploadBaseForm extends Form {

	/** @var integer */
	var $_stageId;

	/** @var array the monograph files for this monograph and file stage */
	var $_monographFiles;

	/** @var integer */
	var $_reviewType;

	/** @var integer */
	var $_round;


	/**
	 * Constructor.
	 * @param $request Request
	 * @param $template string
	 * @param $monographId integer
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $fileStage integer
	 * @param $revisionOnly boolean
	 * @param $reviewType integer
	 * @param $round integer
	 * @param $revisedFileId integer
	 */
	function SubmissionFilesUploadBaseForm(&$request, $template, $monographId, $stageId, $fileStage,
			$revisionOnly = false, $reviewType = null, $round = null, $revisedFileId = null) {

		// Check the incoming parameters.
		assert(is_numeric($monographId) && $monographId > 0);
		assert(is_numeric($fileStage) && $fileStage > 0);
		assert(is_numeric($stageId) && $stageId >= 1 && $stageId <= 5);

		// Initialize class.
		parent::Form($template);
		$this->_stageId = $stageId;
		$this->setData('fileStage', (int)$fileStage);
		$this->setData('monographId', (int)$monographId);
		$this->setData('revisionOnly', (boolean)$revisionOnly);
		$this->_reviewType = $reviewType ? (int)$reviewType : null;
		$this->_round = $round ? (int)$round : null;
		$this->setData('revisedFileId', $revisedFileId ? (int)$revisedFileId : null);

		// Add validators.
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Setters and Getters
	//
	/**
	 * Get the workflow stage id.
	 * @return integer
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the review type (if any).
	 * @return integer
	 */
	function getReviewType() {
		return $this->_reviewType;
	}

	/**
	 * Get the review round (if any).
	 * @return integer
	 */
	function getRound() {
		return $this->_round;
	}

	/**
	 * Get the revised file id (if any).
	 * @return int the revised file id
	 */
	function getRevisedFileId() {
		return $this->getData('revisedFileId') ? (int)$this->getData('revisedFileId') : null;
	}

	/**
	 * Get the monograph files belonging to the
	 * monograph and to the file stage.
	 * @return array a list of MonographFile instances.
	 */
	function &getMonographFiles() {
		if (is_null($this->_monographFiles)) {
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			if ($this->getReviewType()) {
				// If we have a review type then we also expect a review round.
				if ($this->getRound() < 1) fatalError('Invalid review round!');

				// Review round files only can point to submission files.
				if ($this->getData('fileStage') != MONOGRAPH_FILE_SUBMISSION) fatalError('Invalid file stage!');

				// Retrieve the monograph files for the given review round.
				$this->_monographFiles =& $submissionFileDao->getRevisionsByReviewRound(
					$this->getData('monographId'),
					$this->getReviewType(), $this->getRound()
				);
			} else {
				// Retrieve the monograph files for the given file stage.
				$this->_monographFiles =& $submissionFileDao->getLatestRevisions(
					$this->getData('monographId'), $this->getData('fileStage')
				);
			}
		}

		return $this->_monographFiles;
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		// Only Genre and revised file can be set in the form. All other
		// information is generated on our side.
		$this->readUserVars(array('revisedFileId'));
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		// Set the workflow stage.
		$this->setData('stageId', $this->getStageId());

		// Retrieve the uploaded file (if any).
		$uploadedFile =& $this->getData('uploadedFile');

		// Initialize the list with files available for review.
		$monographFileOptions = array();
		$currentMonographFileGenres = array();

		// Go through all files and build a list of files available for review.
		$revisedFileId = $this->getRevisedFileId();
		$foundRevisedFile = false;
		$monographFiles =& $this->getMonographFiles();
		foreach ($monographFiles as $monographFile) {
			// The uploaded file must be excluded from the list of revisable files.
			if ($uploadedFile && $uploadedFile->getFileId() == $monographFile->getFileId()) continue;

			// Is this the revised file?
			if ($revisedFileId && $revisedFileId == $monographFile->getFileId()) {
				// This is the revised monograph file, so pass it's data on to the form.
				$this->setData('revisedFileName', $monographFile->getOriginalFileName());
				$this->setData('genreId', $monographFile->getGenreId());
				$foundRevisedFile = true;
			}

			// Create an entry in the list of existing files which
			// the user can select from in case he chooses to upload
			// a revision.
			$fileName = $monographFile->getLocalizedName() != '' ? $monographFile->getLocalizedName() : Locale::translate('common.untitled');
			if ($monographFile->getRevision() > 1) $fileName .= ' (' . $monographFile->getRevision() . ')';
			$monographFileOptions[$monographFile->getFileId()] = $fileName;
			$currentMonographFileGenres[$monographFile->getFileId()] = $monographFile->getGenreId();
		}

		// If this is not a "review only" form then add a default item.
		if (count($monographFileOptions) && !$this->getData('revisionOnly')) {
			$monographFileOptions = array('' => Locale::translate('submission.upload.uploadNewFile')) + $monographFileOptions;
		}

		// Make sure that the revised file (if any) really was among
		// the retrieved monograph files in the current file stage.
		if ($revisedFileId && !$foundRevisedFile) fatalError('Invalid revised file id!');

		// Set the review file candidate data in the template.
		$this->setData('currentMonographFileGenres', $currentMonographFileGenres);
		$this->setData('monographFileOptions', $monographFileOptions);

		return parent::fetch($request);
	}
}

?>
