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


import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadBaseForm');

class SubmissionFilesUploadForm extends SubmissionFilesUploadBaseForm {
	/**
	 * Constructor.
	 * @param $request Request
	 * @param $monographId integer
	 * @param $fileStage integer
	 * @param $revisionOnly boolean
	 * @param $revisedFileId integer
	 */
	function SubmissionFilesUploadForm(&$request, $monographId, $fileStage, $revisionOnly = false, $revisedFileId = null) {
		// Initialize class.
		parent::SubmissionFilesUploadBaseForm($request,
				'controllers/grid/files/submissionFiles/form/fileUploadForm.tpl',
				$monographId, $fileStage, $revisionOnly, $revisedFileId);
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('genreId'));
		return parent::readInputData();
	}

	/**
	 * @see Form::validate()
	 */
	function validate(&$request) {
		// Is this a revision?
		$revisedFileId = $this->getRevisedFileId();
		if ($this->getData('revisionOnly')) {
			assert($revisedFileId > 0);
		}

		if (!$revisedFileId) {
			// Add an additional check for the genre to the form.
			$router =& $request->getRouter();
			$context =& $router->getContext($request);
			$this->addCheck(new FormValidatorCustom($this, 'genreId', FORM_VALIDATOR_REQUIRED_VALUE,
					'submission.upload.noFileType',
					create_function('$genreId,$genreDao,$context', 'return is_a($genreDao->getById($genreId, $context->getId()), "Genre");'),
					array(DAORegistry::getDAO('GenreDAO'), $context)));
		}

		return parent::validate();
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		// Retrieve available monograph file genres.
		$genreList =& $this->_retrieveGenreList($request);
		$this->setData('monographFileGenres', $genreList);

		return parent::fetch($request);
	}

	/**
	 * @see Form::execute()
	 * @return MonographFile if successful, otherwise null
	 */
	function &execute() {
		// Identify the file genre.
		$revisedFileId = $this->getRevisedFileId();
		if ($revisedFileId) {
			// The file genre will be copied over from the revised file.
			$fileGenre = null;
		} else {
			// This is a new file so we need the file genre from the form.
			$fileGenre = $this->getData('genreId') ? (int)$this->getData('genreId') : null;
		}

		// Upload the file.
		import('classes.file.MonographFileManager');
		return MonographFileManager::uploadMonographFile(
				$this->getData('monographId'), 'uploadedFile', $this->getFileStage(), $revisedFileId, $fileGenre);
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
		$genreList = array();
		while($genre =& $genres->next()){
			$genreId = $genre->getId();
			$genreList[$genreId] = $genre->getLocalizedName();
			unset($genre);
		}
		return $genreList;
	}
}

?>
