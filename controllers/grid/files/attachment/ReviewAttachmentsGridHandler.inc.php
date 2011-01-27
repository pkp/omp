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
 * @brief Handle review attachments grid requests.
 */

// Import submission files grid base class
import('controllers.grid.files.SubmissionFilesGridHandler');

class ReviewAttachmentsGridHandler extends SubmissionFilesGridHandler {
	/**
	 * Constructor
	 */
	function ReviewAttachmentsGridHandler($canAdd = true, $isSelectable = false, $canDownloadAll = true) {
		parent::SubmissionFilesGridHandler(MONOGRAPH_FILE_ATTACHMENT, $canAdd, false, $isSelectable, $canDownloadAll, true);
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
		$this->setSelectName('attachments');

		// Load monograph files.
		$monograph =& $this->getMonograph();
		$this->loadMonographFiles($monograph);

		import('controllers.grid.files.SubmissionFilesGridCellProvider');
		$cellProvider =& new SubmissionFilesGridCellProvider();
		parent::initialize($request, $cellProvider, $additionalActionArgs);
	}
}