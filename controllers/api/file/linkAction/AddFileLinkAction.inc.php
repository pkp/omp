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

import('controllers.api.file.linkAction.BaseAddFileLinkAction');

class AddFileLinkAction extends BaseAddFileLinkAction {

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
	 * @param $fileStage integer The file stage the file should be
	 *  uploaded to (one of the MONOGRAPH_FILE_* constants).
	 * @param $assocType integer The type of the element the file should
	 *  be associated with (one fo the ASSOC_TYPE_* constants).
	 * @param $assocId integer The id of the element the file should be
	 *  associated with.
	 */
	function AddFileLinkAction(&$request, $monographId, $stageId, $uploaderRoles,
			$fileStage, $assocType = null, $assocId = null) {

		// Create the action arguments array.
		$actionArgs = array('fileStage' => $fileStage);
		if (is_numeric($assocType) && is_numeric($assocId)) {
			$actionArgs['assocType'] = (int)$assocType;
			$actionArgs['assocId'] = (int)$assocId;
		}

		// Identify text labels based on the file stage.
		$textLabels = AddFileLinkAction::_getTextLabels($fileStage);

		// Call the parent class constructor.
		parent::BaseAddFileLinkAction(
			$request, $monographId, $stageId, $uploaderRoles, $actionArgs,
			__($textLabels['wizardTitle']), __($textLabels['buttonLabel'])
		);
	}


	//
	// Private methods
	//
	/**
	 * Static method to return text labels
	 * for upload to different file stages.
	 *
	 * @param $fileStage integer One of the
	 *  MONOGRAPH_FILE_* constants.
	 * @return array
	 */
	function _getTextLabels($fileStage) {
		static $textLabels = array(
			MONOGRAPH_FILE_SUBMISSION => array(
				'wizardTitle' => 'submission.submit.uploadSubmissionFile',
				'buttonLabel' => 'submission.addFile'
			),
			MONOGRAPH_FILE_REVIEW => array(
				'wizardTitle' => 'editor.submissionReview.uploadFile',
				'buttonLabel' => 'editor.submissionReview.uploadFile'
			),
			MONOGRAPH_FILE_FINAL => array(
				'wizardTitle' => 'submission.uploadAFinalDraft',
				'buttonLabel' => 'submission.uploadAFinalDraft'
			),
			MONOGRAPH_FILE_COPYEDIT => array(
				'wizardTitle' => 'submission.uploadACopyeditedVersion',
				'buttonLabel' => 'submission.uploadACopyeditedVersion'
			),
			MONOGRAPH_FILE_FAIR_COPY => array(
				'wizardTitle' => 'submission.uploadFairCopy',
				'buttonLabel' => 'submission.uploadFairCopy'
			)
		);

		assert(isset($textLabels[$fileStage]));
		return $textLabels[$fileStage];
	}
}

?>
