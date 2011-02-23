<?php
/**
 * @defgroup controllers_api_file_linkAction
 */

/**
 * @file controllers/api/file/linkAction/AddFileLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddFileLinkAction
 * @ingroup controllers_api_file_linkAction
 *
 * @brief An action to add a submission file.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class AddFileLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The monograph the file should be
	 *  uploaded to.
	 * @param $fileStage integer The file stage the file should be
	 *  uploaded to (one of the MONOGRAPH_FILE_* constants).
	 * @param $assocType integer The type of the element the file should
	 *  be associated with (one fo the ASSOC_TYPE_* constants).
	 * @param $assocId integer The id of the element the file should be
	 *  associated with.
	 */
	function AddFileLinkAction(&$request, $monographId, $fileStage, $assocType = null, $assocId = null) {
		// Create the action arguments array.
		$actionArgs = array('monographId' => $monographId, 'fileStage' => $fileStage);
		if (is_numeric($assocType) && is_numeric($assocId)) {
			$actionArgs['assocType'] = (int)$assocType;
			$actionArgs['assocId'] = (int)$assocId;
		}

		// Instantiate the file upload modal.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.WizardModal');
		$modal = new WizardModal(
				$router->url($request, null,
						'wizard.fileUpload.FileUploadWizardHandler', 'startWizard',
						null, $actionArgs),
				__('submission.submit.uploadSubmissionFile'), 'fileManagement');

		// Configure the link action.
		parent::LinkAction('addFile', $modal, __('submission.addFile'), 'add');
	}
}