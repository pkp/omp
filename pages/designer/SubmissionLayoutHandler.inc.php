<?php

/**
 * @file SubmissionLayoutHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
	 * View an assigned submission's design page.
	 * @param $args array ($monographId)
	 */
	function submission($args) {
		$monographId = isset($args[0]) ? $args[0] : 0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		$productionAssignments =& $submission->getProductionAssignments();
		for ($i=0,$iCount=count($productionAssignments); $i<$iCount; $i++) {
			$productionSignoffsUnindexed =& $productionAssignments[$i]->getSignoffs();
			$productionSignoff =& $productionSignoffsUnindexed['PRODUCTION_DESIGN']; //FIXME other symbolics for other assignment types
			if (!$productionSignoff->getDateUnderway()) {
				$productionSignoff->setDateUnderway(Core::getCurrentDate());
				$signoffDao->updateObject($productionSignoff);
			}
			$productionSignoffsIndexed['PRODUCTION_DESIGN'] =& $productionSignoff;
			$productionAssignments[$i]->setSignoffs($productionSignoffsIndexed);
		}
		$submission->setProductionAssignments($productionAssignments);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $submission);

		$templateMgr->assign('helpTopicId', 'editorial.designersRole.layout');

		$templateMgr->display('designer/submission.tpl');
	}

	function viewMetadata($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'summary');

		DesignerAction::viewMetadata($submission);
	}

	/**
	 * Mark assignment as complete.
	 */
	function completeDesign($args) {
		$monographId = Request::getUserVar('monographId');
		$assignmentId = Request::getUserVar('assignmentId');
		$this->validate($monographId, true);
		$press =& $this->press;
		$submission =& $this->submission;

		if (DesignerAction::completeDesign($submission, $assignmentId, Request::getUserVar('send'))) {
			Request::redirect(null, null, 'submission', $monographId);
		}		
	}


	//
	// Galley Management
	//

	/**
	 * Create a new galley file.
	 */
	function uploadGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);

		import('classes.submission.form.MonographGalleyForm');
		$productionAssignmentId = Request::getUserVar('productionAssignmentId');

		$galleyForm = new MonographGalleyForm($monographId);
		$galleyId = $galleyForm->execute('galleyFile', $productionAssignmentId);

		Request::redirect(null, null, 'editGalley', array($monographId, $galleyId));
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

		import('classes.submission.form.MonographGalleyForm');

		$submitForm = new MonographGalleyForm($monographId, $galleyId);

		if ($submitForm->isLocaleResubmit()) {
			$submitForm->readInputData();
		} else {
			$submitForm->initData();
		}
		$submitForm->display();
	}

	/**
	 * Save changes to a galley.
	 * @param $args array ($monographId, $galleyId)
	 */
	function saveGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'editing');

		import('classes.submission.form.MonographGalleyForm');

		$submitForm = new MonographGalleyForm($monographId, $galleyId);

		$submitForm->readInputData();
		if ($submitForm->validate()) {
			$submitForm->execute();

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
	 * Delete a monograph image.
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

		import('classes.submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId,'PROOFREAD_LAYOUT_COMPLETE', $send?'':Request::url(null, 'designer', 'designerProofreadingComplete', 'send'))) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}


	//
	// Validation
	//

	/**
	 * Validate that the user is an assigned designer for the submission.
	 * Redirects to designer index page if validation fails.
	 * @param $monographId int the submission being edited
	 */
	function validate($monographId) {
		parent::validate();

		$isValid = false;

		$press =& Request::getPress();
		$user =& Request::getUser();

		$designerSubmissionDao =& DAORegistry::getDAO('DesignerSubmissionDAO');
		$submission =& $designerSubmissionDao->getSubmission($monographId);

		if (isset($submission)) {
			$isValid = true;
		}

		if (!$isValid) {
			Request::redirect(null, Request::getRequestedPage());
		}
		
		$this->press =& $press;
		$this->submission =& $submission;

		return true;
	}
}

?>
