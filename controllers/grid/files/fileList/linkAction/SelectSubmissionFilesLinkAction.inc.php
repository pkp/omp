<?php
/**
 * @file controllers/grid/files/fileList/linkAction/SelectSubmissionFilesLinkAction.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectSubmissionFilesLinkAction
 * @ingroup controllers_grid_files_fileList_linkAction
 *
 * @brief An action to open up the modal that allows users to select submission
 *  files from a file list grid.
 */

import('lib.pkp.controllers.grid.files.fileList.linkAction.SelectFilesLinkAction');

class SelectSubmissionFilesLinkAction extends SelectFilesLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The id of the monograph
	 *  from which to select files.
	 */
	function __construct($request, $monographId, $actionLabel) {
		$actionArgs = array('submissionId' => $monographId);

		parent::__construct($request, $actionArgs, $actionLabel);
	}
}

?>
