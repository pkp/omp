<?php

/**
 * @file SubmissionCopyeditHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCopyeditHandler
 * @ingroup pages_copyeditor
 *
 * @brief Handle requests for submission tracking. 
 */


import('pages.copyeditor.CopyeditorHandler');

class SubmissionCopyeditHandler extends CopyeditorHandler {
	/**
	 * Constructor
	 **/
	function SubmissionCopyeditHandler() {
		parent::CopyeditorHandler();
	}
	/** submission associated with the request **/
	var $submission;

	function submission($args) {
		$monographId = $args[0];
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);		
		$press =& Request::getPress();

		CopyeditorAction::copyeditUnderway($submission);

		$useLayoutEditors = $press->getSetting('useLayoutEditors');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('copyeditor', $submission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('initialCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('editorAuthorCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_AUTHOR'));
		$templateMgr->assign_by_ref('finalCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL'));
		$templateMgr->assign('useLayoutEditors', $useLayoutEditors);
		$templateMgr->assign('helpTopicId', 'editorial.copyeditorsRole.copyediting');
		$templateMgr->display('copyeditor/submission.tpl');
	}

	function completeCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);

		if (CopyeditorAction::completeCopyedit($submission, Request::getUserVar('send'))) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	function completeFinalCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);;

		if (CopyeditorAction::completeFinalCopyedit($submission, Request::getUserVar('send'))) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	function uploadCopyeditVersion() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;

		$copyeditStage = Request::getUserVar('copyeditStage');
		CopyeditorAction::uploadCopyeditVersion($submission, $copyeditStage);

		Request::redirect(null, null, 'submission', $monographId);
	}

	//
	// Misc
	//

	/**
	 * Download a file.
	 * @param $args array ($monographId, $fileId, [$revision])
	 */
	function downloadFile($args) {
		$monographId = isset($args[0]) ? $args[0] : 0;
		$fileId = isset($args[1]) ? $args[1] : 0;
		$revision = isset($args[2]) ? $args[2] : null;

		$this->validate($monographId);
		$submission =& $this->submission;
		if (!CopyeditorAction::downloadCopyeditorFile($submission, $fileId, $revision)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	/**
	 * View a file (inlines file).
	 * @param $args array ($monographId, $fileId, [$revision])
	 */
	function viewFile($args) {
		$monographId = isset($args[0]) ? $args[0] : 0;
		$fileId = isset($args[1]) ? $args[1] : 0;
		$revision = isset($args[2]) ? $args[2] : null;

		$this->validate($monographId);
		$submission =& $this->submission;
		if (!CopyeditorAction::viewFile($monographId, $fileId, $revision)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	//
	// Validation
	//

	/**
	 * Validate that the user is the assigned copyeditor for
	 * the monograph.
	 * Redirects to copyeditor index page if validation fails.
	 */
	function validate($monographId) {
		parent::validate();

		$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		$isValid = true;

		$copyeditorSubmission =& $copyeditorSubmissionDao->getCopyeditorSubmission($monographId, $user->getId());

		if ($copyeditorSubmission == null) {
			$isValid = false;
		} else if ($copyeditorSubmission->getPressId() != $press->getId()) {
			$isValid = false;
		} else {
			if ($copyeditorSubmission->getUserIdBySignoffType('SIGNOFF_COPYEDITING_INITIAL') != $user->getId()) {
				$isValid = false;
			}
		}

		if (!$isValid) {
			Request::redirect(null, Request::getRequestedPage());
		}

		$this->submission =& $copyeditorSubmission;
		return true;
	}

	//
	// Proofreading
	//

	/**
	 * Set the author proofreading date completion
	 */
	function authorProofreadingComplete($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$this->setupTemplate(true, $monographId);

		$send = Request::getUserVar('send') ? true : false;

		import('classes.submission.proofreader.ProofreaderAction');

		if (ProofreaderAction::proofreadEmail($monographId,'PROOFREAD_AUTHOR_COMPLETE', $send?'':Request::url(null, 'copyeditor', 'authorProofreadingComplete', 'send'))) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	/**
	 * Proof / "preview" a galley.
	 * @param $args array ($monographId, $galleyId)
	 */
	function proofGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->assign('galleyId', $galleyId);
		$templateMgr->display('submission/layout/proofGalley.tpl');
	}

	/**
	 * Proof galley (shows frame header).
	 * @param $args array ($monographId, $galleyId)
	 */
	function proofGalleyTop($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->assign('galleyId', $galleyId);
		$templateMgr->assign('backHandler', 'submission');
		$templateMgr->display('submission/layout/proofGalleyTop.tpl');
	}

	/**
	 * Proof galley (outputs file contents).
	 * @param $args array ($monographId, $galleyId)
	 */
	function proofGalleyFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);

		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$galley =& $galleyDao->getGalley($galleyId, $monographId);

		import('classes.file.MonographFileManager'); // FIXME

		if (isset($galley)) {
			if ($galley->isHTMLGalley()) {
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign_by_ref('galley', $galley);
				if ($galley->isHTMLGalley() && $styleFile =& $galley->getStyleFile()) {
					$templateMgr->addStyleSheet(Request::url(null, 'monograph', 'viewFile', array(
						$monographId, $galleyId, $styleFile->getFileId()
					)));
				}
				$templateMgr->display('submission/layout/proofGalleyHTML.tpl');

			} else {
				// View non-HTML file inline
				SubmissionCopyeditHandler::viewFile(array($monographId, $galley->getFileId()));
			}
		}
	}

	/**
	 * Metadata functions.
	 */
	function viewMetadata($args) {
		$monographId = $args[0];
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'editing');

		CopyeditorAction::viewMetadata($submission);
	}

	function saveMetadata() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);

		if (CopyeditorAction::saveMetadata($submission)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	/**
	 * Remove cover page from monograph
	 */
	function removeCoverPage($args) {
		$monographId = isset($args[0]) ? (int)$args[0] : 0;
		$formLocale = $args[1];
		$this->validate($monographId);
		$submission =& $this->submission;
		$press =& Request::getPress();

		import('classes.file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
		$publicFileManager->removePressFile($press->getId(),$submission->getFileName($formLocale));
		$submission->setFileName('', $formLocale);
		$submission->setOriginalFileName('', $formLocale);
		$submission->setWidth('', $formLocale);
		$submission->setHeight('', $formLocale);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographDao->updateMonograph($submission);

		Request::redirect(null, null, 'viewMetadata', $monographId);
	}

}
?>
