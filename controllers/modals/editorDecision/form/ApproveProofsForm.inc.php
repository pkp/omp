<?php

/**
 * @file controllers/modals/editorDecision/form/ApproveProofsForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApproveProofsForm
 * @ingroup controllers_modals_editorDecision_form
 *
 * @brief Form for approving proofs
 */

import('lib.pkp.classes.form.Form');

class ApproveProofsForm extends Form {
	/** @var $seriesEditorSubmission SeriesEditorSubmission */
	var $seriesEditorSubmission;
	var $publicationFormat;

	/**
	 * Constructor.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 */
	function ApproveProofsForm(&$seriesEditorSubmission, $publicationFormatId) {
		parent::Form(
			'controllers/modals/editorDecision/form/approveProofsForm.tpl'
		);
		$this->seriesEditorSubmission =& $seriesEditorSubmission;

		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$this->publicationFormat =& $publicationFormatDao->getById($publicationFormatId, $seriesEditorSubmission->getId());
		assert($this->publicationFormat);
	}

	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData($args, &$request) {
		return parent::initData($args, $request);
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('publicationFormatId', $this->publicationFormat->getId());
		$templateMgr->assign('monographId', $this->seriesEditorSubmission->getId());
		$templateMgr->assign('stageId', WORKFLOW_STAGE_ID_PRODUCTION);
		return parent::fetch($request);
	}

	/**
	 * @see Form::readInputData
	 */
	function readInputData() {
		$this->readUserVars(array('selectedFiles'));
	}

	/**
	 * @see Form::execute()
	 */
	function execute($args, &$request) {
		$selectedFileIds = (array) $this->getData('selectedFiles');
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		import('classes.monograph.MonographFile'); // File constants
		$stageMonographFiles =& $submissionFileDao->getLatestRevisions($this->seriesEditorSubmission->getId(), MONOGRAPH_FILE_PROOF);
		foreach ($stageMonographFiles as $monographFile) {
			$fileIdentification = $monographFile->getFileId() . '-' . $monographFile->getRevision();
			if (in_array($fileIdentification, $selectedFileIds) && !$monographFile->getViewable()) {
				// Expose the file to readers (e.g. via e-commerce)
				$monographFile->setViewable(true);
				$submissionFileDao->updateObject($monographFile);
			} elseif (!in_array($fileIdentification, $selectedFileIds) && $monographFile->getViewable()) {
				// No longer expose the file to readers
				$monographFile->setViewable(false);
				$submissionFileDao->updateObject($monographFile);
			}
		}
	}
}

?>
