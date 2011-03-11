<?php

/**
 * @file controllers/wizard/fileUpload/form/SubmissionFilesUploadConfirmationForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadConfirmationForm
 * @ingroup controllers_wizard_fileUpload_form
 *
 * @brief Form for adding/editing a submission file
 */


import('controllers.wizard.fileUpload.form.SubmissionFilesUploadBaseForm');

class SubmissionFilesUploadConfirmationForm extends SubmissionFilesUploadBaseForm {
	/**
	 * Constructor.
	 * @param $request Request
	 * @param $monographId integer
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $fileStage integer
	 * @param $revisedFileId integer
	 * @param $uploadedFile integer
	 */
	function SubmissionFilesUploadConfirmationForm(&$request, $monographId, $stageId, $fileStage,
			$revisedFileId = null, $uploadedFile = null) {

		// Initialize class.
		parent::SubmissionFilesUploadBaseForm(
			$request, 'controllers/wizard/fileUpload/form/fileUploadConfirmationForm.tpl',
			$monographId, $stageId, $fileStage, false, null, null, $revisedFileId
		);

		if (is_a($uploadedFile, 'MonographFile')) {
			$this->setData('uploadedFile', $uploadedFile);
		}
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('uploadedFileId'));
		return parent::readInputData();
	}

	/**
	 * @see Form::execute()
	 * @return MonographFile if successful, otherwise null
	 */
	function &execute() {
		// Retrieve the file ids of the revised and the uploaded files.
		$revisedFileId = $this->getRevisedFileId();
		$uploadedFileId = (int)$this->getData('uploadedFileId');
		if (!($revisedFileId && $uploadedFileId)) fatalError('Invalid file ids!');
		if ($revisedFileId == $uploadedFileId) fatalError('The revised file id and the uploaded file id cannot be the same!');

		// Assign the new file as the latest revision of the old file.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographId = $this->getData('monographId');
		$fileStage = $this->getData('fileStage');
		$uploadedFile =& $submissionFileDao->setAsLatestRevision($revisedFileId, $uploadedFileId, $monographId, $fileStage);

		return $uploadedFile;
	}
}

?>
