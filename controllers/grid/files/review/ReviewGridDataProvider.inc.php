<?php

/**
 * @file controllers/grid/files/review/ReviewGridDataProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewGridDataProvider
 * @ingroup controllers_grid_files_review
 *
 * @brief Provide access to review file data for grids.
 */


import('controllers.grid.files.SubmissionFilesGridDataProvider');

class ReviewGridDataProvider extends SubmissionFilesGridDataProvider {
	/** @var integer */
	var $_round;


	/**
	 * Constructor
	 */
	function ReviewGridDataProvider($stageId, $fileStageId) {
		parent::SubmissionFilesGridDataProvider($stageId, $fileStageId);
	}


	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		// FIXME: Need to authorize review round, see #6200.
		// Get the review round and review stage id (internal/external) from the request
		$round = $request->getUserVar('round');
		assert(!empty($round));
		$this->_round = (int)$round;

		return parent::getAuthorizationPolicy($request, $args, $roleAssignments);
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		return array_merge(parent::getRequestArgs(), array('round' => $this->_getRound()));
	}

	/**
	 * @see GridDataProvider::loadData()
	 */
	function &loadData() {
		// Get all review files assigned to this submission.
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getRevisionsByReviewRound(
			$monograph->getId(), $this->_getStageId(), $this->_getRound(), $this->_getFileStage()
		);
		return $this->prepareSubmissionFileData($monographFiles);
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
			__('editor.submissionArchive.manageReviewFiles')
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
