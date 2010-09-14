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
 * @brief Form for adding/edditing a submission file
 */

import('lib.pkp.classes.form.Form');

class SubmissionFilesUploadForm extends Form {
	/** the id of the file being edited */
	var $_fileId;

	/** the id of the monograph being edited */
	var $_monographId;

	/**
	 * Constructor.
	 */
	function SubmissionFilesUploadForm($fileId = null, $monographId) {
		$this->_fileId = $fileId;
		$this->_monographId = $monographId;

		parent::Form('controllers/grid/files/submissionFiles/form/fileForm.tpl');

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData(&$args, &$request) {
		$this->_data['monographId'] = $this->_monographId;
		if (isset($this->_fileId) ) {
			$this->_data['fileId'] = $this->_fileId;

			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFile =& $monographFileDao->getMonographFile($this->_fileId);
			$this->_data['monographFileName'] = $monographFile->getOriginalFileName();
			$this->_data['currentFileType'] = $monographFile->getAssocId();
		}

		$context =& $request->getContext();
		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$bookFileTypes = $bookFileTypeDao->getEnabledByPressId($context->getId());

		$bookFileTypeList = array();
		while($bookFileType =& $bookFileTypes->next()){
			$bookFileTypeId = $bookFileType->getId();
			$bookFileTypeList[$bookFileTypeId] = $bookFileType->getLocalizedName();
			unset($bookFileType);
		}

		$this->_data['bookFileTypes'] = $bookFileTypeList;
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 */
	function fetch(&$request) {
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('gridId', 'fileType'));
	}

	/**
	 * Check if the uploaded file has a similar name to existing files (i.e., a possible revision)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int MonographFile Id
	 */
	function checkForRevision(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);

		if ($monographFileManager->uploadedFileExists('submissionFile')) {
			$fileName = $monographFileManager->getUploadedFileName('submissionFile');

			// Check similarity of filename against existing filenames
			return $this->_checkForSimilarFilenames($fileName, $monographId);
		}
	}

	/**
	 * Upload the submission file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int
	 */
	function uploadFile(&$args, &$request) {
		$monographId = $this->_monographId;
		$fileId = $this->_fileId;
		$fileTypeId = $this->getData('fileType');

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);
		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$bookFileType = $bookFileTypeDao->getById($fileTypeId);

		if ($monographFileManager->uploadedFileExists('submissionFile')) {
			switch ($bookFileType->getCategory()) {
				case BOOK_FILE_CATEGORY_ARTWORK:
					$submissionFileId = $monographFileManager->uploadArtworkFile('submissionFile', $fileTypeId, $fileId);
					if (isset($submissionFileId)) {
						$artworkFileDao =& DAORegistry::getDAO('ArtworkFileDAO');
						$artworkFile =& $artworkFileDao->newDataObject();
						$artworkFile->setFileId($submissionFileId);
						$artworkFile->setMonographId($monographId);
						$artworkFileDao->insertObject($artworkFile);
					}
					break;
				default:
					$submissionFileId = $monographFileManager->uploadBookFile('submissionFile', $fileTypeId, $fileId);
					if (isset($submissionFileId)) {
						$monographDao =& DAORegistry::getDAO('MonographDAO');
						$monograph = $monographDao->getMonograph($monographId);
						$monograph->setSubmissionFileId($submissionFileId);
						$monographDao->updateMonograph($monograph);
					}
					break;
			}
		}

		return isset($submissionFileId) ? $submissionFileId : false;
	}

	/**
	 * Check the filename against existing files in the submission
	 * A criterion of 70% of characters matching is used to determine the return value
	 * @param $fileName string
	 * @param $monographId int
	 * @return int MonographFile Id
	 */
	function _checkForSimilarFilenames($fileName, $monographId) {
		$criterion = 70;

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($monographId);

		$matchedFileId = null;
		foreach ($monographFiles as $monographFile) {
			$matchedChars = similar_text($fileName, $monographFile->getOriginalFileName(), &$p);
			if($p > $criterion) {
				$matchedFileId = $monographFile->getFileId();
				$criterion = $p; // Reset criterion to this comparison's precentage to see if there are better matches
			}
		}

		return $matchedFileId;
	}
}

?>
