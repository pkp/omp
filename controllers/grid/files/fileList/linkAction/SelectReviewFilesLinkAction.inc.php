<?php
/**
 * @file controllers/grid/files/fileList/linkAction/SelectReviewFilesLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectReviewFilesLinkAction
 * @ingroup controllers_grid_files_fileList_linkAction
 *
 * @brief An action to open up the modal that allows users to select review files
 *  from a file list grid.
 */

import('controllers.grid.files.fileList.linkAction.SelectFilesLinkAction');

class SelectReviewFilesLinkAction extends SelectFilesLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The id of the monograph
	 *  from which to select files.
	 * @param $stageId integer The review stage id required
	 *  to select review files.
	 * @param $round integer The review round from which to
	 *  select review files.
	 */
	function SelectReviewFilesLinkAction(&$request, $monographId, $stageId, $round, $actionLabel) {
		$actionArgs = array('monographId' => $monographId,
				'stageId' => $stageId, 'round' => $round);

		parent::SelectFilesLinkAction($request, $actionArgs, $actionLabel);
	}
}

?>
