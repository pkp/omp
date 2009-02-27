<?php

/**
 * @file SubmitHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmitHandler
 * @ingroup pages_author
 *
 * @brief Handle requests for author monograph submission. 
 */

// $Id$


class SubmitHandler extends AuthorHandler {

	/**
	 * Display author monograph submission.
	 * Displays author index page if a valid step is not specified.
	 * @param $args array optional, if set the first parameter is the step to display
	 */
	function submit($args) {
		parent::validate('author.submit.authorSubmitLoginMessage');
		parent::setupTemplate(true);

		$step = isset($args[0]) ? $args[0] : 0;
		$monographId = Request::getUserVar('monographId');
		
		import('author.form.submit.AuthorSubmissionSequence');

		$sequence =& new AuthorSubmissionSequence($monographId);
		$submitForm = $sequence->getFormForStep($step);
		
		if ($submitForm->isLocaleResubmit()) {
			$submitForm->readInputData();
		} else {
			$submitForm->initData();
		}
		$submitForm->display();
	}

	/**
	 * Save a submission step.
	 * @param $args array first parameter is the step being saved
	 */
	function saveSubmit($args) {
		parent::validate();
		parent::setupTemplate(true);

		$step = isset($args[0]) ? $args[0] : 0;
		$monographId = Request::getUserVar('monographId');

		import('author.form.submit.AuthorSubmissionSequence');
		$sequence = new AuthorSubmissionSequence($monographId);
		$submitForm =& $sequence->getFormForStep($step);

		$submitForm->readInputData();

		$editData = $submitForm->processEvents();

		// Check for any special cases before trying to save
		switch ($step) {
			case 3:
				if (Request::getUserVar('uploadSubmissionFile')) {
					$submitForm->uploadSubmissionFile('submissionFile');
					$editData = true;
				}
				if (Request::getUserVar('uploadCompletedProspectusFile')) {
					$submitForm->uploadCompletedProspectusFile('completedProspectusFile');
					$editData = true;
				}
				break;

			case 4:
				if (Request::getUserVar('submitUploadSuppFile')) {
					SubmitHandler::submitUploadSuppFile();
					return;
				}
				break;
		}


		if (!$editData && $submitForm->validate()) {
			$monographId = $submitForm->execute();
			if ($step == 5) {
				$press =& Request::getPress();
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign_by_ref('press', $press);
				// If this is an editor and there is a
				// submission file, monograph can be expedited.
		//		if (Validation::isEditor($press->getPressId()) && $monograph->getSubmissionFileId()) {
		//			$templateMgr->assign('canExpedite', true);
		//		}
				$templateMgr->assign('monographId', $monographId);
				$templateMgr->assign('helpTopicId','submission.index');
				$templateMgr->display('author/submit/complete.tpl');

			} else {
				Request::redirect(null, null, 'submit', $sequence->getNextStep(), array('monographId' => $monographId));
			}

		} else {

			$submitForm->display();
		}
	}

	/**
	 * Create new supplementary file with a uploaded file.
	 */
	function submitUploadSuppFile() {
		parent::validate();
		parent::setupTemplate(true);
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographId = Request::getUserVar('monographId');

		list($press, $monograph) = SubmitHandler::validate($monographId, 4);
		$monograph =& $monographDao->getMonograph((int) $monographId);

		import('author.form.submit.AuthorSubmitSuppFileForm');
		$submitForm =& new AuthorSubmitSuppFileForm($monograph);
		$submitForm->setData('title', Locale::translate('common.untitled'));
		$suppFileId = $submitForm->execute();

		Request::redirect(null, null, 'submitSuppFile', $suppFileId, array('monographId' => $monographId));
	}

