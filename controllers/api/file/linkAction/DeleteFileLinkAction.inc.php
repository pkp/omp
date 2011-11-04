<?php
/**
 * @file controllers/api/file/linkAction/DeleteFileLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DeleteFileLinkAction
 * @ingroup controllers_api_file_linkAction
 *
 * @brief An action to delete a file.
 */

import('controllers.api.file.linkAction.FileLinkAction');

class DeleteFileLinkAction extends FileLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographFile MonographFile the monograph file to be deleted
	 * @param $stageId int (optional)
	 * @param $localeKey string (optional) Locale key to use for delete link
	 *  be deleted.
	 */
	function DeleteFileLinkAction(&$request, &$monographFile, $stageId, $localeKey = 'grid.action.delete') {
		// Instantiate the confirmation modal.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
		$confirmationModal = new RemoteActionConfirmationModal(
			__('common.confirmDelete'), null,
			$router->url(
				$request, null, 'api.file.ManageFileApiHandler',
				'deleteFile', null, $this->getActionArgs($monographFile, $stageId)
			)
		);

		// Configure the file link action.
		parent::FileLinkAction(
			'deleteFile', $confirmationModal,
			__($localeKey), 'delete'
		);
	}
}

?>
