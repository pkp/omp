<?php

/**
 * @file ProductionHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionHandler
 * @ingroup pages_information
 *
 * @brief Display press information.
 */

// $Id$


import('handler.Handler');
import('submission.productionEditor.ProductionEditorAction');

class ProductionEditorHandler extends Handler {

	function ProductionEditorHandler() {
		parent::Handler();
	}

	/**
	 * Display the information page for the press..
	 */
	function index($args) {

		$this->validate();
		$this->setupTemplate();
		$press =& Request::getPress();

		$templateMgr =& TemplateManager::getManager();
		$user =& Request::getUser();
		$rangeInfo =& Handler::getRangeInfo('submissions');
		$productionSubmissionDao =& DAORegistry::getDAO('ProductionEditorSubmissionDAO');

		$page = isset($args[0]) ? $args[0] : '';
		switch($page) {
			case 'completed':
				$active = false;
				break;
			default:
				$page = 'active';
				$active = true;
		}

		$submissions = $productionSubmissionDao->getProductionEditorSubmissions($user->getId(), $press->getId(), $active, $rangeInfo);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageToDisplay', $page);
		if (!$active) {
			// Make view counts available if enabled.
			$templateMgr->assign('statViews', $press->getSetting('statViews'));
		}
		$templateMgr->assign_by_ref('submissions', $submissions);
		$templateMgr->display('productionEditor/index.tpl');
	}


