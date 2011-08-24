<?php
/**
 * @defgroup controllers_api_file_linkAction
 */

/**
 * @file controllers/api/file/linkAction/AddRevisionLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddRevisionLinkAction
 * @ingroup controllers_api_file_linkAction
 *
 * @brief An action to upload a revision of file currently under review.
 */

import('controllers.api.file.linkAction.BaseAddFileLinkAction');

class AddRevisionLinkAction extends BaseAddFileLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The monograph the file should be
	 *  uploaded to.
	 * @param $uploaderRoles array The ids of all roles allowed to upload
	 *  in the context of this action.
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $round integer The review round to upload to.
	 */
	function AddRevisionLinkAction(&$request, $monographId, $uploaderRoles, $stageId, $round) {
		// Create the action arguments array.
		$actionArgs = array(
			'fileStage' => MONOGRAPH_FILE_REVIEW_REVISION,
			'stageId' => $stageId,
			'round' => $round,
			'revisionOnly' => '1'
		);

		// Call the parent class constructor.
		parent::BaseAddFileLinkAction(
			$request, $monographId, $stageId, $uploaderRoles, $actionArgs,
			__('editor.review.uploadRevisionToRound', array('round' => $round)),
			__('editor.review.uploadRevision')
		);
	}
}

?>
