<?php

/**
 * @file controllers/grid/files/SelectableSubmissionFileListCategoryGridHandler.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableSubmissionFileListCategoryGridHandler
 * @ingroup controllers_grid_files
 *
 * @brief Handle selectable submission file list category grid requests.
 */

// Import monograph file class which contains the SUBMISSION_FILE_* constants.
import('classes.monograph.MonographFile');

// Base class
import('lib.pkp.controllers.grid.files.PKPSelectableSubmissionFileListCategoryGridHandler');

class SelectableSubmissionFileListCategoryGridHandler extends PKPSelectableSubmissionFileListCategoryGridHandler {
	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SelectableSubmissionFileListCategoryGridHandler($dataProvider, $stageId, $capabilities) {
		parent::PKPSelectableSubmissionFileListCategoryGridHandler($dataProvider, $stageId, $capabilities);
	}

	/**
	 * @see GridHandler::isDataElementInCategorySelected()
	 */
	function isDataElementInCategorySelected($categoryDataId, &$gridDataElement) {
		$currentStageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$submissionFile = $gridDataElement['submissionFile'];

		// Check for special cases when the file needs to be unselected.
		$dataProvider = $this->getDataProvider();
		if ($dataProvider->getFileStage() != $submissionFile->getFileStage()) {
			return false;
		} elseif ($currentStageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $currentStageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			if ($currentStageId != $categoryDataId) {
				return false;
			}
		}

		// Passed the checks above. If viewable then select it.
		return $submissionFile->getViewable();
	}
}

?>
