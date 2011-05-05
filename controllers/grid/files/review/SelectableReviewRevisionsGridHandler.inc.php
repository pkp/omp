<?php

/**
 * @file controllers/grid/files/review/SelectableReviewRevisionsGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableReviewRevisionsGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Display the file revisions authors have uploaded in a selectable grid.
 *   Used for selecting files to send to external review or copyediting.
 */

// import submission files grid specific classes
import('controllers.grid.files.review.ReviewRevisionsGridHandler');

class SelectableReviewRevisionsGridHandler extends ReviewRevisionsGridHandler {
	/**
	 * Constructor
	 */
	function SelectableReviewRevisionsGridHandler() {
		parent::ReviewRevisionsGridHandler(false, true);
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		$selectionPolicy =& $this->getSelectionPolicy($request, $args, $roleAssignments);
		if (!is_null($selectionPolicy)) {
			$this->addPolicy($selectionPolicy);
		}
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		// Add checkbox column to the grid.
		import('controllers.grid.files.fileList.FileSelectionGridColumn');
		$this->addColumn(new FileSelectionGridColumn($this->getSelectName()));

		parent::initialize($request);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		$requestArgs = array_merge(parent::getRequestArgs(), $this->getSelectionArgs());
		return $requestArgs;
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function &loadData($request, $filter) {
		// Go through the submission files and set their
		// "selected" flag.
		$submissionFiles =& parent::loadData($request, $filter);
		$selectedFiles =& $this->getSelectedFileIds($submissionFiles);
		foreach($submissionFiles as $fileId => $submissionFileData) {
			assert(isset($submissionFileData['submissionFile']));
			$monographFile =& $submissionFileData['submissionFile']; /* @var $monographFile MonographFile */
			$submissionFiles[$fileId]['selected'] = in_array(
				$monographFile->getFileIdAndRevision(),
				$selectedFiles
			);
			unset($monographFile);
		}

		return $submissionFiles;
	}


	//
	// Protected methods
	//
	/**
	 * Return an (optional) additional authorization policy
	 * to authorize the file selection.
	 * @param $request Request
	 * @param $args array
	 * @param $roleAssignments array
	 * @return PolicySet
	 */
	function getSelectionPolicy(&$request, $args, $roleAssignments) {
		// By default we do not require an additional policy.
		return null;
	}

	/**
	 * Request parameters that describe the selected
	 * files.
	 * @param $request Request
	 * @return array
	 */
	function getSelectionArgs() {
		// By default we do not add any additional
		// request parameters for the selection.
		return array();
	}

	/**
	 * Get the selected file IDs.
	 * @param $submissionFiles array Set of submission files to filter
	 * @return array
	 */
	function getSelectedFileIds($submissionFiles) {
		// By default we select nothing.
		return array();
	}

	/**
	 * Get the selection name.
	 * @return string
	 */
	function getSelectName() {
		return 'selectedAttachments';
	}
}

?>
