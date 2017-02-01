<?php

/**
 * @file controllers/wizard/fileUpload/form/SubmissionFilesUploadBaseForm.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadBaseForm
 * @ingroup controllers_wizard_fileUpload_form
 *
 * @brief Form for adding/editing a submission file
 */


import('lib.pkp.controllers.wizard.fileUpload.form.PKPSubmissionFilesUploadBaseForm');

class SubmissionFilesUploadBaseForm extends PKPSubmissionFilesUploadBaseForm {

	/**
	 * Constructor.
	 * @param $request Request
	 * @param $template string
	 * @param $submissionId integer
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $fileStage integer
	 * @param $revisionOnly boolean
	 * @param $reviewRound ReviewRound
	 * @param $revisedFileId integer
	 */
	function __construct($request, $template, $submissionId, $stageId, $fileStage,
			$revisionOnly = false, $reviewRound = null, $revisedFileId = null, $assocType = null, $assocId = null) {
		parent::__construct($request, $template, $submissionId, $stageId, $fileStage,
				$revisionOnly, $reviewRound, $revisedFileId, $assocType, $assocId);
	}

	/**
	 * @see PKPSubmissionFilesUploadBaseForm::getSubmissionFiles() for the rest of this.  This function
	 * exists in this subclass for Monograph-specific submission files.
	 */
	function getSubmissionFiles() {
		if (is_null($this->_submissionFiles)) {
			if ($this->getStageId() == WORKFLOW_STAGE_ID_PRODUCTION &&
				$this->getAssocType() == ASSOC_TYPE_PUBLICATION_FORMAT && is_int($this->getAssocId())) {
				$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
				// Retrieve only the submission files with the same publication format.
				$this->_submissionFiles = $submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_PUBLICATION_FORMAT,
					$this->getAssocId(), $this->getData('submissionId'), $this->getData('fileStage'));
			} else {
				// Check with the parent class for things besides publication formats.
				$this->_submissionFiles = parent::getSubmissionFiles();
			}
		}

		return $this->_submissionFiles;
	}
}

?>
