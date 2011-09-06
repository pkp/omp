<?php

/**
 * @file controllers/grid/files/attachment/ReviewerReviewAttachmentGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
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
	function ReviewerReviewAttachmentGridDataProvider() {
		parent::SubmissionFilesGridDataProvider(MONOGRAPH_FILE_REVIEW_ATTACHMENT);
	}


	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.internal.ReviewAssignmentRequiredPolicy');

		// Need to use the reviewId because this grid can either be
		// viewed by the reviewer (in which case, we could do a
		// $request->getUser()->getId() or by the editor when reading
		// the review. The following covers both cases...
		$assocType = (int) $request->getUserVar('assocType');
		$assocId = (int) $request->getUserVar('assocId');
		if ($assocType && $assocId) {
			// Viewing from a Reviewer perspective.
			assert($assocType == ASSOC_TYPE_REVIEW_ASSIGNMENT);

			$this->setUploaderRoles($roleAssignments);
			import('classes.security.authorization.OmpReviewStageAccessPolicy');
			$authorizationPolicy = new OmpReviewStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $request->getUserVar('stageId'));
			$paramName = 'assocId';
		} else {
			// Viewing from a press role perspective.
			$authorizationPolicy = parent::getAuthorizationPolicy($request, $args, $roleAssignments);
			$paramName = 'reviewId';
		}

		$authorizationPolicy->addPolicy(new ReviewAssignmentRequiredPolicy($request, $args, $paramName));

		return $authorizationPolicy;
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		return array_merge(
			parent::getRequestArgs(),
			array(
				'assocType' => ASSOC_TYPE_REVIEW_ASSIGNMENT,
				'assocId' => $this->_getReviewId()
			)
		);
	}

	/**
	 * @see GridDataProvider::loadData()
	 */
	function &loadData() {
		// Get all review files assigned to this submission.
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
		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT);
		return $reviewAssignment->getId();
	}
}

?>
