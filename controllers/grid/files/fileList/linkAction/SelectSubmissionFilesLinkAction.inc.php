<?php
/**
 * @file controllers/grid/files/fileList/linkAction/SelectSubmissionFilesLinkAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectSubmissionFilesLinkAction
 * @ingroup controllers_grid_files_fileList_linkAction
 *
 * @brief An action to open up the modal that allows users to select submission
 *  files from a file list grid.
 */

import('controllers.grid.files.fileList.linkAction.SelectFilesLinkAction');

class SelectSubmissionFilesLinkAction extends SelectFilesLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The id of the monograph
	 *  from which to select files.
	 */
	function SelectSubmissionFilesLinkAction(&$request, $monographId, $actionLabel) {
		$actionArgs = array('monographId' => $monographId);

		parent::SelectFilesLinkAction($request, $actionArgs, $actionLabel);
	}
}

?>
