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
	 * @param $reviewRound ReviewRound The review round to upload to.
	 * @param $uploaderRoles array The ids of all roles allowed to upload
	 *  in the context of this action.
	 */
	function AddRevisionLinkAction(&$request, &$reviewRound, $uploaderRoles) {
		// Create the action arguments array.
		$actionArgs = array(
			'fileStage' => MONOGRAPH_FILE_REVIEW_REVISION,
			'stageId' => $reviewRound->getStageId(),
			'reviewRoundId' => $reviewRound->getId(),
			'revisionOnly' => '1'
		);

		// Call the parent class constructor.
		parent::BaseAddFileLinkAction(
			$request, $reviewRound->getSubmissionId(), $reviewRound->getStageId(), $uploaderRoles, $actionArgs,
			__('editor.review.uploadRevisionToRound', array('round' => $reviewRound->getRound())),
			__('editor.review.uploadRevision')
		);
	}
}

?>
