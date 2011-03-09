<?php

/**
 * @filecontrollers/grid/files/attachment/ReviewAttachmentsGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewAttachmentsGridHandler
 * @ingroup controllers_grid_files_attachment
 *
 * @brief Base handler for review attachment grids
 */

// Import submission files grid base class
import('controllers.grid.files.SubmissionFilesGridHandler');

class ReviewAttachmentsGridHandler extends SubmissionFilesGridHandler {
	/**
	 * Constructor
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function ReviewAttachmentsGridHandler($capabilities) {
		parent::SubmissionFilesGridHandler(MONOGRAPH_FILE_REVIEW, $capabilities);
	}

	//
	// Implement template methods from PKPHandler
	//

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 * @param $additionalActionArgs array
	 */
	function initialize(&$request, $additionalActionArgs = array()) {
		$this->setTitle('grid.reviewAttachments.title');

		// Set the select checkbox name to avoid namespace collisions
		$this->setSelectName('reviewAttachments');

		// Load monograph files.
		$this->loadMonographFiles();

		import('controllers.grid.files.SubmissionFilesGridCellProvider');
		$cellProvider =& new SubmissionFilesGridCellProvider();
		parent::initialize($request, $cellProvider, $additionalActionArgs);
	}
}