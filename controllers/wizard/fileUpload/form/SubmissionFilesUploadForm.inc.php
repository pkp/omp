<?php

/**
 * @file controllers/wizard/fileUpload/form/SubmissionFilesUploadForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadForm
 * @ingroup controllers_wizard_fileUpload_form
 *
 * @brief Form for adding/editing a submission file
 */


import('controllers.wizard.fileUpload.form.SubmissionFilesUploadBaseForm');

class SubmissionFilesUploadForm extends SubmissionFilesUploadBaseForm {
	/**
	 * Constructor.
	 * @param $request Request
	 * @param $monographId integer
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $fileStage integer
	 * @param $revisionOnly boolean
	 * @param $revisedFileId integer
	 */
	function SubmissionFilesUploadForm(&$request, $monographId, $stageId, $uploaderRoles, $fileStage,
			$revisionOnly = false, $reviewType = null, $round = null, $revisedFileId = null) {

		// Initialize class.
		parent::SubmissionFilesUploadBaseForm($request,
				'controllers/wizard/fileUpload/form/fileUploadForm.tpl',
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
			$this->getData('monographId'), 'uploadedFile',
			$this->getData('fileStage'), $uploaderUserGroupId,
			$revisedFileId, $fileGenre
		);
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
