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

class SubmissionFilesUploadForm extends Form {
	/** @var integer the id of the monograph being edited */
	var $_monographId;

	/** @var integer the workflow stage file store we are working with */
	var $_fileStage;

	/** @var boolean whether we revise an existing file */
	var $_isRevision;

	/** @var integer will be non-null when we revise an existing file */
	var $_revisedFileId;

	/**
	 * Constructor.
	 * @param $request Request
	 * @param $monographId integer
	 * @param $fileStage integer
	 * @param $isRevision boolean
	 * @param $revisedFileId integer
	 */
	function SubmissionFilesUploadForm(&$request, $monographId, $fileStage, $isRevision = false, $revisedFileId = null) {
		// Check the incoming parameters.
		assert(is_numeric($monographId) && $monographId > 0);
		assert(is_numeric($fileStage) && $fileStage > 0);

		// Initialize class.
		$this->_monographId = (int)$monographId;
		$this->_fileStage = (int)$fileStage;
		$this->_isRevision = (boolean)$isRevision;
		$this->_revisedFileId = is_null($revisedFileId) ? null : (int)$revisedFileId;
		if (!$isRevision) {
			assert(is_null($revisedFileId));
		}

		parent::Form('controllers/grid/files/submissionFiles/form/fileForm.tpl');

		// Add validators.
		$this->addCheck(new FormValidatorPost($this));

		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		$this->addCheck(new FormValidatorCustom($this, 'fileType', FORM_VALIDATOR_REQUIRED_VALUE,
				'submission.upload.noFileType',
				create_function('$fileType,$fileTypeDao,$context', 'return is_a($fileTypeDao->getById($fileType, $context->getId()), "MonographFileType");'),
				array(DAORegistry::getDAO('MonographFileTypeDAO'), $context)));
	}


	//
	// Setters and Getters
	//
	/**
	 * Get the monograph id.
	 * @return integer
	 */
	function getMonographId() {
		return $this->_monographId;
	}

	/**
	 * Get the file stage.
	 * @return integer
	 */
	function getFileStage() {
		return $this->_fileStage;
	}

	/**
	 * Do we revise an existing file?
	 * @return boolean
	 */
	function isRevision() {
		return $this->_isRevision;
	}

	/**
	 * Returns the id of the file being revised
	 * by the uploaded file.
	 * Will return null if no file has been
	 * selected as revised file.
	 * @return integer
	 */
	function getRevisedFileId() {
		return $this->_revisedFileId;
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData($args, &$request) {
		// Pass the form state on to the template.
		$this->setData('monographId', $this->getMonographId());
		$this->setData('isRevision', $this->isRevision());
		$this->setData('revisedFileId', $this->getRevisedFileId());

		// Retrieve the monograph files for the given file stage.
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$monographFiles =& $monographFileDao->getByMonographId($this->getMonographId(), $this->getFileStage());

		// Initialize the list with files available for review.
		$monographFileOptions = array();
		$currentMonographFileGenres = array();

		// If this is not a "review only" form then add a default
		// item.
		if (!$this->isRevision()) $monographFileOptions[0] = Locale::translate('submission.upload.uploadNewFile');

		// Go through all files and build a list of files available for review.
		$foundRevisedFile = false;
		$this->setData('currentFileType', null);
		for ($i = 0; $i < count($monographFiles); $i++) {
			// Only look at the latest revision of each file. Files
			// come sorted by file id and revision.
			if (!isset($monographFiles[$i+1])
					|| $monographFiles[$i]->getFileId() != $monographFiles[$i+1]->getFileId()) {
				// Is this the revised file?
				if ($this->getRevisedFileId() == $monographFiles[$i]->getFileId()) {
					// This is the uploaded monograph file, so pass it's data on to the form.
					$this->setData('revisedMonographFileName', $monographFiles[$i]->getOriginalFileName());
					$this->setData('currentFileType', $monographFiles[$i]->getMonographFileTypeId());
					$foundRevisedFile = true;
				}

				// Create an entry in the list of existing files which
				// the user can select from in case he chooses to upload
				// a revision.
				$fileName = $monographFiles[$i]->getLocalizedName() != '' ? $monographFiles[$i]->getLocalizedName() : Locale::translate('common.untitled');
				if ($monographFiles[$i]->getRevision() > 1) $fileName .= ' (' . $monographFiles[$i]->getRevision() . ')';
				$monographFileOptions[$monographFiles[$i]->getFileId()] = $fileName;
				$currentMonographFileGenres[$monographFiles[$i]->getFileId()] = $monographFiles[$i]->getMonographFileTypeId();
			}
		}
		$this->setData('monographFileOptions', $monographFileOptions);
		$this->setData('currentMonographFileGenres', $currentMonographFileGenres);

		// Make sure that the revised file (if any) really was among
		// the retrieved monograph files in the current file stage.
		if ($this->getRevisedFileId() && !$foundRevisedFile) fatalError('Invalid revised file id!');

		// Retrieve available monograph file types.
		$context =& $request->getContext();
		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');
		$monographFileGenres =& $monographFileTypeDao->getEnabledByPressId($context->getId());

		// Transform the monograph file types into an array and
		// assign them to the form.
		$monographFileGenreList = array();
		while($monographFileGenre =& $monographFileGenres->next()){
			$monographFileGenreId = $monographFileGenre->getId();
			$monographFileGenreList[$monographFileGenreId] = $monographFileGenre->getLocalizedName();
			unset($monographFileGenre);
		}
		$this->setData('monographFileGenres', $monographFileGenreList);
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('fileType', 'revisedFileId'));
	}

	/**
	 * @see Form::execute()
	 */
	function execute(&$request) {
		// Is this a revision?
		$revisedFileId = (int)$this->getRevisedFileId();
		if ($revisedFileId) {
			// The file genre will be copied over from the revised file.
			$fileGenre = null;
		} else {
			// This is a new file so we need the file genre from the form.
			$fileGenre = (int)$this->getData('fileType');
		}
		if($uploadedFile = MonographFileManager::uploadMonographFile(
				$this->getMonographId(), 'submissionFile', $this->getFileStage(), $revisedFileId, $fileGenre)) {

			// Customize the URLs of the form to point to this file.
			$router =& $request->getRouter();
			$queryParams = array(
				'monographId' => $this->getMonographId(),
				'fileId' => $uploadedFile->getFileId(),
				'revision' => $uploadedFile->getRevision()
			);
			$additionalAttributes = array(
 				'fileFormUrl' => $router->url($request, null, null, 'displayFileForm', null, $queryParams),
				'metadataUrl' => $router->url($request, null, null, 'editMetadata', null, $queryParams),
				'deleteUrl' => $router->url($request, null, null, 'deleteFile', null, $queryParams)
			);

			$fileName = $uploadedFile->getOriginalFilename();
			$json = new JSON('true', Locale::translate('submission.uploadSuccessfulContinue', array('fileName' => $fileName)), 'false', '0', $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('common.uploadFailed'));
		}

		return $json->getString();
	}
}

?>
