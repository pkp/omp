<?php

/**
 * @file controllers/grid/files/attachment/ReviewerReviewAttachmentGridDataProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewGridDataProvider
 * @ingroup controllers_grid_files_attachment
 *
 * @brief Provide the reviewers access to their own review attachments data for grids.
 */


import('controllers.grid.files.SubmissionFilesGridDataProvider');

class ReviewerReviewAttachmentGridDataProvider extends SubmissionFilesGridDataProvider {
	/** @var integer */
	var $_reviewId;

	/**
	 * Constructor
	 */
	function ReviewerReviewAttachmentGridDataProvider($stageId) {
		parent::SubmissionFilesGridDataProvider($stageId, MONOGRAPH_FILE_REVIEW_ATTACHMENT);
	}


	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		// FIXME: #6199 need to use the reviewId because this grid can either be viewed by the
		// reviewer (in which case, we could do a $request->getUser()->getId() or by the editor when reading
		// the review. The following covers both cases...
		$assocType = (int) $request->getUserVar('assocType');
		$assocId = (int) $request->getUserVar('assocId');
		if ($assocType && $assocId) {
			assert($assocType == ASSOC_TYPE_REVIEW_ASSIGNMENT);
			$reviewId = $assocId;
		} else {
			$reviewId = (int) $request->getUserVar('reviewId');
		}

		// Ensure the review id is valid
		// FIXME: #6199 could also check the user is the reviewerId or the user is allowed to view the review.
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId); /* @var $reviewAssignment ReviewAssignment */
		assert(isset($reviewAssignment));

		$this->_reviewId = (int) $reviewAssignment->getId();
		return parent::getAuthorizationPolicy($request, $args, $roleAssignments);
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		return array_merge(parent::getRequestArgs(), array('assocType' => ASSOC_TYPE_REVIEW_ASSIGNMENT,
													 		'assocId' => $this->_getReviewId()));
	}

	/**
	 * @see GridDataProvider::loadData()
	 */
	function &loadData() {
		// Get all review files assigned to this submission.
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getAllRevisionsByAssocId(
			ASSOC_TYPE_REVIEW_ASSIGNMENT, $this->_getReviewId(), $this->_getFileStage()
		);
		return $this->prepareSubmissionFileData($monographFiles);
	}

	//
	// Overridden public methods from FilesGridDataProvider
	//
	/**
	 * @see FilesGridDataProvider::getAddFileAction()
	 */
	function &getAddFileAction($request) {
		import('controllers.api.file.linkAction.AddFileLinkAction');
		$monograph =& $this->getMonograph();
		$addFileAction = new AddFileLinkAction(
			$request, $monograph->getId(), $this->_getStageId(),
			$this->getUploaderRoles(), $this->_getFileStage(),
			ASSOC_TYPE_REVIEW_ASSIGNMENT, $this->_getReviewId()
		);
		return $addFileAction;
	}
	//
	// Private helper methods
	//
	/**
	 * Get the review id.
	 * @return integer
	 */
	function _getReviewId() {
		return $this->_reviewId;
	}
}

?>
