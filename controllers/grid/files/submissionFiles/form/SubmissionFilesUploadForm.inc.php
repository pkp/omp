<?php

/**
 * @file controllers/grid/files/submissionFiles/form/SubmissionFilesUploadForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadForm
 * @ingroup controllers_grid_files_submissionFiles_form
 *
 * @brief Form for adding/editing a submission file
 */


import('lib.pkp.classes.form.Form');
import('classes.file.MonographFileManager');

// The percentage of characters that the name of a file
// has to share with an existing file for it to be
// considered as a revision of that file.
define('SUBMISSION_MIN_SIMILARITY_OF_REVISION', 70);

// Form execution modes.
define('FILE_FORM_UPLOAD', 1);
define('FILE_FORM_REVISE', 2);

class SubmissionFilesUploadForm extends Form {
	/** @var integer the workflow stage file store we are working with */
	var $_fileStage;

	/** @var array the monograph files for this monograph and file stage */
	var $_monographFiles;

	/**
	 * Constructor.
	 * @param $request Request
	 * @param $monographId integer
	 * @param $fileStage integer
	 * @param $revisionOnly boolean
	 */
	function SubmissionFilesUploadForm(&$request, $monographId, $fileStage, $revisionOnly = false, $revisedFileId = null) {
		// Check the incoming parameters.
		assert(is_numeric($monographId) && $monographId > 0);
		assert(is_numeric($fileStage) && $fileStage > 0);

		// Initialize class.
		parent::Form('controllers/grid/files/submissionFiles/form/fileForm.tpl');
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
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
			$this->_monographFiles =& $monographFileDao->getByMonographId($this->getData('monographId'), $this->getFileStage());
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
		$this->readUserVars(array('genreId', 'revisedFileId', 'uploadedFileId'));
	}

	/**
	 * @see Form::validate()
	 * @param $request Request
	 * @param $executionMode integer one of the FILE_FORM_* constants
	 * @return boolean
	 */
	function validate(&$request, $executionMode) {
		if ($executionMode == FILE_FORM_UPLOAD && !$this->getData('revisedFileId')) {
			// Add an additional check for the genre to the form.
			$router =& $request->getRouter();
			$context =& $router->getContext($request);
			$this->addCheck(new FormValidatorCustom($this, 'genreId', FORM_VALIDATOR_REQUIRED_VALUE,
					'submission.upload.noFileType',
					create_function('$genreId,$genreDao,$context', 'return is_a($genreDao->getById($genreId, $context->getId()), "Genre");'),
					array(DAORegistry::getDAO('GenreDAO'), $context)));
		} else {
			// Make sure that no invalid genre may slip through.
			$this->setData('genreId', null);
		}

		return parent::validate();
	}

	/**
	 * @see Form::execute()
	 * @param $executionMode integer one of the FILE_FORM_* constants
	 * @return boolean true if successful, otherwise false
	 */
	function execute($executionMode) {
		switch($executionMode) {
			case FILE_FORM_UPLOAD:
				$uploadedFile =& $this->_upload();
				break;

			case FILE_FORM_REVISE:
				$uploadedFile =& $this->_revise();
				break;

			default:
				assert(false);
		}

		// Save the uploaded file to the form.
		if (is_a($uploadedFile, 'MonographFile')) {
			$this->setData('uploadedFile', $uploadedFile);
			return true;
		}

		return false;
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
		$revisedFileId = $this->getData('revisedFileId');
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

		// Retrieve available monograph file genres.
		$genreList =& $this->_retrieveGenreList($request);
		$this->setData('monographFileGenres', $genreList);

		return parent::fetch($request);
	}


	//
	// Private helper methods
	//
	/**
	 * Retrieve the genre list.
	 * @param $request Request
	 * @return array
	 */
	function &_retrieveGenreList(&$request) {
		$context =& $request->getContext();
		$genreDao =& DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */
		$genres =& $genreDao->getEnabledByPressId($context->getId());

		// Transform the genres into an array and
		// assign them to the form.
		$monographFileGenreList = array();
		while($genre =& $genres->next()){
			$genreId = $genre->getId();
			$genreList[$genreId] = $genre->getLocalizedName();
			unset($genre);
		}
		return $genreList;
	}

