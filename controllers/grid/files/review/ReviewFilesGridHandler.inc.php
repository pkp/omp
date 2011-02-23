<?php

/**
 * @file controllers/grid/files/review/ReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFilesGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Base handler for review stage grids
 */

// Import submission files grid base class
import('controllers.grid.files.SubmissionFilesGridHandler');

// import UI base classes
import('lib.pkp.classes.linkAction.request.AjaxAction');

class ReviewFilesGridHandler extends SubmissionFilesGridHandler {
	/** @var boolean */
	var $_canManage;

	/** @var int */
	var $_reviewType;

	/** @var int */
	var $_round;

	/**
	 * Constructor
	 */
	function ReviewFilesGridHandler($canAdd = false, $isSelectable = false, $canDownloadAll = false, $canManage = true) {
		$this->_canManage = $canManage;

		parent::SubmissionFilesGridHandler(MONOGRAPH_FILE_REVIEW, $canAdd, $isSelectable, $canDownloadAll);
	}

	//
	// Getters/Setters
	//
	/**
	 * Whether the grid allows file management (select existing files to add to grid)
	 * @return boolean
	 */
	function canManage() {
		return $this->_canManage;
	}

	/**
	 * Set the round number
	 * @param $round int
	 */
	function setRound($round) {
	    $this->_round = $round;
	}

	/**
	 * Get the round number
	 * @return int
	 */
	function getRound() {
	    return $this->_round;
	}

	/**
	 * Set the review type
	 * @param $reviewType int
	 */
	function setReviewType($reviewType) {
	    $this->_reviewType = $reviewType;
	}

	/**
	 * Get the review type
	 * @return int
	 */
	function getReviewType() {
	    return $this->_reviewType;
	}


	//
	// Implement template methods from PKPHandler
	//
	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Get the review round and review type (internal/external) from the request
		$reviewType = (int)$request->getUserVar('reviewType');
		$round = (int)$request->getUserVar('round');
		assert(!empty($reviewType) && !empty($round));
		$this->setReviewType($reviewType);
		$this->setRound($round);

		// Set a default title if it hasn't already been set by subclasses
		if(!$this->getTitle()) $this->setTitle('reviewer.monograph.reviewFiles');

		// Load additional locale components
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Load the monograph files to be displayed in the grid
		$this->loadMonographFiles();

		if($this->canManage()) {
			$monograph =& $this->getMonograph();
			$router =& $request->getRouter();
			$this->addAction(
					new LinkAction(
							'manageReviewFiles',
							new AjaxModal(
									$router->url($request, null, null, 'manageReviewFiles', null,
											array('monographId' => $monograph->getId(),
													'reviewType' => $this->getReviewType(),
													'round' => $this->getRound())),
									__('editor.submissionArchive.manageReviewFiles')),
							__('editor.submissionArchive.manageReviewFiles'),
							'add'));
		}

		import('controllers.grid.files.SubmissionFilesGridCellProvider');
		$cellProvider =& new SubmissionFilesGridCellProvider();
		$additionalActionArgs = array('reviewType' => $this->getReviewType(), 'round' => $this->getRound());
		parent::initialize($request, $cellProvider, $additionalActionArgs);

		$this->addColumn(new GridColumn('type', 'common.type', null, 'controllers/grid/gridCell.tpl', $cellProvider));
	}


	//
	// Protected methods
	//
	/**
	 * Select the files to load in the grid
	 * @see SubmissionFilesGridHandler::loadMonographFiles()
	 */
	function loadMonographFiles() {
		$monograph =& $this->getMonograph();

		// Grab the files that are currently set for the review
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */
		$monographFiles =& $reviewRoundDao->getReviewFilesByRound($monograph->getId());
		$rowData = array();
		if(isset($monographFiles[$this->getReviewType()][$this->getRound()])) {
			$rowData = $monographFiles[$this->getReviewType()][$this->getRound()];
		}
		$this->setData($rowData);
	}

	/**
	 * Show the form to allow the user to manage review files (bring in/take out files from submission stage to review stage)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function manageReviewFiles($args, &$request) {
		$monograph =& $this->getMonograph();

		import('controllers.grid.files.review.form.ManageReviewFilesForm');
		$manageReviewFilesForm = new ManageReviewFilesForm($monograph->getId(), $this->getReviewType(), $this->getRound());

		$manageReviewFilesForm->initData($args, $request);
		$json = new JSON(true, $manageReviewFilesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save 'manage review files' form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateReviewFiles($args, &$request) {
		$monograph =& $this->getMonograph();

		import('controllers.grid.files.review.form.ManageReviewFilesForm');
		$manageReviewFilesForm = new ManageReviewFilesForm($monograph->getId(), $this->getReviewType(), $this->getRound());

		$manageReviewFilesForm->readInputData();

		if ($manageReviewFilesForm->validate()) {
			$manageReviewFilesForm->execute($args, $request);

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSON(false);
			return $json->getString();
		}
	}
}