	/**
	 * Select Proofreader.
	 * @param $args array ($monographId, $userId)
	 */
	function selectProofreader($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$userId = isset($args[1]) ? (int) $args[1] : 0;

		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;		

		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if ($userId && $monographId && $roleDao->roleExists($press->getId(), $userId, ROLE_ID_PROOFREADER)) {
			import('submission.proofreader.ProofreaderAction');
			ProofreaderAction::selectProofreader($userId, $submission);
			Request::redirect(null, null, 'submissionEditing', $monographId);
		} else {
			$this->setupTemplate(true, $monographId, 'editing');

			$searchType = null;
			$searchMatch = null;
			$search = $searchQuery = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (isset($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} else if (isset($searchInitial)) {
				$searchInitial = String::strtoupper($searchInitial);
				$searchType = USER_FIELD_INITIAL;
				$search = $searchInitial;
			}

			$proofreaders = $roleDao->getUsersByRoleId(ROLE_ID_PROOFREADER, $press->getId(), $searchType, $search, $searchMatch);

			$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
			$proofreaderStatistics = $acquisitionsEditorSubmissionDao->getProofreaderStatistics($press->getId());

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

			$templateMgr->assign_by_ref('users', $proofreaders);

			$proofAssignment =& $submission->getProofAssignment();
			if ($proofAssignment) {
				$templateMgr->assign('currentUser', $proofAssignment->getProofreaderId());
			}
			$templateMgr->assign('statistics', $proofreaderStatistics);
			$templateMgr->assign('fieldOptions', Array(
				USER_FIELD_FIRSTNAME => 'user.firstName',
				USER_FIELD_LASTNAME => 'user.lastName',
				USER_FIELD_USERNAME => 'user.username',
				USER_FIELD_EMAIL => 'user.email'
			));
			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign('pageSubTitle', 'editor.monograph.selectProofreader');
			$templateMgr->assign('pageTitle', 'user.role.proofreaders');
			$templateMgr->assign('actionHandler', 'selectProofreader');

			$templateMgr->assign('helpTopicId', 'press.roles.proofreader');
			$templateMgr->display('acquisitionsEditor/selectUser.tpl');
		}
	}

	/**
	 * Notify author for proofreading
	 */
	function notifyAuthorProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send')?1:0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;		
		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_AUTHOR_REQUEST', $send?'':Request::url(null, null, 'notifyAuthorProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Thank author for proofreading
	 */
	function thankAuthorProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send')?1:0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_AUTHOR_ACK', $send?'':Request::url(null, null, 'thankAuthorProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Editor initiates proofreading
	 */
	function editorInitiateProofreader() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
		$proofAssignment =& $proofAssignmentDao->getProofAssignmentByMonographId($monographId);
		$proofAssignment->setDateProofreaderNotified(Core::getCurrentDate());
		$proofAssignmentDao->updateProofAssignment($proofAssignment);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Editor completes proofreading
	 */
	function editorCompleteProofreader() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
		$proofAssignment =& $proofAssignmentDao->getProofAssignmentByMonographId($monographId);
		$proofAssignment->setDateProofreaderCompleted(Core::getCurrentDate());
		$proofAssignmentDao->updateProofAssignment($proofAssignment);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Notify proofreader for proofreading
	 */
	function notifyProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send');
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_REQUEST', $send?'':Request::url(null, null, 'notifyProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Thank proofreader for proofreading
	 */
	function thankProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send')?1:0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;				
		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_ACK', $send?'':Request::url(null, null, 'thankProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Editor initiates layout editor proofreading
	 */
	function editorInitiateLayoutEditor() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
		$proofAssignment =& $proofAssignmentDao->getProofAssignmentByMonographId($monographId);
		$proofAssignment->setDateLayoutEditorNotified(Core::getCurrentDate());
		$proofAssignmentDao->updateProofAssignment($proofAssignment);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Editor completes layout editor proofreading
	 */
	function editorCompleteLayoutEditor() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
		$proofAssignment =& $proofAssignmentDao->getProofAssignmentByMonographId($monographId);
		$proofAssignment->setDateLayoutEditorCompleted(Core::getCurrentDate());
		$proofAssignmentDao->updateProofAssignment($proofAssignment);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Notify layout editor for proofreading
	 */
	function notifyLayoutEditorProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send')?1:0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_LAYOUT_REQUEST', $send?'':Request::url(null, null, 'notifyLayoutEditorProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Thank layout editor for proofreading
	 */
	function thankLayoutEditorProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send')?1:0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_LAYOUT_ACK', $send?'':Request::url(null, null, 'thankLayoutEditorProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function submission($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		$press =& Request::getPress();
		$this->setupTemplate(false, $monographId);

		$user =& Request::getUser();

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettings = $pressSettingsDao->getPressSettings($press->getId());

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$isEditor = $roleDao->roleExists($press->getId(), $user->getId(), ROLE_ID_EDITOR);

		$enableComments = $press->getSetting('enableComments');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign('userId', $user->getId());
		$templateMgr->assign('isEditor', $isEditor);
		$templateMgr->assign('enableComments', $enableComments);

		if ($enableComments) {
			import('monograph.Monograph');
			$templateMgr->assign('commentsStatus', $submission->getCommentsStatus());
			$templateMgr->assign_by_ref('commentsStatusOptions', Monograph::getCommentsStatusOptions());
		}

		$templateMgr->assign('helpTopicId', 'editorial.editorsRole.submissionSummary');

		$templateMgr->display('productionEditor/submission.tpl');


	}
	function submitArtwork($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(false, $monographId);

		$user =& Request::getUser();
		import('monograph.form.MonographArtworkForm');
		$artworkForm = new MonographArtworkForm('productionEditor/art.tpl', $submission);

		$editData = $artworkForm->processEvents();

		if (!$editData && $artworkForm->validate()) {
			$monographId = $artworkForm->execute();
		}

		Request::redirect(null, null, 'submissionArt', $submission->getMonographId());
	}
	function submissionArt($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(false, $monographId);

		$user =& Request::getUser();
		import('monograph.form.MonographArtworkForm');
		$artworkForm = new MonographArtworkForm('productionEditor/art.tpl', $submission);

		if ($artworkForm->isLocaleResubmit()) {
			$artworkForm->readInputData();
		} else {
			$artworkForm->initData();
		}

		$artworkForm->display();
	}

	function assignLayoutEditor($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$designerId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		
		$submission =& $this->submission;
		$press =& $this->press;

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		if ($designerId && $roleDao->roleExists($press->getId(), $designerId, ROLE_ID_DESIGNER)) {
			ProductionEditorAction::assignLayoutEditor($submission, $designerId);			
			Request::redirect(null, null, 'submissionLayout', $monographId);
		} else {
			$searchType = null;
			$searchMatch = null;
			$search = $searchQuery = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (isset($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} else if (isset($searchInitial)) {
				$searchInitial = String::strtoupper($searchInitial);
				$searchType = USER_FIELD_INITIAL;
				$search = $searchInitial;
			}

			$designers = $roleDao->getUsersByRoleId(ROLE_ID_DESIGNER, $press->getId(), $searchType, $search, $searchMatch);

			$productionEditorSubmissionDao =& DAORegistry::getDAO('ProductionEditorSubmissionDAO');

			$this->setupTemplate(true, $monographId, 'editing');

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));
			$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));

			$templateMgr->assign('pageTitle', 'user.role.designers');
			$templateMgr->assign('pageSubTitle', 'editor.monograph.selectDesigner');
			$templateMgr->assign('actionHandler', 'assignLayoutEditor');
			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign_by_ref('users', $designers);

			$templateMgr->assign('fieldOptions', Array(
				USER_FIELD_FIRSTNAME => 'user.firstName',
				USER_FIELD_LASTNAME => 'user.lastName',
				USER_FIELD_USERNAME => 'user.username',
				USER_FIELD_EMAIL => 'user.email'
			));
			$templateMgr->assign('helpTopicId', 'press.roles.productionEditor');
			$templateMgr->display('acquisitionsEditor/selectUser.tpl');
		}
	}

