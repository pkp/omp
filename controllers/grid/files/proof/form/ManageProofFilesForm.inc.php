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
	var $_representationId;

	/**
	 * Constructor.
	 * @param $submissionId int Submission ID.
	 * @param $representationId int Publication format ID.
	 */
	function ManageProofFilesForm($submissionId, $representationId) {
		parent::ManageSubmissionFilesForm($submissionId, 'controllers/grid/files/proof/manageProofFiles.tpl');
		$this->_representationId = $representationId;
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
		$templateMgr->assign('representationId', $this->_representationId);
		return parent::fetch($request);
	}

	/**
	 * @copydoc ManageSubmissionFilesForm::_fileExistsInStage
	 */
	protected function _fileExistsInStage($submissionFile, $stageSubmissionFiles) {
		return false;
	}


	/**
	 * @copydoc ManageSubmissionFilesForm::_importFile()
	 */
	protected function _importFile($context, $submissionFile, $fileStage) {
		$newSubmissionFile = parent::_importFile($context, $submissionFile, $fileStage);

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($this->_representationId, $this->getSubmissionId(), $context->getId());

		$newSubmissionFile->setAssocType(ASSOC_TYPE_PUBLICATION_FORMAT);
		$newSubmissionFile->setAssocId($publicationFormat->getId());
		$newSubmissionFile->setFileStage(SUBMISSION_FILE_PROOF);

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$submissionFileDao->updateObject($newSubmissionFile);
		return $newSubmissionFile;
	}
}

?>
