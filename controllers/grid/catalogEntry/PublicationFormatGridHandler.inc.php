<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');

// import format grid specific classes
import('controllers.grid.catalogEntry.PublicationFormatGridRow');
import('controllers.grid.catalogEntry.PublicationFormatGridCategoryRow');
import('controllers.grid.catalogEntry.PublicationFormatCategoryGridDataProvider');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class PublicationFormatGridHandler extends CategoryGridHandler {
	/** @var PublicationFormatGridCellProvider */
	var $_cellProvider;

	/** @var Submission */
	var $_submission;

	/** @var boolean */
	protected $_canManage;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct(new PublicationFormatCategoryGridDataProvider($this));
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR),
			array(
				'setAvailable', 'editApprovedProof', 'saveApprovedProof',
			)
		);
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT),
			array(
				'addFormat', 'editFormat', 'editFormatTab', 'updateFormat', 'deleteFormat',
				'setApproved', 'setProofFileCompletion', 'selectFiles',
				'identifiers', 'updateIdentifiers', 'clearPubId',
				'dependentFiles',
			)
		);
		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR, ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT),
			array(
				'fetchGrid', 'fetchRow', 'fetchCategory',
			)
		);
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the submission associated with this publication format grid.
	 * @return Submission
	 */
	function getSubmission() {
		return $this->_submission;
	}

	/**
	 * Set the submission
	 * @param $submission Submission
	 */
	function setSubmission($submission) {
		$this->_submission = $submission;
	}


	//
	// Overridden methods from PKPHandler
	//
	/**
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize($request) {
		parent::initialize($request);

		// Retrieve the authorized submission.
		$this->setSubmission($this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION));

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_EDITOR,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_PKP_DEFAULT
		);
		$this->setTitle('monograph.publicationFormats');

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APP_SUBMISSION,
			LOCALE_COMPONENT_APP_DEFAULT,
			LOCALE_COMPONENT_APP_EDITOR
		);

		// Grid actions
		$router = $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);
		$this->_canManage = 0 != count(array_intersect($userRoles, array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT)));
		if ($this->_canManage) $this->addAction(
			new LinkAction(
				'addFormat',
				new AjaxModal(
					$router->url($request, null, null, 'addFormat', null, $actionArgs),
					__('grid.action.addFormat'),
					'modal_add_item'
				),
				__('grid.action.addFormat'),
				'add_item'
			)
		);

		// Columns
		$submission = $this->getSubmission();
		import('controllers.grid.catalogEntry.PublicationFormatGridCellProvider');
		$this->_cellProvider = new PublicationFormatGridCellProvider($submission->getId(), $this->_canManage);
		$this->addColumn(
			new GridColumn(
				'name',
				'common.name',
				null,
				null,
				$this->_cellProvider,
				array('width' => 60, 'anyhtml' => true)
			)
		);
		if ($this->_canManage) {
			$this->addColumn(
				new GridColumn(
					'isComplete',
					'common.complete',
					null,
					'controllers/grid/common/cell/statusCell.tpl',
					$this->_cellProvider,
					array('width' => 20)
				)
			);
			$this->addColumn(
				new GridColumn(
					'isAvailable',
					'grid.catalogEntry.availability',
					null,
					'controllers/grid/common/cell/statusCell.tpl',
					$this->_cellProvider,
					array('width' => 20)
				)
			);
		}
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
		$this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return PublicationFormatGridCategoryRow
	 */
	function getCategoryRowInstance() {
		return new PublicationFormatGridCategoryRow($this->getSubmission(), $this->_cellProvider, $this->_canManage);
	}


	//
	// Public Publication Format Grid Actions
	//
	/**
	 * Edit a publication format modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editFormat($args, $request) {
		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representationId = $request->getUserVar('representationId');
		$representation = $representationDao->getById(
			$representationId,
			$submission->getId()
		);
		// Check if this is a remote galley
		$remoteURL = isset($representation) ? $representation->getRemoteURL() : null;
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('submissionId', $submission->getId());
		$templateMgr->assign('representationId', $representationId);
		$templateMgr->assign('remoteRepresentation', $remoteURL);
		return new JSONMessage(true, $templateMgr->fetch('controllers/grid/catalogEntry/editFormat.tpl'));
	}

	/**
	 * Edit a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editFormatTab($args, $request) {
		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representation = $representationDao->getById(
			$request->getUserVar('representationId'),
			$submission->getId()
		);

		import('controllers.grid.catalogEntry.form.PublicationFormatForm');
		$publicationFormatForm = new PublicationFormatForm($submission, $representation);
		$publicationFormatForm->initData();

		return new JSONMessage(true, $publicationFormatForm->fetch($request));
	}

	/**
	 * Update a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateFormat($args, $request) {
		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representation = $representationDao->getById(
			$request->getUserVar('representationId'),
			$submission->getId()
		);

		import('controllers.grid.catalogEntry.form.PublicationFormatForm');
		$publicationFormatForm = new PublicationFormatForm($submission, $representation);
		$publicationFormatForm->readInputData();
		if ($publicationFormatForm->validate()) {
			$publicationFormatForm->execute($request);
			return DAO::getDataChangedEvent();
		}
		return new JSONMessage(true, $publicationFormatForm->fetch($request));
	}

	/**
	 * Delete a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function deleteFormat($args, $request) {
		$context = $request->getContext();
		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representation = $representationDao->getById(
			$request->getUserVar('representationId'),
			$submission->getId()
		);

		if (!$request->checkCSRF() || !$representation || !$representationDao->deleteById($representation->getId())) {
			return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
		}

		// Create a tombstone for this publication format.
		import('classes.publicationFormat.PublicationFormatTombstoneManager');
		$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
		$publicationFormatTombstoneMgr->insertTombstoneByPublicationFormat($representation, $context);

		$currentUser = $request->getUser();
		$notificationMgr = new NotificationManager();
		$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedPublicationFormat')));

		// Log the deletion of the format.
		import('lib.pkp.classes.log.SubmissionLog');
		import('classes.log.SubmissionEventLogEntry');
		SubmissionLog::logEvent($request, $this->getSubmission(), SUBMISSION_LOG_PUBLICATION_FORMAT_REMOVE, 'submission.event.publicationFormatRemoved', array('formatName' => $representation->getLocalizedName()));

		return DAO::getDataChangedEvent();
	}

	/**
	 * Set a format's "approved" state
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function setApproved($args, $request) {
		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representation = $representationDao->getById(
			$request->getUserVar('representationId'),
			$submission->getId()
		);

		if (!$representation) return new JSONMessage(false, __('manager.setup.errorDeletingItem'));

		$confirmationText = __('grid.catalogEntry.approvedRepresentation.removeMessage');
		if ($request->getUserVar('newApprovedState')) {
			$confirmationText = __('grid.catalogEntry.approvedRepresentation.message');
		}
		import('lib.pkp.controllers.grid.pubIds.form.PKPAssignPublicIdentifiersForm');
		$formTemplate = $this->getAssignPublicIdentifiersFormTemplate();
		$assignPublicIdentifiersForm = new PKPAssignPublicIdentifiersForm($formTemplate, $representation, $request->getUserVar('newApprovedState'), $confirmationText);
		if (!$request->getUserVar('confirmed')) {
			// Display assign pub ids modal
			$assignPublicIdentifiersForm->initData();
			return new JSONMessage(true, $assignPublicIdentifiersForm->fetch($request));
		}
		if ($request->getUserVar('newApprovedState')) {
			// Assign pub ids
			$assignPublicIdentifiersForm->readInputData();
			$assignPublicIdentifiersForm->execute($request);
		}

		$newApprovedState = (int) $request->getUserVar('newApprovedState');
		$representation->setIsApproved($newApprovedState);
		$representationDao->updateObject($representation);

		// log the state changing of the format.
		import('lib.pkp.classes.log.SubmissionLog');
		import('classes.log.SubmissionEventLogEntry');
		SubmissionLog::logEvent(
			$request, $this->getSubmission(),
			$newApprovedState?SUBMISSION_LOG_PUBLICATION_FORMAT_PUBLISH:SUBMISSION_LOG_PUBLICATION_FORMAT_UNPUBLISH,
			$newApprovedState?'submission.event.publicationFormatPublished':'submission.event.publicationFormatUnpublished',
			array('publicationFormatName' => $representation->getLocalizedName())
		);

		// Update the formats tombstones.
		import('classes.publicationFormat.PublicationFormatTombstoneManager');
		$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
		if ($representation->getIsAvailable() && $representation->getIsApproved()) {
			// Delete any existing tombstone.
			$publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats(array($representation));
		} else {
			// (Re)create a tombstone for this publication format.
			$publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats(array($representation));
			$publicationFormatTombstoneMgr->insertTombstoneByPublicationFormat($representation, $request->getContext());
		}

		return DAO::getDataChangedEvent($representation->getId());
	}

	/**
	 * Set a format's "available" state
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function setAvailable($args, $request) {
		$context = $request->getContext();
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById(
			$request->getUserVar('representationId'),
			null, // $submissionId
			$context->getId() // Make sure to validate the context.
		);

		if (!$publicationFormat) return new JSONMessage(false, __('manager.setup.errorDeletingItem'));

		$newAvailableState = (int) $request->getUserVar('newAvailableState');
		$publicationFormat->setIsAvailable($newAvailableState);
		$publicationFormatDao->updateObject($publicationFormat);

		// log the state changing of the format.
		import('lib.pkp.classes.log.SubmissionLog');
		import('classes.log.SubmissionEventLogEntry');
		SubmissionLog::logEvent(
			$request, $this->getSubmission(),
			$newAvailableState?SUBMISSION_LOG_PUBLICATION_FORMAT_AVAILABLE:SUBMISSION_LOG_PUBLICATION_FORMAT_UNAVAILABLE,
			$newAvailableState?'submission.event.publicationFormatMadeAvailable':'submission.event.publicationFormatMadeUnavailable',
			array('publicationFormatName' => $publicationFormat->getLocalizedName())
		);

		// Update the formats tombstones.
		import('classes.publicationFormat.PublicationFormatTombstoneManager');
		$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
		if ($publicationFormat->getIsAvailable() && $publicationFormat->getIsApproved()) {
			// Delete any existing tombstone.
			$publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats(array($publicationFormat));
		} else {
			// (Re)create a tombstone for this publication format.
			$publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats(array($publicationFormat));
			$publicationFormatTombstoneMgr->insertTombstoneByPublicationFormat($publicationFormat, $context);
		}

		return DAO::getDataChangedEvent($publicationFormat->getId());
	}

	/**
	 * Edit an approved proof.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editApprovedProof($args, $request) {
		$this->initialize($request);
		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representation = $representationDao->getById(
			$request->getUserVar('representationId'),
			$submission->getId()
		);

		import('controllers.grid.files.proof.form.ApprovedProofForm');
		$approvedProofForm = new ApprovedProofForm($submission, $representation, $request->getUserVar('fileId'));
		$approvedProofForm->initData();

		return new JSONMessage(true, $approvedProofForm->fetch($request));
	}

	/**
	 * Save an approved proof.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function saveApprovedProof($args, $request) {
		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representation = $representationDao->getById(
			$request->getUserVar('representationId'),
			$submission->getId()
		);

		import('controllers.grid.files.proof.form.ApprovedProofForm');
		$approvedProofForm = new ApprovedProofForm($submission, $representation, $request->getUserVar('fileId'));
		$approvedProofForm->readInputData();

		if ($approvedProofForm->validate()) {
			$approvedProofForm->execute($request);
			return DAO::getDataChangedEvent();
		}
		return new JSONMessage(true, $approvedProofForm->fetch($request));
	}

	/**
	 * Get the filename of the "assign public identifiers" form template.
	 * @return string
	 */
	function getAssignPublicIdentifiersFormTemplate() {
		return 'controllers/grid/pubIds/form/assignPublicIdentifiersForm.tpl';
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @copydoc GridHandler::getRowInstance()
	 */
	function getRowInstance() {
		return new PublicationFormatGridRow($this->_canManage);
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the submission.
	 * @return array
	 */
	function getRequestArgs() {
		return array(
			'submissionId' => $this->getSubmission()->getId(),
		);
	}


	//
	// Public grid actions
	//
	/**
	 * Add a new publication format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function addFormat($args, $request) {
		return $this->editFormat($args, $request);
	}

	/**
	 * Set the approval status for a file.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function setProofFileCompletion($args, $request) {
		$submission = $this->getSubmission();
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		import('lib.pkp.classes.submission.SubmissionFile'); // Constants
		$submissionFile = $submissionFileDao->getRevision(
			$request->getUserVar('fileId'),
			$request->getUserVar('revision'),
			SUBMISSION_FILE_PROOF,
			$submission->getId()
		);
		$confirmationText = __('editor.submission.proofreading.confirmRemoveCompletion');
		if ($request->getUserVar('approval')) {
			$confirmationText = __('editor.submission.proofreading.confirmCompletion');
		}
		if ($submissionFile && $submissionFile->getAssocType()==ASSOC_TYPE_REPRESENTATION) {
			import('lib.pkp.controllers.grid.pubIds.form.PKPAssignPublicIdentifiersForm');
			$formTemplate = $this->getAssignPublicIdentifiersFormTemplate();
			$assignPublicIdentifiersForm = new PKPAssignPublicIdentifiersForm($formTemplate, $submissionFile, $request->getUserVar('approval'), $confirmationText);
			if (!$request->getUserVar('confirmed')) {
				// Display assign pub ids modal
				$assignPublicIdentifiersForm->initData();
				return new JSONMessage(true, $assignPublicIdentifiersForm->fetch($request));
			}
			if ($request->getUserVar('approval')) {
				// Asign pub ids
				$assignPublicIdentifiersForm->readInputData();
				$assignPublicIdentifiersForm->execute($request);
			}
			// Update the approval flag
			$submissionFile->setViewable($request->getUserVar('approval')?1:0);
			$submissionFileDao->updateObject($submissionFile);

			// Log the event
			import('lib.pkp.classes.log.SubmissionFileLog');
			import('lib.pkp.classes.log.SubmissionFileEventLogEntry'); // constants
			$user = $request->getUser();
			SubmissionFileLog::logEvent($request, $submissionFile, SUBMISSION_LOG_FILE_SIGNOFF_SIGNOFF, 'submission.event.signoffSignoff', array('file' => $submissionFile->getOriginalFileName(), 'name' => $user->getFullName(), 'username' => $user->getUsername()));

			return DAO::getDataChangedEvent();
		}
		return new JSONMessage(false);
	}

	/**
	 * Show the form to allow the user to select files from previous stages
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function selectFiles($args, $request) {
		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representation = $representationDao->getById(
			$request->getUserVar('representationId'),
			$submission->getId()
		);

		import('lib.pkp.controllers.grid.files.proof.form.ManageProofFilesForm');
		$manageProofFilesForm = new ManageProofFilesForm($submission->getId(), $representation->getId());
		$manageProofFilesForm->initData();
		return new JSONMessage(true, $manageProofFilesForm->fetch($request));
	}

	/**
	 * Edit pub ids
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function identifiers($args, $request) {
		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representation = $representationDao->getById(
			$request->getUserVar('representationId'),
			$submission->getId()
		);
		import('lib.pkp.controllers.tab.pubIds.form.PKPPublicIdentifiersForm');
		$form = new PKPPublicIdentifiersForm($representation);
		$form->initData();
		return new JSONMessage(true, $form->fetch($request));
	}

	/**
	 * Update pub ids
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateIdentifiers($args, $request) {
		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representation = $representationDao->getById(
			$request->getUserVar('representationId'),
			$submission->getId()
		);
		import('lib.pkp.controllers.tab.pubIds.form.PKPPublicIdentifiersForm');
		$form = new PKPPublicIdentifiersForm($representation);
		$form->readInputData();
		if ($form->validate($request)) {
			$form->execute($request);
			return DAO::getDataChangedEvent();
		} else {
			return new JSONMessage(true, $form->fetch($request));
		}
	}

	/**
	 * Clear pub id
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function clearPubId($args, $request) {
		if (!$request->checkCSRF()) return new JSONMessage(false);

		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representation = $representationDao->getById(
			$request->getUserVar('representationId'),
			$submission->getId()
		);
		import('lib.pkp.controllers.tab.pubIds.form.PKPPublicIdentifiersForm');
		$form = new PKPPublicIdentifiersForm($representation);
		$form->clearPubId($request->getUserVar('pubIdPlugIn'));
		return new JSONMessage(true);
	}

	/**
	 * Show dependent files for a monograph file.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function dependentFiles($args, $request) {
		$submission = $this->getSubmission();
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		import('lib.pkp.classes.submission.SubmissionFile'); // Constants
		$submissionFile = $submissionFileDao->getRevision(
			$request->getUserVar('fileId'),
			$request->getUserVar('revision'),
			SUBMISSION_FILE_PROOF,
			$submission->getId()
		);

		// Check if this is a remote galley
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign(array(
			'submissionId' => $this->getSubmission()->getId(),
			'submissionFile' => $submissionFile,
		));
		return new JSONMessage(true, $templateMgr->fetch('controllers/grid/catalogEntry/dependentFiles.tpl'));
	}
}

?>
