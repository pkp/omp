<?php

/**
 * @file controllers/grid/catalogEntry/IdentificationCodeGridHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IdentificationCodeGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for identification codes.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import format grid specific classes
import('controllers.grid.catalogEntry.IdentificationCodeGridCellProvider');
import('controllers.grid.catalogEntry.IdentificationCodeGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class IdentificationCodeGridHandler extends GridHandler {
	/** @var Submission */
	var $_submission;

	/** @var Publication */
	var $_publication;

	/** @var PublicationFormat */
	var $_publicationFormat;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
				array(ROLE_ID_MANAGER),
				array('fetchGrid', 'fetchRow', 'addCode', 'editCode',
				'updateCode', 'deleteCode'));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the submission associated with this grid.
	 * @return Submission
	 */
	function getSubmission() {
		return $this->_submission;
	}

	/**
	 * Set the Submission
	 * @param Submission
	 */
	function setSubmission($submission) {
		$this->_submission = $submission;
	}

	/**
	 * Get the publication associated with this grid.
	 * @return Publication
	 */
	function getPublication() {
		return $this->_publication;
	}

	/**
	 * Set the Publication
	 * @param Publication
	 */
	function setPublication($publication) {
		$this->_publication = $publication;
	}

	/**
	 * Get the publication format assocated with these identification codes
	 * @return PublicationFormat
	 */
	function getPublicationFormat() {
		return $this->_publicationFormat;
	}

	/**
	 * Set the publication format
	 * @param PublicationFormat
	 */
	function setPublicationFormat($publicationFormat) {
		$this->_publicationFormat = $publicationFormat;
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
		import('lib.pkp.classes.security.authorization.PublicationAccessPolicy');
		$this->addPolicy(new PublicationAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @copydoc GridHandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request, $args);

		// Retrieve the authorized submission.
		$this->setSubmission($this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION));
		$this->setPublication($this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION));
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$representationId = null;

		// Retrieve the associated publication format for this grid.
		$identificationCodeId = (int) $request->getUserVar('identificationCodeId'); // set if editing or deleting a code

		if ($identificationCodeId != '') {
			$identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO');
			$identificationCode = $identificationCodeDao->getById($identificationCodeId, $this->getPublication()->getId());
			if ($identificationCode) {
				$representationId = $identificationCode->getPublicationFormatId();
			}
		} else { // empty form for new Code
			$representationId = (int) $request->getUserVar('representationId');
		}

		$publicationFormat = $publicationFormatDao->getById($representationId, $this->getPublication()->getId());

		if ($publicationFormat) {
			$this->setPublicationFormat($publicationFormat);
		} else {
			fatalError('The publication format is not assigned to authorized submission!');
		}

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_APP_DEFAULT,
			LOCALE_COMPONENT_PKP_DEFAULT
		);

		// Basic grid configuration
		$this->setTitle('submission.publicationFormat.productIdentifierType');

		// Grid actions
		$router = $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addCode',
				new AjaxModal(
					$router->url($request, null, null, 'addCode', null, $actionArgs),
					__('grid.action.addCode'),
					'modal_add_item'
				),
				__('grid.action.addCode'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new IdentificationCodeGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'value',
				'grid.catalogEntry.identificationCodeValue',
				null,
				null,
				$cellProvider,
				array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
			)
		);
		$this->addColumn(
			new GridColumn(
				'code',
				'grid.catalogEntry.identificationCodeType',
				null,
				null,
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return IdentificationCodeGridRow
	 */
	function getRowInstance() {
		return new IdentificationCodeGridRow($this->getSubmission());
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the submission.
	 * @return array
	 */
	function getRequestArgs() {
		return [
			'submissionId' => $this->getSubmission()->getId(),
			'publicationId' => $this->getPublication()->getId(),
			'representationId' => $this->getPublicationFormat()->getId()
		];
	}

	/**
	 * @see GridHandler::loadData
	 */
	function loadData($request, $filter = null) {
		$publicationFormat = $this->getPublicationFormat();
		$identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO');
		$data = $identificationCodeDao->getByPublicationFormatId($publicationFormat->getId());
		return $data->toArray();
	}


	//
	// Public Identification Code Grid Actions
	//
	/**
	 * Edit a new (empty) code
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function addCode($args, $request) {
		return $this->editCode($args, $request);
	}

	/**
	 * Edit a code
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editCode($args, $request) {
		// Identify the code to be updated
		$identificationCodeId = (int) $request->getUserVar('identificationCodeId');
		$submission = $this->getSubmission();

		$identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO');
		$identificationCode = $identificationCodeDao->getById($identificationCodeId, $this->getPublication()->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.IdentificationCodeForm');
		$identificationCodeForm = new IdentificationCodeForm($submission, $this->getPublication(), $identificationCode);
		$identificationCodeForm->initData();

		return new JSONMessage(true, $identificationCodeForm->fetch($request));
	}

	/**
	 * Update a code
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateCode($args, $request) {
		// Identify the code to be updated
		$identificationCodeId = $request->getUserVar('identificationCodeId');
		$submission = $this->getSubmission();

		$identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO');
		$identificationCode = $identificationCodeDao->getById($identificationCodeId, $this->getPublication()->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.IdentificationCodeForm');
		$identificationCodeForm = new IdentificationCodeForm($submission, $this->getPublication(), $identificationCode);
		$identificationCodeForm->readInputData();
		if ($identificationCodeForm->validate()) {
			$identificationCodeId = $identificationCodeForm->execute();

			if(!isset($identificationCode)) {
				// This is a new code
				$identificationCode = $identificationCodeDao->getById($identificationCodeId, $this->getPublication()->getId());
				// New added code action notification content.
				$notificationContent = __('notification.addedIdentificationCode');
			} else {
				// code edit action notification content.
				$notificationContent = __('notification.editedIdentificationCode');
			}

			// Create trivial notification.
			$currentUser = $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row = $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($identificationCodeId);
			$row->setData($identificationCode);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			return new JSONMessage(true, $identificationCodeForm->fetch($request));
		}
	}

	/**
	 * Delete a code
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function deleteCode($args, $request) {

		// Identify the code to be deleted
		$identificationCodeId = $request->getUserVar('identificationCodeId');

		$identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO');
		$identificationCode = $identificationCodeDao->getById($identificationCodeId, $this->getPublication()->getId());
		if ($identificationCode != null) { // authorized

			$result = $identificationCodeDao->deleteObject($identificationCode);

			if ($result) {
				$currentUser = $request->getUser();
				$notificationMgr = new NotificationManager();
				$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedIdentificationCode')));
				return DAO::getDataChangedEvent();
			} else {
				return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
			}
		}
	}
}


