<?php

/**
 * @file classes/author/form/submit/AuthorSubmitStep3Form.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep3Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 3 of author manuscript submission.
 */

// $Id$


import('author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep3Form extends AuthorSubmitForm {

	/**
	 * Constructor.
	 */
	function AuthorSubmitStep3Form($monograph) {
		parent::AuthorSubmitForm($monograph);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array('newBookFileTypeInfo', 'bookFileType', 'selectedFiles')
		);
	}

	function getHelpTopicId() {
		return 'submission.indexingAndMetadata';
	}
	function getTemplateFile() {
		return 'author/submit/step3.tpl';
	}
	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');


		$bookFileTypes =& $pressSettingsDao->getSetting($this->monograph->getPressId(), 'bookFileTypes', Locale::getLocale());
		$monographFiles =& $monographFileDao->getByMonographId($this->monograph->getMonographId(), 'submission');

		$templateMgr->assign_by_ref('bookFileTypes', $bookFileTypes);
		$templateMgr->assign_by_ref('submissionFiles', $monographFiles);

		parent::display();
	}

	/**
	 * Upload the book file.
	 * @param $fileName string
	 * @return boolean
	 */
	function uploadBookFile($fileName) {
		import('file.MonographFileManager');

		$manuscriptFileManager = new MonographFileManager($this->monograph->getMonographId());

		if ($manuscriptFileManager->uploadedFileExists($fileName)) {
			// upload new book file, overwriting previous if necessary
			$bookFileTypeInfo = $this->getData('newBookFileTypeInfo');
			$bookFileType = $this->getData('bookFileType');
			$bookFileTypeInfo = $bookFileTypeInfo[$bookFileType];
			$submissionFileId = $manuscriptFileManager->uploadBookFile(
								$fileName, 
								$bookFileTypeInfo['prefix'], 
								$bookFileTypeInfo['type']
							);

			$monographFileSettingsDao =& DAORegistry::getDAO('MonographFileSettingsDAO');
			$monographFileSettingsDao->updateSetting($submissionFileId, null, 'bookFileTypeName', $bookFileTypeInfo['type']);
			$monographFileSettingsDao->updateSetting($submissionFileId, null, 'bookFileTypeDescription', $bookFileTypeInfo['description']);
			$monographFileSettingsDao->updateSetting($submissionFileId, null, 'bookFileTypePrefix', $bookFileTypeInfo['prefix']);
		}

		return !isset($submissionFileId) ? false : true;
	}

	/**
	 * Save changes to monograph.
	 * @return int the monograph ID
	 */
	function execute() {
		// Update monograph
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $this->monograph;

		if ($monograph->getSubmissionProgress() <= $this->sequence->currentStep) {
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress($this->sequence->currentStep + 1);
			$monographDao->updateMonograph($monograph);
		}

		return $this->monograph->getMonographId();
	}
	function processEvents() {
		$eventProcessed = false;
		if (Request::getUserVar('uploadBookFile')) {
			$eventProcessed = true;
			$this->uploadBookFile('bookFile');
		} else if (Request::getUserVar('deleteSelectedFiles')) {
			$eventProcessed = true;
			$checkedFiles = $this->getData('selectedFiles');
			$monographFilesDao =& DAORegistry::getDAO('MonographFileDAO');
			import('file.MonographFileManager');
			$monographFileManager = new MonographFileManager($this->monograph->getMonographId());
			foreach ($checkedFiles as $fileId) {
				$monographFileManager->deleteFile($fileId);
				$monographFilesDao->deleteMonographFileById($fileId);
			}
		}
		return $eventProcessed;
	}
}

?>
