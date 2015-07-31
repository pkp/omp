<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import format grid specific classes
import('controllers.grid.catalogEntry.PublicationFormatGridCellProvider');
import('controllers.grid.catalogEntry.PublicationFormatGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class PublicationFormatGridHandler extends GridHandler {
	/** @var Submission */
	var $_submission;

	/** @var boolean */
	var $_inCatalogEntryModal;

	/**
	 * Constructor
	 */
	function PublicationFormatGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR),
			array(
				'fetchGrid', 'fetchRow', 'addFormat',
				'editFormat', 'updateFormat', 'deleteFormat',
				'setAvailable'
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

	/**
	 * Get flag indicating if this grid is loaded
	 * inside a catalog entry modal or not.
	 * @return boolean
	 */
	function getInCatalogEntryModal() {
		return $this->_inCatalogEntryModal;
	}


	//
	// Overridden methods from PKPHandler
	//
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

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize($request) {
		parent::initialize($request);

		$this->setTitle('monograph.publicationFormats');
		$this->setInstructions('editor.monograph.production.publicationFormatDescription');
		$this->_inCatalogEntryModal = (boolean) $request->getUserVar('inCatalogEntryModal');

		// Retrieve the authorized submission.
		$this->setSubmission($this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION));

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_APP_DEFAULT,
			LOCALE_COMPONENT_PKP_DEFAULT,
			LOCALE_COMPONENT_APP_EDITOR
		);

		// Grid actions
		$router = $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
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
		$cellProvider = new PublicationFormatGridCellProvider($submission->getId(), $this->getInCatalogEntryModal());
		$this->addColumn(
			new GridColumn(
				'name',
				'common.name',
				null,
				null,
				$cellProvider,
				array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
			)
		);
		$this->addColumn(
			new GridColumn(
				'proofComplete',
				'grid.catalogEntry.proof',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'isApproved',
				'payment.directSales.catalog',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'isAvailable',
				'grid.catalogEntry.isAvailable',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return PublicationFormatGridRow
	 */
	function getRowInstance() {
		return new PublicationFormatGridRow($this->getSubmission());
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the submission.
	 * @return array
	 */
	function getRequestArgs() {
		$submission = $this->getSubmission();

		return array(
			'submissionId' => $submission->getId(),
			'inCatalogEntryModal' => $this->getInCatalogEntryModal()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function loadData($request, $filter = null) {
		$submission = $this->getSubmission();
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$data = $publicationFormatDao->getBySubmissionId($submission->getId());
		return $data->toAssociativeArray();
	}


	//
	// Public Publication Format Grid Actions
	//

	function addFormat($args, $request) {
		return $this->editFormat($args, $request);
	}

	/**
	 * Edit a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editFormat($args, $request) {
		// Identify the format to be updated
		$representationId = (int) $request->getUserVar('representationId');
		$submission = $this->getSubmission();

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($representationId);

		// Form handling
		import('controllers.grid.catalogEntry.form.PublicationFormatForm');
		$publicationFormatForm = new PublicationFormatForm($submission, $publicationFormat);
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
		// Identify the format to be updated
		$representationId = (int) $request->getUserVar('representationId');
		$submission = $this->getSubmission();

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($representationId);

		// Form handling
		import('controllers.grid.catalogEntry.form.PublicationFormatForm');
		$publicationFormatForm = new PublicationFormatForm($submission, $publicationFormat);
		$publicationFormatForm->readInputData();
		if ($publicationFormatForm->validate()) {
			$representationId = $publicationFormatForm->execute($request);

			if(!isset($publicationFormat)) {
				// This is a new format
				$publicationFormat = $publicationFormatDao->getById($representationId);
				// New added format action notification content.
				$notificationContent = __('notification.addedPublicationFormat');
			} else {
				// Format edit action notification content.
				$notificationContent = __('notification.editedPublicationFormat');
			}

			// Create trivial notification.
			$currentUser = $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row = $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($representationId);
			$row->setData($publicationFormat);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			return new JSONMessage(true, $publicationFormatForm->fetch($request));
		}
	}

	/**
	 * Delete a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function deleteFormat($args, $request) {
		$context = $request->getContext();
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById(
			$request->getUserVar('representationId'),
			null, // $submissionId
			$context->getId() // Make sure to validate the context
		);
		$result = false;
		if ($publicationFormat) {
			$result = $publicationFormatDao->deleteById($publicationFormat->getId());
		}

		if ($result) {
			// Create a tombstone for this publication format.
			import('classes.publicationFormat.PublicationFormatTombstoneManager');
			$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
			$publicationFormatTombstoneMgr->insertTombstoneByPublicationFormat($publicationFormat, $context);

			$currentUser = $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedPublicationFormat')));

			// log the deletion of the format.
			import('lib.pkp.classes.log.SubmissionLog');
			import('classes.log.SubmissionEventLogEntry');
			SubmissionLog::logEvent($request, $this->getSubmission(), SUBMISSION_LOG_PUBLICATION_FORMAT_REMOVE, 'submission.event.publicationFormatRemoved', array('formatName' => $publicationFormat->getLocalizedName()));

			return DAO::getDataChangedEvent();
		} else {
			return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
		}

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

		if ($publicationFormat) {
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

			if ($newAvailableState) {
				// Delete any existing tombstone.
				$publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats(array($publicationFormat));
			} else {
				// Create a tombstone for this publication format.
				$publicationFormatTombstoneMgr->insertTombstoneByPublicationFormat($publicationFormat, $context);
			}

			return DAO::getDataChangedEvent($publicationFormat->getId());
		} else {
			return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
		}

	}
}

?>
