<?php
/**
 * @defgroup controllers_api_file_linkAction
 */

/**
 * @file controllers/api/signoff/linkAction/AddSignoffFileLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddSignoffFileLinkAction
 * @ingroup controllers_api_signoff_linkAction
 *
 * @brief Class for signoff file upload actions.
 */

import('lib.pkp.classes.linkAction.LinkAction');

// Bring in MONOGRAPH_FILE_* constants
import('classes.monograph.MonographFile');

class AddSignoffFileLinkAction extends LinkAction {

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
	function AddSignoffFileLinkAction(&$request, $monographId, $stageId, $symbolic, $signoffId = null,
			$modalTitle, $buttonLabel) {

		// Create the actionArgs array
		$actionArgs = array();
		$actionArgs['monographId'] = $monographId;
		$actionArgs['stageId'] = $stageId;
		$actionArgs['symbolic'] = $symbolic;
		$actionArgs['signoffId'] = $signoffId;
		$actionArgs['fileStage'] = MONOGRAPH_FILE_SIGNOFF;

		// Instantiate the file upload modal.
		$dispatcher =& $request->getDispatcher();
		import('lib.pkp.classes.linkAction.request.WizardModal');
		$modal = new AjaxModal(
			$dispatcher->url(
				$request, ROUTE_COMPONENT, null,
				'modals.signoff.FileSignoffHandler', 'displayFileUploadForm',
				null, $actionArgs
			),
			$modalTitle, 'fileManagement'
		);

		// Configure the link action.
		parent::LinkAction('addSignoff', $modal, $buttonLabel, 'add');
	}
}

?>
