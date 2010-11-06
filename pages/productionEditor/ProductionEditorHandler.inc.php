<?php

/**
 * @file ProductionHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionHandler
 * @ingroup pages_information
 *
 * @brief Display press information.
 */



import('classes.handler.Handler');
import('classes.submission.productionEditor.ProductionEditorAction');

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
	 * @param $args array ($monographId, $assignmentId, $userId)
	 */
	function selectProofreader($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$assignmentId = isset($args[1]) ? (int) $args[1] : 0;
		$userId = isset($args[2]) ? (int) $args[2] : 0;

		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if ($userId && $monographId && $roleDao->userHasRole($press->getId(), $userId, ROLE_ID_PROOFREADER)) {
			import('classes.submission.proofreader.ProofreaderAction');
			ProofreaderAction::selectProofreader($userId, $assignmentId, $submission);
			Request::redirect(null, null, 'submissionLayout', $monographId);
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

			$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
			$proofreaderStatistics = $seriesEditorSubmissionDao->getProofreaderStatistics($press->getId());

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

			$templateMgr->assign_by_ref('users', $proofreaders);

			$signoffDao =& DAORegistry::getDAO('SignoffDAO');
			$proofSignoff =& $signoffDao->getBySymbolic('PRODUCTION_PROOF_PROOFREADER', ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $assignmentId);
			if ($proofSignoff) {
				$templateMgr->assign('currentUser', $proofSignoff->getUserId());
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
			$templateMgr->assign('productionAssignmentId', $assignmentId);
			$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));
			$templateMgr->assign('helpTopicId', 'press.roles.proofreader');
			$templateMgr->display('productionEditor/selectUser.tpl');
		}
	}

	/**
	 * Select Designer.
	 * @param $args array ($monographId, $assignmentId, $userId)
	 */
	function selectDesigner($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$assignmentId = isset($args[1]) ? (int) $args[1] : 0;
		$userId = isset($args[2]) ? (int) $args[2] : 0;

		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if ($userId && $monographId && $roleDao->userHasRole($press->getId(), $userId, ROLE_ID_DESIGNER)) {
			import('classes.submission.designer.DesignerAction');
			DesignerAction::selectDesigner($userId, $assignmentId, $submission);
			Request::redirect(null, null, 'submissionLayout', $monographId);
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

			$proofreaders = $roleDao->getUsersByRoleId(ROLE_ID_DESIGNER, $press->getId(), $searchType, $search, $searchMatch);

			$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
			$designerStatistics = $seriesEditorSubmissionDao->getDesignerStatistics($press->getId());

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

			$templateMgr->assign_by_ref('users', $proofreaders);

			$signoffDao =& DAORegistry::getDAO('SignoffDAO');
			$designSignoff =& $signoffDao->getBySymbolic('PRODUCTION_DESIGNER', ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $assignmentId);
			if ($designSignoff) {
				$templateMgr->assign('currentUser', $designSignoff->getUserId());
			}

			$templateMgr->assign('statistics', $designerStatistics);
			$templateMgr->assign('fieldOptions', Array(
				USER_FIELD_FIRSTNAME => 'user.firstName',
				USER_FIELD_LASTNAME => 'user.lastName',
				USER_FIELD_USERNAME => 'user.username',
				USER_FIELD_EMAIL => 'user.email'
			));
			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign('pageSubTitle', 'production.selectDesigner');
			$templateMgr->assign('pageTitle', 'user.role.designers');
			$templateMgr->assign('actionHandler', 'selectDesigner');
			$templateMgr->assign('productionAssignmentId', $assignmentId);
			$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));
			$templateMgr->assign('helpTopicId', 'press.roles.proofreader');
			$templateMgr->display('productionEditor/selectUser.tpl');
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

		import('classes.submission.proofreader.ProofreaderAction');
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

		import('classes.submission.proofreader.ProofreaderAction');
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

		import('classes.submission.proofreader.ProofreaderAction');
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

		import('classes.submission.proofreader.ProofreaderAction');
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
	function notifyDesigner($args) {
		$monographId = Request::getUserVar('monographId');
		$assignmentId = Request::getUserVar('assignmentId');
		$send = Request::getUserVar('send') ? true : false;
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'editing');

		import('classes.submission.productionEditor.ProductionEditorAction');
		if (ProductionEditorAction::notifyDesigner($submission, $assignmentId, $send)) {
			Request::redirect(null, null, 'submissionLayout', $monographId);
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

		import('classes.submission.proofreader.ProofreaderAction');
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
		$isEditor = $roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_EDITOR);

		$enableComments = $press->getSetting('enableComments');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign('userId', $user->getId());
		$templateMgr->assign('isEditor', $isEditor);
		$templateMgr->assign('enableComments', $enableComments);

		$templateMgr->assign('pageToDisplay', 'submissionSummary');

		if ($enableComments) {
			import('classes.monograph.Monograph');
			$templateMgr->assign('commentsStatus', $submission->getCommentsStatus());
			$templateMgr->assign_by_ref('commentsStatusOptions', Monograph::getCommentsStatusOptions());
		}

		$templateMgr->assign('helpTopicId', 'editorial.editorsRole.submissionSummary');

		$templateMgr->display('productionEditor/submission.tpl');


	}
	function submissionArt($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(false, $monographId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageToDisplay', 'submissionArt');
		$templateMgr->assign_by_ref('submission', $submission);

		$templateMgr->display('productionEditor/submission.tpl');
	}
	function productionAssignment($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$productionAssignmentId = isset($args[1]) ? (int) $args[1] : null;
		$this->validate($monographId);
		$this->setupTemplate();
		$submission =& $this->submission;

		$productionAssignment = null;
		if ($productionAssignmentId) {
			$productionAssignmentDao =& DAORegistry::getDAO('ProductionAssignmentDAO');
			$productionAssignment =& $productionAssignmentDao->getById($productionAssignmentId);
		}

		import('classes.submission.form.ProductionAssignmentForm');
		$form = new ProductionAssignmentForm($monographId, $productionAssignment);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $submission);

		if (!Request::getUserVar('fromDesignAssignmentForm')) {
			$form->initData();
		} else {
			$form->readInputData();

			if (Request::getUserVar('save') && $form->validate()) {
				$form->readInputData();
				$form->execute();
				Request::redirect(null, null, 'submissionLayout', $monographId);
			}
		}

		$form->display();
	}

	function deleteSelectedAssignments() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);

		$productionAssignmentDao =& DAORegistry::getDAO('ProductionAssignmentDAO');
		$selectedAssignments = Request::getUserVar('selectedAssignments');
		foreach ($selectedAssignments as $selectedAssignment) {
			$productionAssignmentDao->deleteById($selectedAssignment);
		}

		Request::redirect(null, null, 'submissionLayout', $monographId);
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

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);

		$monographFileManager->deleteFile($fileId);

		Request::redirect(null, null, 'submissionArt', $monographId);
	}

	function addProductionAssignment($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);

		$newProductionAssignment = Request::getUserVar('newProductionAssignment');

		$designAssignmentDao =& DAORegistry::getDAO('ProductionAssignmentDAO');
		$productionAssignment =& $designAssignmentDao->newDataObject();
		$productionAssignment->setType($newProductionAssignment['type']);
		$productionAssignment->setLabel($newProductionAssignment['label']);
		$productionAssignment->setMonographId($monographId);

		$designAssignmentDao->insertObject($productionAssignment);

		Request::redirect(null, null, 'submissionLayout', $monographId);
	}

	/**
	 * Upload an artwork file (either layout version, galley, or supp. file).
	 */
	function uploadArtworkFile() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);

		import('classes.file.MonographFileManager');
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

