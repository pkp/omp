<?php

/**
 * @file controllers/api/signoff/linkAction/AddSignoffFileLinkAction.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddSignoffFileLinkAction
 * @ingroup controllers_api_signoff_linkAction
 *
 * @brief Class for signoff file upload actions.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class ReadSignoffLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The monograph the file should be
	 *  uploaded to.
	 * @param $stageId integer The workflow stage in which the file
	 *  uploader is being instantiated (one of the WORKFLOW_STAGE_ID_*
	 *  constants).
	 * @param $signoffId integer The id of the signoff being read
	 *  upload wizard.
	 * @param $wizardTitle string The title to be displayed in the file
	 *  upload wizard.
	 * @param $buttonLabel string The link action's button label.
	 */
	function ReadSignoffLinkAction($request, $monographId, $stageId, $signoffId,
										$modalTitle, $buttonLabel, $image = null) {

		// Create the actionArgs array
		$actionArgs = array();
		$actionArgs['submissionId'] = $monographId;
		$actionArgs['stageId'] = $stageId;
		$actionArgs['signoffId'] = $signoffId;

		// Instantiate the file upload modal.
		$dispatcher = $request->getDispatcher();
		import('lib.pkp.classes.linkAction.request.WizardModal');
		$modal = new AjaxModal(
			$dispatcher->url(
				$request, ROUTE_COMPONENT, null,
				'modals.signoff.FileSignoffHandler', 'readSignoff',
				null, $actionArgs
			),
			$modalTitle, 'modal_add_file'
		);

		// Configure the link action.
		parent::LinkAction('readSignoff', $modal, $buttonLabel, $image);
	}
}

?>
