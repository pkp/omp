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

// $Id: AuthorSubmitStep3Form.inc.php,v 1.9 2009/10/14 19:25:59 tylerl Exp $


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
			array('bookFileType', 'selectedFiles')
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

		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');


		$bookFileTypes =& $bookFileTypeDao->getEnabledByPressId($this->monograph->getPressId());
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
			$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
			$bookFileTypeId = $this->getData('bookFileType');

			$bookFileType =& $bookFileTypeDao->getById($bookFileTypeId);

			$submissionFileId = $manuscriptFileManager->uploadBookFile(
								$fileName, 
								$bookFileType->getDesignation($this->getFormLocale()), 
								$bookFileType->getName($this->getFormLocale())
							);
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