//		$artworkCount = $monographFileDao->getArtworkFileCountByMonographId($submission->getId());

		$productionAssignmentDao =& DAORegistry::getDAO('ProductionAssignmentDAO');
		$productionAssignmentTypes = $productionAssignmentDao->productionAssignmentTypeToLocaleKey();

		$enableComments = $press->getSetting('enableComments');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('productionAssignmentTypes', $productionAssignmentTypes);
		$templateMgr->assign('enableComments', $enableComments);
		$templateMgr->assign_by_ref('submission', $submission);

		$templateMgr->assign('pageToDisplay', 'submissionLayout');

		if ($enableComments) {
			import('classes.monograph.Monograph');
			$templateMgr->assign('commentsStatus', $submission->getCommentsStatus());
			$templateMgr->assign_by_ref('commentsStatusOptions', Monograph::getCommentsStatusOptions());
		}

		$templateMgr->display('productionEditor/submission.tpl');
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
		import('classes.file.MonographFileManager');
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
		import('classes.file.MonographFileManager');
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
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION));
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
		$layoutAssignmentId = Request::getUserVar('assignmentId');
		$this->validate($monographId);
		$submission =& $this->submission;

		$send = Request::getUserVar('send') ? true : false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (ProductionEditorAction::notifyDesigner($submission, $assignmentId, $send)) {
			Request::redirect(null, null, 'submissionLayout', $monographId);
		}
	}

	/**
	 * Thank the layout designer.
	 */
	function thankLayoutDesigner($args) {
		$monographId = Request::getUserVar('monographId');
		$assignmentId = Request::getUserVar('assignmentId');
		$this->validate($monographId);
		$press =& $this->press;
		$submission =& $this->submission;

		$send = Request::getUserVar('send') ? true : false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (ProductionEditorAction::thankLayoutDesigner($submission, $assignmentId, $send)) {
			Request::redirect(null, null, 'submissionLayout', $monographId);
		}
	}
}

?>
