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

// Import basic grid layout.
import('controllers.grid.files.fileList.FileListGridHandler');

class ReviewFilesGridHandler extends FileListGridHandler {

	/**
	 * Constructor
	 * @see FileListGridHandler::FileListGridHandler()
	 */
	function ReviewFilesGridHandler($canAdd = false, $isSelectable = false, $canDownloadAll = false, $canManage = true) {
		import('controllers.grid.files.review.ReviewFilesGridDataProvider');
		$dataProvider = new ReviewFilesGridDataProvider();
		parent::FileListGridHandler($dataProvider, $canAdd, $isSelectable, $canDownloadAll, $canManage);
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the round number
	 * @return int
	 */
	function getRound() {
	    return $this->getRequestArg('round');
	}

	/**
	 * Get the review type
	 * @return int
	 */
	function getReviewType() {
		return $this->getRequestArg('reviewType');
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		// Set a default title if it hasn't already been set by subclasses
		if(!$this->getTitle()) $this->setTitle('reviewer.monograph.reviewFiles');

		// Load additional locale components
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR));

		parent::initialize($request);
	}


	//
	// Public handler methods
	//
	/**
	 * Show the form to allow the user to select review files
	 * (bring in/take out files from submission stage to review stage)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function selectFiles($args, &$request) {
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