	/**
	 * Display supplementary file submission form.
	 * @param $args array optional, if set the first parameter is the supplementary file to edit
	 */
	function submitSuppFile($args) {
		parent::validate();
		parent::setupTemplate(true);
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		$monographId = Request::getUserVar('monographId');
		$suppFileId = isset($args[0]) ? (int) $args[0] : 0;

		list($press, $monograph) = SubmitHandler::validate($monographId, 4);
		$monograph =& $monographDao->getMonograph((int) $monographId);

		import('author.form.submit.AuthorSubmitSuppFileForm');
		$submitForm =& new AuthorSubmitSuppFileForm($monograph, $suppFileId);

		if ($submitForm->isLocaleResubmit()) {
			$submitForm->readInputData();
		} else {
			$submitForm->initData();
		}
		$submitForm->display();
	}

	/**
	 * Save a supplementary file.
	 * @param $args array optional, if set the first parameter is the supplementary file to update
	 */
	function saveSubmitSuppFile($args) {
		parent::validate();
		parent::setupTemplate(true);
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		$monographId = Request::getUserVar('monographId');
		$suppFileId = isset($args[0]) ? (int) $args[0] : 0;

		list($press, $monograph) = SubmitHandler::validate($monographId, 4);
		$monograph =& $monographDao->getMonograph((int) $monographId);

		import('author.form.submit.AuthorSubmitSuppFileForm');
		$submitForm =& new AuthorSubmitSuppFileForm($monograph, $suppFileId);
		$submitForm->readInputData();

		if ($submitForm->validate()) {
			$submitForm->execute();
			Request::redirect(null, null, 'submit', '4', array('monographId' => $monographId));
		} else {
			$submitForm->display();
		}
	}

	/**
	 * Delete a supplementary file.
	 * @param $args array, the first parameter is the supplementary file to delete
	 */
	function deleteSubmitSuppFile($args) {
		import('file.MonographFileManager');

		parent::validate();
		parent::setupTemplate(true);
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		$monographId = Request::getUserVar('monographId');
		$suppFileId = isset($args[0]) ? (int) $args[0] : 0;


		list($press, $monograph) = SubmitHandler::validate($monographId, 4);
		$monograph =& $monographDao->getMonograph((int) $monographId);

		$suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
		$suppFile = $suppFileDao->getSuppFile($suppFileId, $monographId);
		$suppFileDao->deleteSuppFileById($suppFileId, $monographId);

		if ($suppFile->getFileId()) {
			$monographFileManager =& new MonographFileManager($monographId);
			$monographFileManager->deleteFile($suppFile->getFileId());
		}

		Request::redirect(null, null, 'submit', '4', array('monographId' => $monographId));
	}

	function expediteSubmission() {
		$monographId = (int) Request::getUserVar('monographId');
		list($press, $monograph) = SubmitHandler::validate($monographId);

		// The author must also be an editor to perform this task.
		if (Validation::isEditor($press->getPressId()) && $monograph->getSubmissionFileId()) {
			import('submission.editor.EditorAction');
			EditorAction::expediteSubmission($monograph);
			Request::redirect(null, 'editor', 'submissionEditing', array($monograph->getMonographId()));
		}

		Request::redirect(null, null, 'track');
	}

	/**
	 * Validation check for submission.
	 * Checks that monograph ID is valid, if specified.
	 * @param $monographId int
	 * @param $step int
	 */
	function validate($monographId = null, $step = false) {
/*		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$user =& Request::getUser();
		$press =& Request::getPress();

		if ($step !== false && ($step < 1 || $step > 5 || (!isset($monographId) && $step != 1))) {
			Request::redirect(null, null, 'submit', array(1));
		}
*/
/*		$monograph = null;

		// Check that monograph exists for this press and user and that submission is incomplete
		if (isset($monographId)) {
			$monograph =& $monographDao->getMonograph((int) $monographId);

			if (!$monograph || $monograph->getUserId() !== $user->getUserId() || $monograph->getPressId() !== $press->getPressId() || ($step !== false && $step > $monograph->getSubmissionProgress())) {
				Request::redirect(null, null, 'submit');
			}
		}
		return array(&$press, &$monograph);
*/
	}
}
?>