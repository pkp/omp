<?php
/**
 * @file controllers/informationCenter/linkAction/NotifyLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotifyLinkAction
 * @ingroup controllers_informationCenter_linkAction
 *
 * @brief An action to open up the notify part of the IC.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class NotifyLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monograph Monograph The monograph
	 * @param $userId optional
	 *  to show information about.
	 */
	function NotifyLinkAction(&$request, &$monograph, $userId = null) {
		// Instantiate the information center modal.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$ajaxModal = new AjaxModal(
			$router->url(
				$request, null,
				'informationCenter.SubmissionInformationCenterHandler', 'viewNotify',
				null, array('monographId' => $monograph->getId())
			),
			__('common.notify')
		);

		// Configure the file link action.
		parent::LinkAction(
			'notify', $ajaxModal,
			__('common.notify'), 'notify'
		);
	}
}

?>
