<?php

/**
 * @file controllers/grid/files/review/SubmissionFilesCategoryGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesCategoryDataProvider
 * @ingroup controllers_grid_files_review
 *
 * @brief Provide access to submission files data for category grids.
 */


import('lib.pkp.classes.controllers.grid.CategoryGridDataProvider');

class SubmissionFilesCategoryGridDataProvider extends CategoryGridDataProvider {

	/** @var $_submissionFileGridDataProvider SubmissionFilesGridDataProvider */
	var $_submissionFilesGridDataProvider;

	/** @var $_monographFiles array */
	var $_monographFiles;


	/**
	 * Constructor
	 * @param $fileStage int The current file stage that the grid is handling
	 * (others file stages could be shown activating the grid filter, but this
	 * is the file stage that will be used to bring files from other stages, upload
	 * new file, etc).
	 * @param $dataProviderInitParams array Other parameters to initiate the grid
	 * data provider that this category grid data provider will use to implement
	 * common behaviours and data.
	 */
	function SubmissionFilesCategoryGridDataProvider($fileStage, $dataProviderInitParams = null) {
		$gridDataProvider =& $this->initGridDataProvider($fileStage, $dataProviderInitParams);
		$this->setGridDataProvider($gridDataProvider);
	}


	//
	// Getters and setters
	//
	/**
	 * Return the grid data provider being used by this category
	 * grid data provider to implement common behaviours and data.
	 */
	function &getGridDataProvider() {
		return $this->_submissionFilesGridDataProvider;
	}

	/**
	 * Set the grid data provider being used by this category grid
	 * data provider to implement common behaviours and data.
	 * @param $gridDataProvider SubmissionFilesGridDataProvider
	 */
	function setGridDataProvider(&$gridDataProvider) {
		if (is_a($gridDataProvider, 'SubmissionFilesGridDataProvider')) {
			$this->_submissionFilesGridDataProvider =& $gridDataProvider;
		} else {
			assert(false);
		}
	}

	/**
	 * @see SubmissionFilesGridDataProvider::getFileStage()
	 */
	function setStageId($stageId) {
		$this->_submissionFilesGridDataProvider->setStageId($stageId);
	}

	/**
	 * @see SubmissionFilesGridDataProvider::getFileStage()
	 */
	function getFileStage() {
		return $this->_submissionFilesGridDataProvider->getFileStage();
	}


	//
	// Overriden methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::setAuthorizedContext()
	 */
	function setAuthorizedContext(&$authorizedContext) {
		// We need to pass the authorized context object to the grid data provider
		// object that is being used by this class.
		$this->_submissionFilesGridDataProvider->setAuthorizedContext($authorizedContext);

		parent::setAuthorizedContext($authorizedContext);
	}


	//
	// Implement template methods from CategoryGridDataProvider
	//
	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		// Get the submission files grid data provider authorization policy.
		$policy = $this->_submissionFilesGridDataProvider->getAuthorizationPolicy($request, $args, $roleAssignments);

		return $policy;
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		return $this->_submissionFilesGridDataProvider->getRequestArgs();
	}

	/**
	 * @see GridDataProvider::loadData()
	 */
	function loadData() {
		// Return only the user accessible workflow stages.
		return array_keys($this->getAuthorizedContextObject(ASSOC_TYPE_ACCESSIBLE_WORKFLOW_STAGES));
	}

	/**
	 * @see CategoryGridDataProvider::getCategoryData()
	 */
	function &getCategoryData($categoryDataElement, $reviewRound = null) {
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monograph =& $this->_submissionFilesGridDataProvider->getMonograph();
		$stageId = $categoryDataElement;
		$fileStage = $this->_getFileStageByStageId($stageId);
		$stageMonographFiles = null;

		// For review stages, get the revisions of the review round that user is currently accessing.
		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			if (is_null($reviewRound) || $reviewRound->getStageId() != $stageId) {
				$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
				$reviewRound =& $reviewRoundDao->getLastReviewRoundByMonographId($monograph->getId(), $stageId);
			}
			$stageMonographFiles =& $submissionFileDao->getRevisionsByReviewRound($reviewRound, $fileStage);
		} else {
			// Filter the passed workflow stage files.
			if (!$this->_monographFiles) {
				$this->_monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId());
			}
			$monographFiles =& $this->_monographFiles;
			$stageMonographFiles = array();
			foreach ($monographFiles as $key => $monographFile) {
				if ($monographFile->getFileStage() == $fileStage) {
					$stageMonographFiles[$key] =& $monographFile;
				}
				unset($monographFile);
			}
		}

		return $this->_submissionFilesGridDataProvider->prepareSubmissionFileData($stageMonographFiles);
	}

	//
	// Public methods
	//
	/**
	 * @see SubmissionFilesGridDataProvider::getAddFileAction()
	 */
	function &getAddFileAction($request) {
		return $this->_submissionFilesGridDataProvider->getAddFileAction($request);
	}


	//
	// Protected methods.
	//
	/**
	 * Init the grid data provider that this category grid data provider
	 * will use and return it. Override this to initiate another grid data provider.
	 * @param $fileStage int
	 * @param $initParams array (optional) The parameters to initiate the grid data provider.
	 * @return SubmissionFilesGridDataProvider
	 */
	function &initGridDataProvider($fileStage, $initParams = null) {
		// By default, this category grid data provider use the
		// SubmissionFilesGridDataProvider.
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		$gridDataProvider =& new SubmissionFilesGridDataProvider($fileStage);

		return $gridDataProvider;
	}


	//
	// Private helper methods.
	//
	/**
	 * Get the file stage using the passed stage id. This will define
	 * which file stage will be present on each workflow stage category
	 * of the grid.
	 * @param $stageId int
	 * @return int
	 */
	function _getFileStageByStageId($stageId) {
		switch($stageId) {
			case WORKFLOW_STAGE_ID_SUBMISSION:
				return MONOGRAPH_FILE_SUBMISSION;
				break;
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				return MONOGRAPH_FILE_REVIEW_FILE;
				break;
			case WORKFLOW_STAGE_ID_EDITING:
				return MONOGRAPH_FILE_FINAL;
				break;
			case WORKFLOW_STAGE_ID_PRODUCTION:
				return MONOGRAPH_FILE_PRODUCTION_READY;
				break;
			default:
				break;
		}
	}
}

?>
