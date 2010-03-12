<?php

/**
 * @file controllers/grid/submit/submissionFiles/form/SubmissionFilesUploadForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadForm
 * @ingroup controllers_grid_file_form
 *
 * @brief Form for adding/edditing a submission file
 */

import('form.Form');

class SubmissionFilesUploadForm extends Form {
	/** the id of the file being edited */
	var $_fileId; 

	/** the id of the file being edited */
	var $_monographId; 
	
	/**
	 * Constructor.
	 */
	function SubmissionFilesUploadForm($fileId = null, $monographId) {
		$this->_fileId = $fileId;		
		$this->_monographId = $monographId;		
		parent::Form('controllers/grid/submissionFiles/form/fileForm.tpl');

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
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
		foreach ($bookFileTypes as $bookFileType){
			$bookFileTypeId = $bookFileType->getId();
			$bookFileTypeList[$bookFileTypeId] = $bookFileType->getLocalizedName();
		}

		$this->_data['bookFileTypes'] = $bookFileTypeList;
	}

	/**
	 * Display
	 */
	function display() {
		parent::display();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('gridId', 'fileType'));
	}

	function uploadFile(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$fileTypeId = $this->getData('fileType');
		import("file.MonographFileManager");
		$monographFileManager = new MonographFileManager($monographId);

		if ($monographFileManager->uploadedFileExists('submissionFile')) {
			switch ($fileTypeId) {
				// FIXME: Need a way to determine artwork file type from user-specified artwork file types
				case 1:
					$submissionFileId = $monographFileManager->uploadArtworkFile('submissionFile', $fileTypeId);
					if (isset($submissionFileId)) {
						$artworkFileDao =& DAORegistry::getDAO('ArtworkFileDAO');
						$artworkFile =& $artworkFileDao->newDataObject();
						$artworkFile->setFileId($submissionFileId);
						$artworkFile->setMonographId($monographId);
						$artworkFileDao->insertObject($artworkFile);
					}					
					break;
				default:
					$submissionFileId = $monographFileManager->uploadBookFile('submissionFile', $fileTypeId);
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
}

?>
