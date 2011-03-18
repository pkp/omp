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
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 */
	function FileInfoCenterLinkAction(&$request, &$monographFile, $stageId) {
		// Instantiate the information center modal.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$ajaxModal = new AjaxModal(
			$router->url(
				$request, null,
				'informationCenter.FileInformationCenterHandler', 'viewInformationCenter',
				null, $this->getActionArgs($monographFile, $stageId)
			)
		);

		// Configure the file link action.
		parent::FileLinkAction(
			'moreInfo', $ajaxModal,
			__('grid.action.moreInformation'), 'more_info'
		);
	}
}

?>