	/**
	 * Executes the form by uploading a file.
	 * @return MonographFile the uploaded monograph file or null on error
	 */
	function &_upload() {
		// Is this a revision?
		$revisedFileId = $this->getData('revisedFileId') ? (int)$this->getData('revisedFileId') : null;
		if ($this->getData('revisionOnly')) {
			assert($revisedFileId > 0);
		}

		// Identify the file genre.
		if ($revisedFileId) {
			// The file genre will be copied over from the revised file.
			$fileGenre = null;
		} else {
			// This is a new file so we need the file genre from the form.
			$fileGenre = $this->getData('genreId') ? (int)$this->getData('genreId') : null;
		}

		// Upload the file.
		if($uploadedFile = MonographFileManager::uploadMonographFile(
				$this->getData('monographId'), 'submissionFile', $this->getFileStage(), $revisedFileId, $fileGenre)) {

			// If no revised file id was given then try out whether
			// the user maybe accidentally didn't identify this file as a revision.
			if (!$revisedFileId) {
				$this->setData('revisedFileId', $this->_checkForRevision($uploadedFile));
			}

			// Return the uploaded file.
			return $uploadedFile;
		} else {
			$nullVar = null;
			return $nullVar;
		}
	}

	/**
	 * Executes the form by revising a file.
	 * @return MonographFile the new monograph file revision or null on error
	 */
	function &_revise() {
		// Retrieve the file ids of the revised and the uploaded files.
		$revisedFileId = (int)$this->getData('revisedFileId');
		$uploadedFileId = (int)$this->getData('uploadedFileId');
		if (!($revisedFileId && $uploadedFileId)) fatalError('Invalid file ids!');

		// Assign the new file as the latest revision of the old file.
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$monographId = $this->getData('monographId');
		$uploadedFile =& $monographFileDao->setAsLatestRevision($revisedFileId, $uploadedFileId, $monographId, $this->getFileStage());

		if (is_a($uploadedFile, 'MonographFile')) {
			// Remove the now no longer valid form data.
			$this->setData('revisedFileId', null);
			$this->setData('uploadedFileId', null);

			// Return the updated file.
			return  $uploadedFile;
		} else {
			$nullVar = null;
			return $nullVar;
		}
	}

	/**
	 * Check if the uploaded file has a similar name to an existing
	 * file which would then be a candidate for a revised file.
	 * @param $uploadedFile MonographFile
	 * @return integer the if of the possibly revised file or null
	 *  if no matches were found.
	 */
	function &_checkForRevision(&$uploadedFile) {
		// Get the file name.
		$uploadedFileName = $uploadedFile->getOriginalFileName();

		// Start with the minimal required similarity.
		$minPercentage = SUBMISSION_MIN_SIMILARITY_OF_REVISION;

		// Find out whether one of the files belonging to the current
		// file stage matches the given file name.
		$possibleRevisedFileId = null;
		$matchedPercentage = 0;
		$monographFiles =& $this->getMonographFiles();
		foreach ($monographFiles as $monographFile) { /* @var $monographFile MonographFile */
			// Do not consider the uploaded file itself.
			if ($uploadedFile->getFileId() == $monographFile->getFileId()) continue;

			// Test whether the current monograph file is similar
			// to the uploaded file.
			similar_text($uploadedFileName, $monographFile->getOriginalFileName(), &$matchedPercentage);
			if($matchedPercentage > $minPercentage) {
				// We found a file that might be a possible revision.
				$possibleRevisedFileId = $monographFile->getFileId();

				// Reset the min percentage to this comparison's precentage
				// so that only better matches will be considered from now on.
				$minPercentage = $matchedPercentage;
			}
		}

		// Return the id of the file that we found similar.
		return $possibleRevisedFileId;
	}
}

?>
