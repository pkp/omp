<?php

/**
 * @file controllers/grid/files/proof/form/ManageProofFilesForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageProofFilesForm
 * @ingroup controllers_grid_files_proof
 *
 * @brief Form to add files to the proof files grid
 */

import('lib.pkp.controllers.grid.files.form.ManageSubmissionFilesForm');

class ManageProofFilesForm extends ManageSubmissionFilesForm {

	/** @var int Publication format ID. */
	var $_publicationFormatId;

	/**
	 * Constructor.
	 * @param $submissionId int Submission ID.
	 * @param $publicationFormatId int Publication format ID.
	 */
	function ManageProofFilesForm($submissionId, $publicationFormatId) {
		parent::ManageSubmissionFilesForm($submissionId, 'controllers/grid/files/proof/manageProofFiles.tpl');
		$this->_publicationFormatId = $publicationFormatId;
	}


	//
	// Overridden template methods
	//
	/**
	 * Fetch the form contents.
	 * @param $request PKPRequest
	 * @return string Form contents
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('publicationFormatId', $this->_publicationFormatId);
		return parent::fetch($request);
	}

	/**
	 * Save selection of proof files
	 * @param $args array
	 * @param $request PKPRequest
	 * @return array a list of all submission files marked as "proof".
	 */
	function execute($args, $request, $stageSubmissionFiles) {
		$selectedFiles = (array)$this->getData('selectedFiles');
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$submissionFiles = $submissionFileDao->getLatestRevisions($this->getSubmissionId());

		foreach ($submissionFiles as $submissionFile) {
			// Get the viewable flag value.
			$isViewable = in_array(
				$submissionFile->getFileId(),
				$selectedFiles);

			if ($isViewable) {
				// Import a file from a previous stage.
				$context = $request->getContext();
				if ($isViewable) $this->importFile($context, $submissionFile, SUBMISSION_FILE_PROOF);
			}
		}
	}

	/**
	 * @copydoc ManageSubmissionFilesForm::importFile()
	 */
	protected function importFile($context, $submissionFile, $fileStage) {
		$newSubmissionFile = parent::importFile($context, $submissionFile, $fileStage);

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($this->_publicationFormatId, $this->getSubmissionId(), $context->getId());

		$newSubmissionFile->setAssocType(ASSOC_TYPE_PUBLICATION_FORMAT);
		$newSubmissionFile->setAssocId($publicationFormat->getId());
		$newSubmissionFile->setFileStage(SUBMISSION_FILE_PROOF);

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$submissionFileDao->updateObject($newSubmissionFile);
		return $newSubmissionFile;
	}
}

?>
