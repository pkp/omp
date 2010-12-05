<?php

/**
 * @filecontrollers/grid/files/reviewAttachments/ReviewerReviewAttachmentsGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewAttachmentsGridHandler
 * @ingroup controllers_grid_files_reviewAttachments
 *
 * @brief Handle file grid requests.
 */

import('controllers.grid.files.reviewAttachments.ReviewAttachmentsGridHandler');

class ReviewerReviewAttachmentsGridHandler extends ReviewAttachmentsGridHandler {
	/**
	 * Constructor
	 */
	function ReviewerReviewAttachmentsGridHandler() {
		parent::ReviewAttachmentsGridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_REVIEWER),
				array('fetchGrid', 'addFile', 'editFile', 'saveFile', 'deleteFile', 'returnFileRow', 'downloadFile'));
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
		parent::initialize($request);

		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$monographId = $monograph->getId();

		// FIXME: Must be replaced with an object from the authorized context, see #6200.
		$reviewId = (int) $request->getUserVar('reviewId');

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		if (!$reviewId && $monographId ) {
			$monographFiles =& $submissionFileDao->getLatestRevisions($monographId, MONOGRAPH_FILE_ATTACHMENT);
		} else {
			$monographFiles =& $submissionFileDao->getAllRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, $reviewId, MONOGRAPH_FILE_ATTACHMENT);
		}
		$this->setData($monographFiles);

		// Add grid-level actions
		if (!$this->getReadOnly()) {
			$router =& $request->getRouter();
			$this->addAction(
				new LinkAction(
					'addFile',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_APPEND,
					$router->url($request, null, null, 'addFile', null, array('reviewId' => $reviewId, 'monographId' => $monographId)),
					'grid.reviewAttachments.add'
				)
			);
		}
	}

	//
	// Public File Grid Actions
	//

	/**
	 * An action to add a new file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editFile($args, &$request) {
		// FIXME: Must be validated against an authorized object, see #6199.
		$fileId = (int) $request->getUserVar('rowId');
		// FIXME: Must be replaced with an object from the authorized context, see #6200.
		$reviewId = (int) $request->getUserVar('reviewId');

		import('controllers.grid.files.reviewAttachments.form.ReviewerReviewAttachmentsForm');
		$reviewAttachmentsForm = new ReviewerReviewAttachmentsForm($reviewId, $fileId, $this->getId());

		if ($reviewAttachmentsForm->isLocaleResubmit()) {
			$reviewAttachmentsForm->readInputData();
		} else {
			$reviewAttachmentsForm->initData($args, $request);
		}
		$json = new JSON('true', $reviewAttachmentsForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function saveFile($args, &$request) {
		// FIXME: Must be replaced with an object from the authorized context, see #6200.
		$reviewId = (int) $request->getUserVar('reviewId');

		import('controllers.grid.files.reviewAttachments.form.ReviewerReviewAttachmentsForm');
		$reviewAttachmentsForm = new ReviewerReviewAttachmentsForm($reviewId, null, $this->getId());
		$reviewAttachmentsForm->readInputData();

		if ($reviewAttachmentsForm->validate()) {
			$fileId = $reviewAttachmentsForm->execute($args, $request);
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

			$router =& $request->getRouter();
			$additionalAttributes = array(
				'deleteUrl' => $router->url($request, null, null, 'deleteFile', null, array('monographId' => $reviewAssignment->getSubmissionId(), 'rowId' => $fileId)),
				'saveUrl' => $router->url($request, null, null, 'returnFileRow', null, array('monographId' => $reviewAssignment->getSubmissionId(), 'rowId' => $fileId))
			);
			$json = new JSON('true', Locale::translate('submission.uploadSuccessful'), 'false', $fileId, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('common.uploadFailed'));
		}

		return $json->getString();
	}
}