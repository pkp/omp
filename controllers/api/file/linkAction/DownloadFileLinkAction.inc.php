<?php
/**
 * @file controllers/api/file/linkAction/DownloadFileLinkAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DownloadFileLinkAction
 * @ingroup controllers_api_file_linkAction
 *
 * @brief An action to download a file.
 */

import('controllers.api.file.linkAction.FileLinkAction');

class DownloadFileLinkAction extends FileLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographFile MonographFile the monograph file to
	 *  link to.
	 * @param $stageId int (optional)
	 */
	function DownloadFileLinkAction(&$request, &$monographFile, $stageId = null) {
		// Instantiate the redirect action request.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.RedirectAction');
		$redirectRequest = new RedirectAction(
			$router->url(
				$request, null, 'api.file.FileApiHandler', 'downloadFile',
				null, $this->getActionArgs($monographFile, $stageId)
			)
		);

		// Configure the file link action.
		parent::FileLinkAction(
			'downloadFile', $redirectRequest, $this->getLabel($monographFile),
			$monographFile->getDocumentType()
		);
	}

	/**
	 * Get the label for the file download action.
	 * @param $monographFile MonographFile
	 * @return string
	 */
	function getLabel(&$monographFile) {
		return $monographFile->getFileLabel();
	}
}

?>
