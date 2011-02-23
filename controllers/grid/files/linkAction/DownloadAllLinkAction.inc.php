<?php
/**
 * @defgroup controllers_grid_files_linkAction
 */

/**
 * @file controllers/grid/files/linkAction/DownloadAllLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DownloadAllLinkAction
 * @ingroup controllers_grid_files_linkAction
 *
 * @brief An action to download all files in a submission file grid.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class DownloadAllLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The id of the monograph
	 *  from which to download files.
	 */
	function DownloadAllLinkAction(&$request, $monographId) {
		// Create the action arguments array.
		$actionArgs = array('monographId' => $monographId);

		// Instantiate the redirect action request.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.RedirectAction');
		$redirectRequest = new RedirectAction($router->url($request, null, null, 'downloadAllFiles', null, $actionArgs));

		// Configure the link action.
		parent::LinkAction('downloadAll', $redirectRequest, 'submission.files.downloadAll', 'getPackage');
	}
}