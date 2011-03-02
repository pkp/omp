<?php
/**
 * @defgroup controllers_grid_files_fileList_linkAction
 */

/**
 * @file controllers/grid/files/fileList/linkAction/DownloadAllLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DownloadAllLinkAction
 * @ingroup controllers_grid_files_fileList_linkAction
 *
 * @brief An action to download all files in a submission file grid.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class DownloadAllLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 */
	function DownloadAllLinkAction(&$request, $actionArgs) {
		// Instantiate the redirect action request.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.RedirectAction');
		$redirectRequest = new RedirectAction($router->url($request, null, null, 'downloadAllFiles', null, $actionArgs));

		// Configure the link action.
		parent::LinkAction('downloadAll', $redirectRequest, __('submission.files.downloadAll'), 'getPackage');
	}
}