<?php
/**
 * @file controllers/informationCenter/linkAction/FileNotesLinkAction.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileNotesLinkAction
 * @ingroup controllers_informationCenter_linkAction
 *
 * @brief An action to open up the notes IC for a file.
 */

import('lib.pkp.controllers.informationCenter.linkAction.PKPFileNotesLinkAction');

class FileNotesLinkAction extends PKPFileNotesLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $submissionFile SubmissionFile the submission file
	 *  to show information about.
	 * @param $user User
	 * @param $stageId int (optional) The stage id that user is looking at.
	 * @param $removeHistoryTab boolean (optional) Open the information center
	 * without the history tab.
	 */
	function FileNotesLinkAction(&$request, &$submissionFile, $user, $stageId = null, $removeHistoryTab = false) {
		parent::PKPFileNotesLinkAction($request, $submissionFile, $user, $stageId, $removeHistoryTab);
	}

	/**
	 * returns the modal for this link action.
	 * @param $request PKPRequest
	 * @param $submissionFile SubmissionFile
	 * @param $stageId int
	 * @param $removeHistoryTab boolean
	 * @return AjaxModal
	 */
	function getModal($request, $submissionFile, $stageId, $removeHistoryTab) {
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$router =& $request->getRouter();

		$title = (isset($submissionFile)) ? implode(': ', array(__('informationCenter.bookInfo'), $submissionFile->getLocalizedName())) : __('informationCenter.bookInfo');

		$ajaxModal = new AjaxModal(
			$router->url(
				$request, null,
				'informationCenter.FileInformationCenterHandler', 'viewInformationCenter',
				null, array_merge($this->getActionArgs($submissionFile, $stageId), array('removeHistoryTab' => $removeHistoryTab))
			),
			$title,
			'modal_information'
		);

		return $ajaxModal;
	}
}

?>
