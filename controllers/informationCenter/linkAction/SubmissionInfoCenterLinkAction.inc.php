<?php

/**
 * @file controllers/informationCenter/linkAction/SubmissionInfoCenterLinkAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionInfoCenterLinkAction
 * @ingroup controllers_informationCenter_linkAction
 *
 * @brief An action to open up the information center for a submission.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class SubmissionInfoCenterLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId int the ID of the monograph to present link for
	 * to show information about.
	 * @param $linkKey string optional locale key to display for link
	 */
	function SubmissionInfoCenterLinkAction(&$request, $monographId, $linkKey = 'informationCenter.informationCenter') {
		// Instantiate the information center modal.
		$dispatcher =& $request->getDispatcher();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$ajaxModal = new AjaxModal(
			$dispatcher->url(
				$request, ROUTE_COMPONENT, null,
				'informationCenter.SubmissionInformationCenterHandler',
				'viewInformationCenter',
				null,
				array('monographId' => $monographId)
			),
			__('informationCenter.informationCenter'),
			'informationCenter'
		);

		// Configure the link action.
		parent::LinkAction(
			'moreInfo', $ajaxModal,
			__($linkKey), 'more_info'
		);
	}
}

?>
