<?php
/**
 * @defgroup controllers_api_file_linkAction
 */

/**
 * @file controllers/api/file/linkAction/BaseAddFileLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BaseAddFileLinkAction
 * @ingroup controllers_api_file_linkAction
 *
 * @brief Abstract base class for file upload actions.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class BaseAddFileLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The monograph the file should be
	 *  uploaded to.
	 * @param $stageId integer The workflow stage in which the file
	 *  uploader is being instantiated (one of the WORKFLOW_STAGE_ID_*
	 *  constants).
	 * @param $uploaderRoles array The ids of all roles allowed to upload
	 *  in the context of this action.
	 * @param $actionArgs array The arguments to be passed into the file
	 *  upload wizard.
	 * @param $wizardTitle string The title to be displayed in the file
	 *  upload wizard.
	 * @param $buttonLabel string The link action's button label.
	 */
	function BaseAddFileLinkAction(&$request, $monographId, $stageId,
			$uploaderRoles, $actionArgs, $wizardTitle, $buttonLabel) {

		// Augment the action arguments array.
		$actionArgs['monographId'] = $monographId;
		$actionArgs['stageId'] = $stageId;
		assert(is_array($uploaderRoles) && count($uploaderRoles) > 1);
		$actionArgs['uploaderRoles'] = implode('-', $uploaderRoles);

		// Instantiate the file upload modal.
		$dispatcher =& $request->getDispatcher();
		import('lib.pkp.classes.linkAction.request.WizardModal');
		$modal = new WizardModal(
			$dispatcher->url(
				$request, ROUTE_COMPONENT, null,
				'wizard.fileUpload.FileUploadWizardHandler', 'startWizard',
				null, $actionArgs
			),
			$wizardTitle, 'fileManagement'
		);

		// Configure the link action.
		parent::LinkAction('addFile', $modal, $buttonLabel, 'add');
	}
}

?>
