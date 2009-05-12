<?php

/**
 * @file SubmissionLayoutHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionLayoutHandler
 * @ingroup pages_designer
 *
 * @brief Handle requests related to submission layout editing. 
 */

// $Id$

import('pages.designer.DesignerHandler');

class SubmissionLayoutHandler extends DesignerHandler {
	function SubmissionLayoutHandler() {
		parent::DesignerHandler();
	}

	//
	// Submission Management
	//

	/**
	 * View an assigned submission's layout editing page.
	 * @param $args array ($monographId)
	 */
	function submission($args) {
		$monographId = isset($args[0]) ? $args[0] : 0;
		$layoutAssignmentId = isset($args[1]) ? $args[1] : 0;

		$this->validate($monographId, $layoutAssignmentId);
		$press =& $this->press;
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

//		import('submission.proofreader.ProofreaderAction');
//		ProofreaderAction::designerProofreadingUnderway($submission);
//
		$layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $monographId);

		if ($layoutSignoff->getDateNotified() != null && $layoutSignoff->getDateUnderway() == null)
		{
			// Set underway date
			$layoutSignoff->setDateUnderway(Core::getCurrentDate());
			$signoffDao->updateObject($layoutSignoff);
		}

//		$disableEdit = !SubmissionLayoutHandler::layoutEditingEnabled($submission);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $submission);
//		$templateMgr->assign('disableEdit', $disableEdit);

