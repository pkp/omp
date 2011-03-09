<?php

/**
 * @filecontrollers/grid/files/attachment/ReviewerReviewAttachmentsGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewAttachmentsGridHandler
 * @ingroup controllers_grid_files_attachment
 *
 * @brief Handle file grid requests.
 */

import('controllers.grid.files.attachment.ReviewAttachmentsGridHandler');

class ReviewerReviewAttachmentsGridHandler extends ReviewAttachmentsGridHandler {
	/** @var int */
	var $_reviewId;

	/**
	 * Constructor
	 */
	function ReviewerReviewAttachmentsGridHandler() {
		parent::ReviewAttachmentsGridHandler(FILE_GRID_ADD|FILE_GRID_DOWNLOAD_ALL);
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_REVIEWER),
			array(
				'fetchGrid', 'fetchRow', 'finishFileSubmission', 'addFile', 'displayFileUploadForm',
				'uploadFile', 'confirmRevision', 'editMetadata', 'saveMetadata', 'downloadFile',
				'downloadAllFiles', 'deleteFile'
			)
		);
	}

	//
	// Getters / Setters
	//

	/**
	 * Set the review Id
	 * @param $reviewId int
	 */
	function setReviewId($reviewId) {
	    $this->_reviewId = $reviewId;
	}

	/**
	 * Get the review Id
	 * @return int
	 */
	function getReviewId() {
	    return $this->_reviewId;
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		// FIXME: Must be replaced with a review attachment level policy, see #6200.
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// FIXME: Must be replaced with an object from the authorized context, see #6200.
		$reviewId = (int)$request->getUserVar('reviewId');
		assert(!empty($reviewId));
		$this->setReviewId($reviewId);

		// Load grid data.
		$this->loadMonographFiles();

		$additionalActionArgs = array('reviewId' => $this->getReviewId());
		parent::initialize($request, $additionalActionArgs);
	}

	/*
	 * @see SubmissionFilesGridHandler::loadMonographFiles()
	 */
	function loadMonographFiles() {
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getAllRevisionsByAssocId(
			ASSOC_TYPE_REVIEW_ASSIGNMENT, $this->getReviewId(), MONOGRAPH_FILE_REVIEW
		);
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] = $monographFile;
		}
		$this->setData($rowData);
	}

	/**
	 * @see SubmissionFilesGridHandler::uploadFile()
	 */
	function uploadFile($args, &$request) {
		$fileModifyCallback = array($this, 'setFileReviewId');
		return parent::uploadFile($args, $request, $fileModifyCallback);
	}

	/**
	 * Callback to set the assoc_type/assoc_id for the attachment file
	 * @param $monographFile MonographFile
	 */
	function setFileReviewId(&$monographFile) {
		$monographFile->setAssocType(ASSOC_TYPE_REVIEW_ASSIGNMENT);
		$monographFile->setAssocId($this->getReviewId());

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFileDao->updateObject($monographFile);
	}

}