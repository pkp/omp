<?php

/**
 * @file controllers/grid/files/submissionFiles/form/SubmissionFilesUploadBaseForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadBaseForm
 * @ingroup controllers_grid_files_submissionFiles_form
 *
 * @brief Form for adding/editing a submission file
 */


import('lib.pkp.classes.form.Form');
import('classes.file.MonographFileManager');

class SubmissionFilesUploadBaseForm extends Form {
	/** @var integer the workflow stage file store we are working with */
	var $_fileStage;

	/** @var array the monograph files for this monograph and file stage */
	var $_monographFiles;

	/**
	 * Constructor.
	 * @param $request Request
	 * @param $template string
	 * @param $monographId integer
	 * @param $fileStage integer
	 * @param $revisionOnly boolean
	 * @param $revisedFileId integer
	 */
	function SubmissionFilesUploadBaseForm(&$request, $template, $monographId, $fileStage, $revisionOnly = false, $revisedFileId = null) {
		// Check the incoming parameters.
		assert(is_numeric($monographId) && $monographId > 0);
		assert(is_numeric($fileStage) && $fileStage > 0);

		// Initialize class.
		parent::Form($template);
		$this->_fileStage = (int)$fileStage;
		$this->setData('monographId', (int)$monographId);
		$this->setData('revisionOnly', (boolean)$revisionOnly);
		$this->setData('revisedFileId', $revisedFileId ? (int)$revisedFileId : null);

		// Add validators.
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Setters and Getters
	//
	/**
	 * Get the file stage.
	 * @return integer
	 */
	function getFileStage() {
		return $this->_fileStage;
	}

	/**
	 * Get the monograph files belonging to the
	 * monograph and to the file stage.
	 * @return array a list of MonographFile instances.
	 */
	function &getMonographFiles() {
		if (is_null($this->_monographFiles)) {
			// Retrieve the monograph files for the given file stage.
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$this->_monographFiles =& $submissionFileDao->getLatestRevisions($this->getData('monographId'), $this->getFileStage());
		}

		return $this->_monographFiles;
	}

	/**
	 * Get the revised file id (if any).
	 * @return int the revised file id
	 */
	function getRevisedFileId() {
		return $this->getData('revisedFileId') ? (int)$this->getData('revisedFileId') : null;
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
		// Retrieve the uploaded file (if any).
		$uploadedFile =& $this->getData('uploadedFile');

		// Initialize the list with files available for review.
		$monographFileOptions = array();
		$currentMonographFileGenres = array();

		// Go through all files and build a list of files available for review.
		$revisedFileId = $this->getRevisedFileId();
		$foundRevisedFile = false;
		$monographFiles =& $this->getMonographFiles();
		for ($i = 0; $i < count($monographFiles); $i++) {
			// Only look at the latest revision of each file. Files
			// come sorted by file id and revision.
			if (!isset($monographFiles[$i+1])
					|| $monographFiles[$i]->getFileId() != $monographFiles[$i+1]->getFileId()) {
				// The uploaded file must be excluded from the list of revisable files.
				if ($uploadedFile && $uploadedFile->getFileId() == $monographFiles[$i]->getFileId()) continue;

				// Is this the revised file?
				if ($revisedFileId && $revisedFileId == $monographFiles[$i]->getFileId()) {
					// This is the revised monograph file, so pass it's data on to the form.
					$this->setData('revisedFileName', $monographFiles[$i]->getOriginalFileName());
					$this->setData('genreId', $monographFiles[$i]->getGenreId());
					$foundRevisedFile = true;
				}

				// Create an entry in the list of existing files which
				// the user can select from in case he chooses to upload
				// a revision.
				$fileName = $monographFiles[$i]->getLocalizedName() != '' ? $monographFiles[$i]->getLocalizedName() : Locale::translate('common.untitled');
				if ($monographFiles[$i]->getRevision() > 1) $fileName .= ' (' . $monographFiles[$i]->getRevision() . ')';
				$monographFileOptions[$monographFiles[$i]->getFileId()] = $fileName;
				$currentMonographFileGenres[$monographFiles[$i]->getFileId()] = $monographFiles[$i]->getGenreId();
			}
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
