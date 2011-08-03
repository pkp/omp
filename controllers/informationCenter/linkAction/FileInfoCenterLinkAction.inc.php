<?php
/**
 * @defgroup controllers_informationCenter_linkAction
 */

/**
 * @file controllers/informationCenter/linkAction/FileInfoCenterLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileInfoCenterLinkAction
 * @ingroup controllers_informationCenter_linkAction
 *
 * @brief An action to open up the information center for a file.
 */

import('controllers.api.file.linkAction.FileLinkAction');

class FileInfoCenterLinkAction extends FileLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographFile MonographFile the monograph file
	 *  to show information about.
	 */
	function FileInfoCenterLinkAction(&$request, &$monographFile, $titleKey = 'grid.action.moreInformation', $icon = 'more_info') {
		// Instantiate the information center modal.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$ajaxModal = new AjaxModal(
			$router->url(
				$request, null,
				'informationCenter.FileInformationCenterHandler', 'viewInformationCenter',
				null, $this->getActionArgs($monographFile)
			)
		);

		// Configure the file link action.
		parent::FileLinkAction(
			'moreInfo', $ajaxModal,
			__($titleKey), $icon
		);
	}
}

?>
