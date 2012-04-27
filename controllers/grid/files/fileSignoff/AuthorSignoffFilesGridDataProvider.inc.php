<?php

/**
 * @file controllers/grid/files/fileSignoff/AuthorSignoffFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSignoffFilesGridDataProvider
 * @ingroup controllers_grid_files_fileSignoff
 *
 * @brief Provide data for author signoff file grids.
 */


import('controllers.grid.files.SubmissionFilesGridDataProvider');

// Import file stage constants.
import('classes.monograph.MonographFile');

class AuthorSignoffFilesGridDataProvider extends SubmissionFilesGridDataProvider {

	/** @var int */
	var $_userId;

	/* @var string */
	var $_symbolic;

	/**
	 * Constructor
	 */
	function AuthorSignoffFilesGridDataProvider($symbolic, $stageId) {
		parent::SubmissionFilesGridDataProvider(MONOGRAPH_FILE_PROOF);

		$this->setStageId($stageId);
		$this->_symbolic = $symbolic;
	}

	/**
	 * Get symbolic.
	 * @return string
	 */
	function getSymbolic() {
		return $this->_symbolic;
	}

	/**
	 * Get user id.
	 * @return int
	 */
	function getUserId() {
		return $this->_userId;
	}

	/**
	 * Set user id.
	 * @param int
	 */
	function setUserId($userId) {
		$this->_userId = $userId;
	}

	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridHandler::loadData
	 */
	function &loadData() {
		$monographFileSignoffDao =& DAORegistry::getDAO('MonographFileSignoffDAO');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$signoffs =& $monographFileSignoffDao->getAllByMonograph($monograph->getId(), $this->getSymbolic(), $this->getUserId());

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		while ($signoff =& $signoffs->next()) {
			$monographFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId(), null, $monograph->getId());
			$preparedData[$signoff->getId()]['signoff'] =& $signoff;
			$preparedData[$signoff->getId()]['submissionFile'] =& $monographFile;
			unset($signoff, $monographFile);
		}

		return $preparedData;
	}

	//
	// Public methods.
	//
	/**
	 * Get link action to add a signoff file. If user has no incomplete
	 * signoffs, return false.
	 * @return mixed boolean or LinkAction
	 */
	function getAddSignoffFile(&$request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$signoffDao =& DAORegistry::getDAO('MonographFileSignoffDAO'); /* @var $signoffDao MonographFileSignoffDAO */
		$signoffFactory =& $signoffDao->getAllByMonograph($monograph->getId(), $this->getSymbolic(), $this->getUserId(), null, true);

		$action = false;
		if (!$signoffFactory->wasEmpty()) {
			import('controllers.api.signoff.linkAction.AddSignoffFileLinkAction');
			$action = new AddSignoffFileLinkAction(
									$request, $monograph->getId(),
									$this->getStageId(), $this->getSymbolic(), null,
									__('submission.upload.signoff'), __('submission.upload.signoff')
									);
		}

		return $action;
	}
}

?>
