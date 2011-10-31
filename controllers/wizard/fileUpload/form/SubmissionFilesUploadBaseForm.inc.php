<?php

/**
 * @file controllers/wizard/fileUpload/form/SubmissionFilesUploadBaseForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadBaseForm
 * @ingroup controllers_wizard_fileUpload_form
 *
 * @brief Form for adding/editing a submission file
 */


import('lib.pkp.classes.form.Form');
import('classes.file.MonographFileManager');
import('classes.monograph.MonographFile');

class SubmissionFilesUploadBaseForm extends Form {

	/** @var integer */
	var $_stageId;

	/** @var ReviewRound */
	var $_reviewRound;

	/** @var array the monograph files for this monograph and file stage */
	var $_monographFiles;


	/**
	 * Constructor.
	 * @param $request Request
	 * @param $template string
	 * @param $monographId integer
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $fileStage integer
	 * @param $revisionOnly boolean
	 * @param $reviewRound ReviewRound
	 * @param $revisedFileId integer
	 */
	function SubmissionFilesUploadBaseForm(&$request, $template, $monographId, $stageId, $fileStage,
			$revisionOnly = false, &$reviewRound = null, $revisedFileId = null, $assocType = null, $assocId = null) {

		// Check the incoming parameters.
		if ( !is_numeric($monographId) || $monographId <= 0 ||
			!is_numeric($fileStage) || $fileStage <= 0 ||
			!is_numeric($stageId) || $stageId < 1 || $stageId > 5 ||
			isset($assocType) !== isset($assocId)) {
			fatalError('Invalid parameters!');
		}

		// Initialize class.
		parent::Form($template);
		$this->_stageId = $stageId;

		if ($reviewRound) {
			$this->_reviewRound =& $reviewRound;
		} else if ($assocType == ASSOC_TYPE_REVIEW_ASSIGNMENT && !$reviewRound) {
			// Get the review assignment object.
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
			$reviewAssignment =& $reviewAssignmentDao->getById((int) $assocId); /* @var $reviewAssignment ReviewAssignment */
			if ($reviewAssignment->getDateCompleted()) fatalError('Review already completed!');

			// Get the review round object.
			$reviewRoundDao =& DAORegistry::getDAO('ReviewRound');
			$this->_reviewRound =& $reviewRoundDao->getReviewRoundById($reviewAssignment->getReviewRoundId());
		} else if (!$assocType && !$reviewRound) {
			$reviewRound = null;
		}

		$this->setData('fileStage', (int)$fileStage);
		$this->setData('monographId', (int)$monographId);
		$this->setData('revisionOnly', (boolean)$revisionOnly);
		$this->setData('revisedFileId', $revisedFileId ? (int)$revisedFileId : null);
		$this->setData('assocType', $assocType ? (int)$assocType : null);
		$this->setData('assocId', $assocId ? (int)$assocId : null);

		// Add validators.
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Setters and Getters
	//
	/**
	 * Get the workflow stage id.
	 * @return integer
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the review round object (if any).
	 * @return ReviewRound
	 */
	function &getReviewRound() {
		return $this->_reviewRound;
	}

	/**
	 * Get the revised file id (if any).
	 * @return int the revised file id
	 */
	function getRevisedFileId() {
		return $this->getData('revisedFileId') ? (int)$this->getData('revisedFileId') : null;
	}

	/**
	 * Get the associated type
	 * @return integer
	 */
	function getAssocType() {
		return $this->getData('assocType');
	}

	/**
	 * Get the associated id.
	 * @return integer
	 */
	function getAssocId() {
		return $this->getData('assocId');
	}

	/**
	 * Get the monograph files belonging to the
	 * monograph and to the file stage.
	 * @return array a list of MonographFile instances.
	 */
	function &getMonographFiles() {
		if (is_null($this->_monographFiles)) {
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			if ($this->getStageId() == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $this->getStageId() == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
				// If we have a review stage id then we also expect a review round.
				if (!is_a($this->getReviewRound(), 'ReviewRound')) assert(false);

				// Can only upload submission files, review files, or review attachments.
				if (!in_array($this->getData('fileStage'), array(MONOGRAPH_FILE_SUBMISSION, MONOGRAPH_FILE_REVIEW_FILE, MONOGRAPH_FILE_REVIEW_ATTACHMENT, MONOGRAPH_FILE_REVIEW_REVISION))) fatalError('Invalid file stage!');

				// FIXME: #6830 hide the revision selector for review attachments
				// to make it easier for reviewers
				if ($this->getData('fileStage') == MONOGRAPH_FILE_REVIEW_ATTACHMENT) {
					$this->_monographFiles = array();
				} else {
					// Retrieve the monograph files for the given review round.
					$reviewRound =& $this->getReviewRound();
					$this->_monographFiles =& $submissionFileDao->getRevisionsByReviewRound($reviewRound);
				}
			} else {
				// Retrieve the monograph files for the given file stage.
				$this->_monographFiles =& $submissionFileDao->getLatestRevisions(
					$this->getData('monographId'), $this->getData('fileStage')
				);
			}
		}

		return $this->_monographFiles;
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		// Only Genre and revised file can be set in the form. All other
		// information is generated on our side.
		$this->readUserVars(array('revisedFileId'));
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		// Set the workflow stage.
		$this->setData('stageId', $this->getStageId());

		// Set the review round id, if any.
		$reviewRound =& $this->getReviewRound();
		if (is_a($reviewRound, 'ReviewRound')) {
			$this->setData('reviewRoundId', $reviewRound->getId());
		}

		// Retrieve the uploaded file (if any).
		$uploadedFile =& $this->getData('uploadedFile');

		// Initialize the list with files available for review.
		$monographFileOptions = array();
		$currentMonographFileGenres = array();

		// Go through all files and build a list of files available for review.
		$revisedFileId = $this->getRevisedFileId();
		$foundRevisedFile = false;
		$monographFiles =& $this->getMonographFiles();
		foreach ($monographFiles as $monographFile) {
			// The uploaded file must be excluded from the list of revisable files.
			if ($uploadedFile && $uploadedFile->getFileId() == $monographFile->getFileId()) continue;

			// Is this the revised file?
			if ($revisedFileId && $revisedFileId == $monographFile->getFileId()) {
				// This is the revised monograph file, so pass it's data on to the form.
				$this->setData('revisedFileName', $monographFile->getOriginalFileName());
				$this->setData('genreId', $monographFile->getGenreId());
				$foundRevisedFile = true;
			}

			// Create an entry in the list of existing files which
			// the user can select from in case he chooses to upload
			// a revision.
			$fileName = $monographFile->getLocalizedName() != '' ? $monographFile->getLocalizedName() : __('common.untitled');
			if ($monographFile->getRevision() > 1) $fileName .= ' (' . $monographFile->getRevision() . ')';
			$monographFileOptions[$monographFile->getFileId()] = $fileName;
			$currentMonographFileGenres[$monographFile->getFileId()] = $monographFile->getGenreId();

			$lastMonographFile = $monographFile;
		}

		// If there is only one option for a file to review, and user must revise, do not show the selector.
		if (count($monographFileOptions) == 1 && $this->getData('revisionOnly')) {
			// There was only one option, use the last added monograph file
			$this->setData('revisedFileId', $lastMonographFile->getFileId());
			$this->setData('revisedFileName', $lastMonographFile->getOriginalFileName());
			$this->setData('genreId', $lastMonographFile->getGenreId());
		}

		// If this is not a "review only" form then add a default item.
		if (count($monographFileOptions) && !$this->getData('revisionOnly')) {
			$monographFileOptions = array('' => __('submission.upload.uploadNewFile')) + $monographFileOptions;
		}

		// Make sure that the revised file (if any) really was among
		// the retrieved monograph files in the current file stage.
		if ($revisedFileId && !$foundRevisedFile) fatalError('Invalid revised file id!');

		// Set the review file candidate data in the template.
		$this->setData('currentMonographFileGenres', $currentMonographFileGenres);
		$this->setData('monographFileOptions', $monographFileOptions);

		// Show ensuring a blind review link.
		$press =& $request->getPress();
		if ($press->getSetting('showEnsuringLink')) {
			import('lib.pkp.classes.linkAction.request.ConfirmationModal');
			$ensuringLink = new LinkAction(
				'addUser',
				new ConfirmationModal(
					__('review.blindPeerReview'),
					__('review.ensuringBlindReview')),
				__('review.ensuringBlindReview'));

			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('ensuringLink', $ensuringLink);
		}

		return parent::fetch($request);
	}
}

?>
