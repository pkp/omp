<?php

/**
 * @file controllers/grid/files/review/ReviewFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFilesGridDataProvider
 * @ingroup controllers_grid_files_review
 *
 * @brief Provide access to review file data for review file grids.
 */


import('controllers.grid.files.review.ReviewGridDataProvider');

class ReviewFilesGridDataProvider extends ReviewGridDataProvider {
	/**
	 * Constructor
	 */
	function ReviewFilesGridDataProvider() {
		parent::ReviewGridDataProvider(MONOGRAPH_FILE_REVIEW);
	}


	//
	// Override methods from ReviewGridDataProvider
	//
	function &loadData() {
		$data =& ReviewGridDataProvider::loadData();

		// Hide rows that have been marked not viewable using the
		// Manage Review Files dialog
		foreach ($data as $id => $fileData) {
			$file =& $fileData['submissionFile'];
			if (!$file->getViewable()) unset($data[$id]);
			unset($file);
		}

		return $data;
	}

	//
	// Overridden public methods from FilesGridDataProvider
	//
	/**
	 * @see FilesGridDataProvider::getSelectAction()
	 */
	function &getSelectAction($request) {
		import('controllers.grid.files.fileList.linkAction.SelectReviewFilesLinkAction');
		$monograph =& $this->getMonograph();
		$selectAction = new SelectReviewFilesLinkAction(
			&$request, $monograph->getId(), $this->_getStageId(), $this->_getRound(),
			__('editor.monograph.review.manageReviewFiles')
		);
		return $selectAction;
	}

	/**
	 * @see FilesGridDataProvider::getAddFileAction()
	 */
	function &getAddFileAction($request) {
		import('controllers.api.file.linkAction.AddFileLinkAction');
		$monograph =& $this->getMonograph();
		$addFileAction = new AddFileLinkAction(
			$request, $monograph->getId(), $this->_getStageId(),
			$this->getUploaderRoles(), $this->_getFileStage(),
			null, null, $this->_getRound()
		);
		return $addFileAction;
	}

	//
	// Private helper methods
	//
	/**
	 * Get the review round number.
	 * @return integer
	 */
	function _getRound() {
		return $this->_round;
	}
}

?>
