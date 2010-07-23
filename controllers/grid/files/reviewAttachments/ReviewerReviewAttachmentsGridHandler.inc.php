<?php

/**
 * @filecontrollers/grid/files/reviewAttachments/ReviewAttachmentsGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileGridHandler
 * @ingroup controllers_grid_file
 *
 * @brief Handle file grid requests.
 */

import('controllers.grid.files.reviewAttachments.ReviewAttachmentsGridRow');
import('ontrollers.grid.files.reviewAttachments.ReviewAttachmentsGridHandler');

class ReviewerReviewAttachmentsGridHandler extends ReviewAttachmentsGridHandler {
	/**
	 * Constructor
	 */
	function ReviewAttachmentsGridHandler() {
		parent::GridHandler();
		// FIXME: #5600 - Distribute access differently to reviewers and editor roles
		//$this->addRoleAssignment(array(ROLE_ID_REVIEWER, ROLE_ID_PRESS_MANAGER, ROLE_ID_EDITOR),
		//		array('fetchGrid', 'addFile', 'editFile', 'saveFile', 'deleteFile', 'returnFileRow', 'downloadFile'));
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		// FIXME: #5600 - Distribute access differently to reviewers and editor roles
		/*import('classes.security.authorization.OmpWorkflowStagePolicy');
		$this->addPolicy(new OmpWorkflowStagePolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);*/
		return true;
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$reviewId = $request->getUserVar('reviewId');
		$monographId = $request->getUserVar('monographId');

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		if (!$reviewId && $monographId ) {
			$monographFiles =& $monographFileDao->getByMonographId($monographId, MonographFileManager::typeToPath(MONOGRAPH_FILE_REVIEW));
		} else {
			$monographFiles =& $monographFileDao->getMonographFilesByAssocId($reviewId, MONOGRAPH_FILE_REVIEW);
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
	 */
	function editFile(&$args, &$request) {
		$fileId = $request->getUserVar('rowId');
		$reviewId = $request->getUserVar('reviewId');

		import('controllers.grid.files.reviewAttachments.form.ReviewerReviewAttachmentsForm');
		$reviewAttachmentsForm = new ReviewAttachmentsForm($reviewId, $fileId, $this->getId());

		if ($reviewAttachmentsForm->isLocaleResubmit()) {
			$reviewAttachmentsForm->readInputData();
		} else {
			$reviewAttachmentsForm->initData($args, $request);
		}
		$json = new JSON('true', $reviewAttachmentsForm->fetch($request));
		return $json->getString();
	}

	/**
	 * upload a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function saveFile(&$args, &$request) {
		$router =& $request->getRouter();
		$reviewId = $request->getUserVar('reviewId');

		import('controllers.grid.files.reviewAttachments.form.ReviewerReviewAttachmentsForm');
		$reviewAttachmentsForm = new ReviewAttachmentsForm($reviewId, null, $this->getId());
		$reviewAttachmentsForm->readInputData();

		if ($reviewAttachmentsForm->validate()) {
			$fileId = $reviewAttachmentsForm->execute($args, $request);

			$additionalAttributes = array(
				'deleteUrl' => $router->url($request, null, null, 'deleteFile', null, array('rowId' => $fileId)),
				'saveUrl' => $router->url($request, null, null, 'returnFileRow', null, array('rowId' => $fileId))
			);
			$json = new JSON('true', Locale::translate('submission.uploadSuccessful'), 'false', $fileId, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('common.uploadFailed'));
		}

		return '<textarea>' . $json->getString() . '</textarea>';
	}
}