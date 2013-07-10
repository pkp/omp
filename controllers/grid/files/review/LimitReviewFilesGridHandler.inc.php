<?php

/**
 * @file controllers/grid/files/review/LimitReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LimitReviewFilesGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Display a selectable list of review files for the round to editors.
 *   Items in this list can be selected or deselected to give a specific subset
 *   to a particular reviewer.
 */

import('controllers.grid.files.fileList.SelectableFileListGridHandler');

class LimitReviewFilesGridHandler extends SelectableFileListGridHandler {
	/**
	 * Constructor
	 */
	function LimitReviewFilesGridHandler() {
		import('controllers.grid.files.review.ReviewGridDataProvider');
		// Pass in null stageId to be set in initialize from request var.
		parent::SelectableFileListGridHandler(
			new ReviewGridDataProvider(MONOGRAPH_FILE_REVIEW_FILE),
			null,
			FILE_GRID_VIEW_NOTES
		);

		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT),
			array('fetchGrid', 'fetchRow')
		);

		// Set the grid information.
		$this->setTitle('editor.submissionReview.restrictFiles.gridTitle');
		$this->setInstructions('editor.submissionReview.restrictFiles.gridDescription');
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		if ($reviewAssignmentId = $request->getUserVar('reviewAssignmentId')) {
			// If a review assignment ID is specified, preload the
			// checkboxes with the currently selected files. To do
			// this, we'll need the review assignment in the context.
			// Add the required policies:

			// 1) Review stage access policy (fetches monograph in context)
			import('classes.security.authorization.OmpReviewStageAccessPolicy');
			$this->addPolicy(new OmpReviewStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $request->getUserVar('stageId')));

			// 2) Review assignment
			import('classes.security.authorization.internal.ReviewAssignmentRequiredPolicy');
			$this->addPolicy(new ReviewAssignmentRequiredPolicy($request, $args, 'reviewAssignmentId', array('fetchGrid', 'fetchRow')));
		}
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Return the list of selected files.
	 * @param $submissionFiles array
	 */
	function getSelectedFileIds($submissionFiles) {
		$reviewAssignment = $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT);
		if (!$reviewAssignment) return array();

		$reviewFilesDao = DAORegistry::getDAO('ReviewFilesDAO');
		$returner = array();
		foreach ($submissionFiles as $submissionFileData) {
			$submissionFile = $submissionFileData['submissionFile'];
			if ($reviewFilesDao->check($reviewAssignment->getId(), $submissionFile->getFileId())) {
				$returner[] = $submissionFile->getFileId();
			}
		}

		return $returner;
	}
}

?>