	/**
	 * Upload the layout version of the submission file
	 */
	function uploadLayoutFile() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;

		ProductionEditorAction::uploadLayoutVersion($submission);

		Request::redirect(null, null, 'submissionLayout', $monographId);
	}

	/**
	 * Upload an artwork file (either layout version, galley, or supp. file).
	 */
	function removeArtworkFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$fileId = isset($args[1]) ? (int) $args[1] : 0;

		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);

		$monographFileManager->deleteFile($fileId);

		Request::redirect(null, null, 'submissionArt', $monographId);
	}

	/**
	 * Upload an artwork file (either layout version, galley, or supp. file).
	 */
	function uploadArtworkFile() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);

		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);

		if ($monographFileManager->uploadedFileExists('artworkFile')) {
			$fileId = $monographFileManager->uploadArtworkFile('artworkFile', null);
		}
		Request::redirect(null, null, 'submissionArt', Request::getUserVar('monographId'));
	}
	
	function submissionLayout($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(false, $monographId);
		$press =& Request::getPress();
		$user =& Request::getUser();

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettings = $pressSettingsDao->getPressSettings($press->getId());

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$isEditor = $roleDao->roleExists($press->getId(), $user->getId(), ROLE_ID_EDITOR);

		$enableComments = $press->getSetting('enableComments');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('useLayoutEditors', true);

		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('authors', $submission->getAuthors());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign('userId', $user->getId());
		$templateMgr->assign('isEditor', $isEditor);
		$templateMgr->assign('enableComments', $enableComments);
		$templateMgr->assign('useLayoutEditors', true);

		if ($enableComments) {
			import('monograph.Monograph');
			$templateMgr->assign('commentsStatus', $submission->getCommentsStatus());
			$templateMgr->assign_by_ref('commentsStatusOptions', Monograph::getCommentsStatusOptions());
		}

		if ($isEditor) {
			$templateMgr->assign('helpTopicId', 'editorial.editorsRole.submissionSummary');
		}

		$templateMgr->display('productionEditor/layout.tpl');


	}

	/**
	 * Download a file.
	 * @param $args array ($monographId, $fileId, [$revision])
	 */
	function downloadFile($args) {
		$monographId = isset($args[0]) ? $args[0] : 0;
		$fileId = isset($args[1]) ? $args[1] : 0;
		$revision = isset($args[2]) ? $args[2] : null;

		$this->validate($monographId);
		$press =& Request::getPress();
		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);
		if (!$monographFileManager->viewFile($fileId, $revision)) {
			Request::redirect(null, null, 'submissionLayout', $monographId);
		}
	}

	/**
	 * View a file (inline file).
	 * @param $args array ($monographId, $fileId, [$revision])
	 */
	function viewFile($args) {
		$monographId = isset($args[0]) ? $args[0] : 0;
		$fileId = isset($args[1]) ? $args[1] : 0;
		$revision = isset($args[2]) ? $args[2] : null;

		$this->validate($monographId);
		$press =& Request::getPress();
		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);
		if (!$monographFileManager->viewFile($fileId, $revision)) {
			Request::redirect(null, null, 'submissionLayout', $monographId);
		}
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false, $monographId = 0, $parentPage = null) {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_AUTHOR, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION));
		$templateMgr =& TemplateManager::getManager();

		$pageHierarchy = $subclass ? array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'productionEditor'), 'user.role.productionEditor'), array(Request::url(null, 'productionEditor'), 'manuscript.submissions'))
			: array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'productionEditor'), 'user.role.productionEditor'));

		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}

	/**
	 * Validate that user has production editor permissions in the selected press.
	 * Redirects to user index page if not properly authenticated.
	 */
	function validate($monographId = null, $reason = null) {
		parent::validate();

		$press =& Request::getPress();
		$productionEditorSubmission = null;
		$isValid = true;

		if ($monographId != null) {
			$productionEditorSubmissionDao =& DAORegistry::getDAO('ProductionEditorSubmissionDAO');
			$productionEditorSubmission =& $productionEditorSubmissionDao->getById($monographId, $press->getId());

			if ($productionEditorSubmission == null) {
				$isValid = false;

			} else if ($productionEditorSubmission->getPressId() != $press->getId()) {
				$isValid = false;

			} else if ($productionEditorSubmission->getDateSubmitted() == null) {
				$isValid = false;

			}
		}
		if (!isset($press) || !Validation::isProductionEditor($press->getId())) {
				Validation::redirectLogin($reason);
		}

		if (!$isValid) {
			Request::redirect(null, Request::getRequestedPage());
		}

		$this->press =& $press;
		$this->submission =& $productionEditorSubmission;
		return true;
	}
	
	/**
	 * Notify the layout editor.
	 */
	function notifyLayoutDesigner($args) {
		$monographId = Request::getUserVar('monographId');
		$layoutAssignmentId = Request::getUserVar('layoutAssignmentId');
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$send = Request::getUserVar('send') ? true : false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (ProductionEditorAction::notifyLayoutDesigner($submission, $layoutAssignmentId, $send)) {
			Request::redirect(null, null, 'submissionLayout', $monographId);
		}
	}

	/**
	 * Thank the layout editor.
	 */
	function thankLayoutEditor($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$send = Request::getUserVar('send') ? true : false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::thankLayoutEditor($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Create a new galley with the uploaded file.
	 */
	function uploadGalley($fileName = null) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		import('submission.form.MonographGalleyForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$galleyForm =& new MonographGalleyForm($monographId);
		$galleyId = $galleyForm->execute($fileName);

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

		import('submission.form.MonographGalleyForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$submitForm =& new MonographGalleyForm($monographId, $galleyId);

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

		import('submission.form.MonographGalleyForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$submitForm =& new MonographGalleyForm($monographId, $galleyId);

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
			Request::redirect(null, null, 'submissionEditing', $monographId);
		} else {
			$this->setupTemplate(true, $monographId, 'editing');
			$submitForm->display();
		}
	}

	/**
	 * Change the sequence order of a galley.
	 */
	function orderGalley() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		AcquisitionsEditorAction::orderGalley($submission, Request::getUserVar('galleyId'), Request::getUserVar('d'));

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Delete a galley file.
	 * @param $args array ($monographId, $galleyId)
	 */
	function deleteGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		AcquisitionsEditorAction::deleteGalley($submission, $galleyId);

		Request::redirect(null, null, 'submissionEditing', $monographId);
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
				$this->viewFile(array($monographId, $galley->getFileId()));
			}
		}
	}

}

?>
