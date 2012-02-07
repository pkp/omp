<?php

/**
 * @file controllers/grid/files/signoff/AuthorSignoffFilesGridRow.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSignoffFilesGridRow
 * @ingroup controllers_grid_files_fileSignoff
 *
 * @brief Author's view of files that they have been asked to signoff on.
 */

// Import grid base classes.
import('controllers.grid.files.SubmissionFilesGridRow');

class AuthorSignoffFilesGridRow extends SubmissionFilesGridRow {

	/**
	 * Constructor
	 * @param $stageId int
	 */
	function AuthorSignoffFilesGridRow($stageId) {
		// Author cannot delete, but may view notes.
		parent::SubmissionFilesGridRow(false, true, $stageId);
	}


	//
	// Overridden template methods from GridRow
	//
	/**
	 * @see GridRow::initialize
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Get this row's signoff
		$rowData =& $this->getData();
		$signoff =& $rowData['signoff'];
		$submissionFile =& $rowData['submissionFile'];

		// Get the current user
		$user =& $request->getUser();

		// Grid only displays current users' signoffs.
		assert($user->getId() == $signoff->getUserId());

		if (!$signoff->getDateCompleted()) {
			import('controllers.api.signoff.linkAction.AddSignoffFileLinkAction');
			$this->addAction(new AddSignoffFileLinkAction(
				$request, $submissionFile->getMonographId(),
				$this->getStageId(), $signoff->getSymbolic(), $signoff->getId(),
				__('submission.upload.signoff'), __('submission.upload.signoff')));
		}

		import('controllers.informationCenter.linkAction.FileInfoCenterLinkAction');
		$this->addAction(new FileInfoCenterLinkAction($request, $submissionFile, $this->getStageId()));
	}
}

?>