		$templateMgr->assign('useProofreaders', $press->getSetting('useProofreaders'));
		$templateMgr->assign('templates', $press->getSetting('templates'));
		$templateMgr->assign('helpTopicId', 'editorial.designersRole.layout');

		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph =& $publishedMonographDao->getPublishedMonographByMonographId($submission->getMonographId());
/*		if ($publishedMonograph) {
			$issueDao =& DAORegistry::getDAO('IssueDAO');
			$issue =& $issueDao->getIssueById($publishedMonograph->getIssueId());
			$templateMgr->assign_by_ref('publishedMonograph', $publishedMonograph);
			$templateMgr->assign_by_ref('issue', $issue);
		}
*/
		$templateMgr->display('designer/submission.tpl');
	}

	function viewMetadata($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'summary');

		DesignerAction::viewMetadata($submission, ROLE_ID_LAYOUT_EDITOR);
	}

	/**
	 * Mark assignment as complete.
	 */
	function completeAssignment($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, true);
		$press =& $this->press;
		$submission =& $this->submission;

		if (DesignerAction::completeLayoutEditing($submission, Request::getUserVar('send'))) {
			Request::redirect(null, null, 'submission', $monographId);
		}		
	}


	//
	// Galley Management
	//

	/**
	 * Create a new layout file (layout version, galley, or supp file) with the uploaded file.
	 */
	function uploadLayoutFile() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, true);
		$press =& $this->press;
		$submission =& $this->submission;

		switch (Request::getUserVar('layoutFileType')) {
			case 'submission':
				DesignerAction::uploadLayoutVersion($submission);
				Request::redirect(null, null, 'submission', $monographId);
				break;
			case 'galley':
				import('submission.form.MonographGalleyForm');

				// FIXME: Need construction by reference or validation always fails on PHP 4.x
				$galleyForm =& new MonographGalleyForm($monographId);
				$galleyId = $galleyForm->execute('layoutFile');

				Request::redirect(null, null, 'editGalley', array($monographId, $galleyId));
				break;
			case 'supp':
				import('submission.form.SuppFileForm');

				// FIXME: Need construction by reference or validation always fails on PHP 4.x
				$suppFileForm =& new SuppFileForm($submission);
				$suppFileForm->setData('title', Locale::translate('common.untitled'));
				$suppFileId = $suppFileForm->execute('layoutFile');

				Request::redirect(null, null, 'editSuppFile', array($monographId, $suppFileId));
				break;
			default:
				// Invalid upload type.
				Request::redirect(null, 'designer');
		}
	}

	/**
	 * Edit a galley.
	 * @param $args array ($monographId, $galleyId)
	 */
	function editGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$this->setupTemplate(true, $monographId, 'editing');

		if (SubmissionLayoutHandler::layoutEditingEnabled($submission)) {
			import('submission.form.MonographGalleyForm');

			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$submitForm =& new MonographGalleyForm($monographId, $galleyId);

			if ($submitForm->isLocaleResubmit()) {
				$submitForm->readInputData();
			} else {
				$submitForm->initData();
			}
			$submitForm->display();

		} else {
			// View galley only
			$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
			$galley =& $galleyDao->getGalley($galleyId, $monographId);

			if (!isset($galley)) {
				Request::redirect(null, null, 'submission', $monographId);
			}

			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign_by_ref('galley', $galley);
			$templateMgr->display('submission/layout/galleyView.tpl');
		}
	}

	/**
	 * Save changes to a galley.
	 * @param $args array ($monographId, $galleyId)
	 */
	function saveGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, true);
		$press =& $this->press;
		$submission =& $this->submission;

		import('submission.form.MonographGalleyForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$submitForm =& new MonographGalleyForm($monographId, $galleyId);
		$submitForm->readInputData();

		if ($submitForm->validate()) {
			$submitForm->execute();

			// Send a notification to associated users
/*			import('notification.Notification');
			$monographDao =& DAORegistry::getDAO('MonographDAO'); 
			$monograph =& $monographDao->getMonograph($monographId);
			$notificationUsers = $monograph->getAssociatedUserIds(true, false);
			foreach ($notificationUsers as $user) {
				$url = Request::url(null, $user['role'], 'submissionEditing', $monograph->getMonographId(), null, 'layout');
				Notification::createNotification($user['id'], "notification.type.galleyModified",
					$monograph->getMonographTitle(), $url, 1, NOTIFICATION_TYPE_GALLEY_MODIFIED);
			}
*/
			if (Request::getUserVar('uploadImage')) {
				$submitForm->uploadImage();
				Request::redirect(null, null, 'editGalley', array($monographId, $galleyId));
			} else if(($deleteImage = Request::getUserVar('deleteImage')) && count($deleteImage) == 1) {
				list($imageId) = array_keys($deleteImage);
				$submitForm->deleteImage($imageId);
				Request::redirect(null, null, 'editGalley', array($monographId, $galleyId));
			}
			Request::redirect(null, null, 'submission', $monographId);
		} else {
			$this->setupTemplate(true, $monographId, 'editing');
			$submitForm->display();
		}
	}

	/**
	 * Delete a galley file.
	 * @param $args array ($monographId, $galleyId)
	 */
	function deleteGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, true);
		$press =& $this->press;
		$submission =& $this->submission;

		DesignerAction::deleteGalley($submission, $galleyId);

		Request::redirect(null, null, 'submission', $monographId);
	}

	/**
	 * Change the sequence order of a galley.
	 */
	function orderGalley() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, true);
		$press =& $this->press;
		$submission =& $this->submission;

		DesignerAction::orderGalley($submission, Request::getUserVar('galleyId'), Request::getUserVar('d'));

		Request::redirect(null, null, 'submission', $monographId);
	}

	/**
	 * Proof / "preview" a galley.
	 * @param $args array ($monographId, $galleyId)
	 */
	function proofGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

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
		$press =& $this->press;
		$submission =& $this->submission;

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->assign('galleyId', $galleyId);
		$templateMgr->assign('backHandler', 'submissionEditing');
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
		$press =& $this->press;
		$submission =& $this->submission;

		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$galley =& $galleyDao->getGalley($galleyId, $monographId);

		import('file.MonographFileManager'); // FIXME

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
				SubmissionLayoutHandler::viewFile(array($monographId, $galley->getFileId()));
			}
		}
	}

	/**
	 * Delete an monograph image.
	 * @param $args array ($monographId, $fileId)
	 */
	function deleteMonographImage($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$fileId = isset($args[2]) ? (int) $args[2] : 0;
		$revisionId = isset($args[3]) ? (int) $args[3] : 0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;
		DesignerAction::deleteMonographImage($submission, $fileId, $revisionId);

		Request::redirect(null, null, 'editGalley', array($monographId, $galleyId));
	}


	//
	// Supplementary File Management
	//


	/**
	 * Edit a supplementary file.
	 * @param $args array ($monographId, $suppFileId)
	 */
	function editSuppFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$suppFileId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$this->setupTemplate(true, $monographId, 'editing');

		if (SubmissionLayoutHandler::layoutEditingEnabled($submission)) {
			import('submission.form.SuppFileForm');

			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$submitForm =& new SuppFileForm($submission, $suppFileId);

			if ($submitForm->isLocaleResubmit()) {
				$submitForm->readInputData();
			} else {
				$submitForm->initData();
			}
			$submitForm->display();


		} else {
			// View supplementary file only
			$suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
			$suppFile =& $suppFileDao->getSuppFile($suppFileId, $monographId);

			if (!isset($suppFile)) {
				Request::redirect(null, null, 'submission', $monographId);
			}

			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign_by_ref('suppFile', $suppFile);
			$templateMgr->display('submission/suppFile/suppFileView.tpl');	
		}
	}

	/**
	 * Save a supplementary file.
	 * @param $args array ($suppFileId)
	 */
	function saveSuppFile($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$suppFileId = isset($args[0]) ? (int) $args[0] : 0;

		import('submission.form.SuppFileForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$submitForm =& new SuppFileForm($submission, $suppFileId);
		$submitForm->readInputData();

		if ($submitForm->validate()) {
			$submitForm->execute();

			// Send a notification to associated users
			import('notification.Notification');
			$monographDao =& DAORegistry::getDAO('MonographDAO'); 
			$monograph =& $monographDao->getMonograph($monographId);
			$notificationUsers = $monograph->getAssociatedUserIds(true, false);
			foreach ($notificationUsers as $user) {
				$url = Request::url(null, $user['role'], 'submissionEditing', $monograph->getMonographId(), null, 'layout');
				Notification::createNotification($user['id'], "notification.type.suppFileModified",
					$monograph->getMonographTitle(), $url, 1, NOTIFICATION_TYPE_SUPP_FILE_MODIFIED);
			}
			
			Request::redirect(null, null, 'submission', $monographId);

		} else {
			$this->setupTemplate(true, $monographId, 'editing');
			$submitForm->display();
		}
	}

	/**
	 * Delete a supplementary file.
	 * @param $args array ($monographId, $suppFileId)
	 */
	function deleteSuppFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$suppFileId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, true);
		$press =& $this->press;
		$submission =& $this->submission;

		DesignerAction::deleteSuppFile($submission, $suppFileId);

		Request::redirect(null, null, 'submission', $monographId);
	}

	/**
	 * Change the sequence order of a supplementary file.
	 */
	function orderSuppFile() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, true);
		$press =& $this->press;
		$submission =& $this->submission;

		DesignerAction::orderSuppFile($submission, Request::getUserVar('suppFileId'), Request::getUserVar('d'));

		Request::redirect(null, null, 'submission', $monographId);
	}


	//
	// File Access
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
		$press =& $this->press;
		$submission =& $this->submission;
		if (!DesignerAction::downloadFile($submission, $fileId, $revision)) {
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
		$press =& $this->press;
		$submission =& $this->submission;
		if (!DesignerAction::viewFile($monographId, $fileId, $revision)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	//
	// Proofreading
	//

	/**
	 * Sets the date of layout editor proofreading completion
	 */
	function designerProofreadingComplete($args) {
		$monographId = Request::getUserVar('monographId');

		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);

		$send = false;
		if (isset($args[0])) {
			$send = Request::getUserVar('send') ? true : false;
		}

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId,'PROOFREAD_LAYOUT_COMPLETE', $send?'':Request::url(null, 'designer', 'designerProofreadingComplete', 'send'))) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}


	//
	// Validation
	//

	/**
	 * Validate that the user is the assigned layout editor for the submission.
	 * Redirects to designer index page if validation fails.
	 * @param $monographId int the submission being edited
	 * @param $checkEdit boolean check if editor has editing permissions
	 */
	function validate($monographId, $checkEdit = false) {
		parent::validate();

		$isValid = false;

		$press =& Request::getPress();
		$user =& Request::getUser();

		$designerSubmissionDao =& DAORegistry::getDAO('DesignerSubmissionDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$submission =& $designerSubmissionDao->getSubmission($monographId, $press->getId());

		if (isset($submission)) {
			$layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $monographId);
			if (!isset($layoutSignoff)) $isValid = false;
			elseif ($layoutSignoff->getUserId() == $user->getUserId()) {
				if ($checkEdit) {
					$isValid = $this->layoutEditingEnabled($submission);
				} else {
					$isValid = true;
				}
			}			
		}

		if (!$isValid) {
			Request::redirect(null, Request::getRequestedPage());
		}
		
		$this->press =& $press;
		$this->submission =& $submission;
		return true;
	}

	/**
	 * Check if a layout editor is allowed to make changes to the submission.
	 * This is allowed if there is an outstanding galley creation or layout editor
	 * proofreading request.
	 * @param $submission DesignerSubmission
	 * @return boolean true if layout editor can modify the submission
	 */
	function layoutEditingEnabled(&$submission) {
		$layoutAssignment =& $submission->getLayoutAssignments();
		$proofAssignment =& $submission->getProofAssignments();

		return(($layoutAssignment->getDateNotified() != null
			&& $layoutAssignment->getDateCompleted() == null)
		|| ($proofAssignment->getDateDesignerNotified() != null
			&& $proofAssignment->getDateDesignerCompleted() == null));
	}

	function downloadLayoutTemplate($args) {
		parent::validate();
		$press =& Request::getPress();
		$templates = $press->getSetting('templates');
		import('file.PressFileManager');
		$pressFileManager = new PressFileManager($press);
		$templateId = (int) array_shift($args);
		if ($templateId >= count($templates) || $templateId < 0) Request::redirect(null, 'designer');
		$template =& $templates[$templateId];

		$filename = "template-$templateId." . $pressFileManager->parseFileExtension($template['originalFilename']);
		$pressFileManager->downloadFile($filename, $template['fileType']);
	}
}

?>
