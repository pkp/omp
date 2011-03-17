<?php
/**
 * @file controllers/grid/files/fileList/linkAction/SelectFilesLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectFilesLinkAction
 * @ingroup controllers_grid_files_fileList_linkAction
 *
 * @brief An abstract base action for actions to open up a modal that allows users to
 *  select files from a file list grid.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class SelectFilesLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $actionArgs array The parameters required by the
	 *  link action target to identify a list of files.
	 * @param $actionLabel string The localized label of the link action.
	 */
	function SelectFilesLinkAction(&$request, $actionArgs, $actionLabel) {
		// Create an ajax action request that'll contain
		// the file selection grid.
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$router =& $request->getRouter();
		$ajaxModal = new AjaxModal(
				$router->url($request, null, null, 'selectFiles', null, $actionArgs),
				$actionLabel);

		// Configure the link action.
		parent::LinkAction('selectFiles', $ajaxModal, $actionLabel, 'add');
	}
}

?>
