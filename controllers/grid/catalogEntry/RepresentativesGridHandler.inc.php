<?php

/**
 * @file controllers/grid/catalogEntry/RepresentativesGridHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RepresentativesGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for representatives.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');


// import format grid specific classes
import('controllers.grid.catalogEntry.RepresentativesGridCellProvider');
import('controllers.grid.catalogEntry.RepresentativesGridCategoryRow');
import('controllers.grid.catalogEntry.RepresentativesGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class RepresentativesGridHandler extends CategoryGridHandler {
	/** @var Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
				array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT),
				array('fetchGrid', 'fetchCategory', 'fetchRow', 'addRepresentative', 'editRepresentative',
				'updateRepresentative', 'deleteRepresentative'));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this grid.
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the Monograph
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph = $monograph;
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
	 * @copydoc CategoryGridHandler::initialize
	 */
	function initialize($request, $args = null) {
		parent::initialize($request, $args);

		// Retrieve the authorized monograph.
		$this->setMonograph($this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH));

		$representativeId = (int) $request->getUserVar('representativeId'); // set if editing or deleting a representative entry

		if ($representativeId != '') {
			$representativeDao = DAORegistry::getDAO('RepresentativeDAO');
			$representative = $representativeDao->getById($representativeId, $this->getMonograph()->getId());
			if (!isset($representative)) {
				fatalError('Representative referenced outside of authorized monograph context!');
			}
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
		$this->setTitle('grid.catalogEntry.representatives');

		// Grid actions
		$router = $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addRepresentative',
				new AjaxModal(
					$router->url($request, null, null, 'addRepresentative', null, $actionArgs),
					__('grid.action.addRepresentative'),
					'modal_add_item'
				),
				__('grid.action.addRepresentative'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new RepresentativesGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'name',
				'grid.catalogEntry.representativeName',
				null,
				null,
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'role',
				'grid.catalogEntry.representativeRole',
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
	 * @return RepresentativesGridRow
	 */
	function getRowInstance() {
		return new RepresentativesGridRow($this->getMonograph());
	}

	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 * @return RepresentativesGridCategoryRow
	 */
	function getCategoryRowInstance() {
		return new RepresentativesGridCategoryRow();
	}

	/**
	 * @see CategoryGridHandler::loadCategoryData()
	 */
	function loadCategoryData($request, &$category, $filter = null) {
		$representativeDao = DAORegistry::getDAO('RepresentativeDAO');
		if ($category['isSupplier']) {
			$representatives = $representativeDao->getSuppliersByMonographId($this->getMonograph()->getId());
		} else {
			$representatives = $representativeDao->getAgentsByMonographId($this->getMonograph()->getId());
		}
		return $representatives->toAssociativeArray();
	}

	/**
	 * @see CategoryGridHandler::getCategoryRowIdParameterName()
	 */
	function getCategoryRowIdParameterName() {
		return 'representativeCategoryId';
	}

	/**
	 * @see CategoryGridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph = $this->getMonograph();
		return array_merge(
			parent::getRequestArgs(),
			array('submissionId' => $monograph->getId())
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function loadData($request, $filter = null) {
		// set our labels for the two Representative categories
		$categories = array(
				array('name' => 'grid.catalogEntry.agentsCategory', 'isSupplier' => false),
				array('name' => 'grid.catalogEntry.suppliersCategory', 'isSupplier' => true)
			);

		return $categories;
	}


	//
	// Public Representatives Grid Actions
	//

	function addRepresentative($args, $request) {
		return $this->editRepresentative($args, $request);
	}

	/**
	 * Edit a representative entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editRepresentative($args, $request) {
		// Identify the representative entry to be updated
		$representativeId = (int) $request->getUserVar('representativeId');
		$monograph = $this->getMonograph();

		$representativeDao = DAORegistry::getDAO('RepresentativeDAO');
		$representative = $representativeDao->getById($representativeId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.RepresentativeForm');
		$representativeForm = new RepresentativeForm($monograph, $representative);
		$representativeForm->initData();

		return new JSONMessage(true, $representativeForm->fetch($request));
	}

	/**
	 * Update a representative entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateRepresentative($args, $request) {
		// Identify the representative entry to be updated
		$representativeId = $request->getUserVar('representativeId');
		$monograph = $this->getMonograph();

		$representativeDao = DAORegistry::getDAO('RepresentativeDAO');
		$representative = $representativeDao->getById($representativeId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.RepresentativeForm');
		$representativeForm = new RepresentativeForm($monograph, $representative);
		$representativeForm->readInputData();
		if ($representativeForm->validate()) {
			$representativeId = $representativeForm->execute();

			if(!isset($representative)) {
				// This is a new entry
				$representative = $representativeDao->getById($representativeId, $monograph->getId());
				// New added entry action notification content.
				$notificationContent = __('notification.addedRepresentative');
			} else {
				// entry edit action notification content.
				$notificationContent = __('notification.editedRepresentative');
			}

			// Create trivial notification.
			$currentUser = $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row = $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($representativeId);
			$row->setData($representative);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent($representativeId, (int) $representative->getIsSupplier());

		} else {
			return new JSONMessage(true, $representativeForm->fetch($request));
		}
	}

	/**
	 * Delete a representative entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function deleteRepresentative($args, $request) {
		\AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_APP_MANAGER);

		// Identify the representative entry to be deleted
		$representativeId = $request->getUserVar('representativeId');

		$representativeDao = DAORegistry::getDAO('RepresentativeDAO');
		$representative = $representativeDao->getById($representativeId, $this->getMonograph()->getId());

		if (!$representative) {
			return new JSONMessage(false, __('api.404.resourceNotFound'));
		}

		// Don't allow a representative to be deleted if they are associated
		// with a publication format's market metadata
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		foreach ($submission->getData('publications') as $publication) {
			foreach ($publication->getData('publicationFormats') as $publicationFormat) {
				$markets = DAORegistry::getDAO('MarketDAO')->getByPublicationFormatId($publicationFormat->getId())->toArray();
				foreach ($markets as $market) {
					if (in_array($representative->getId(), [$market->getAgentId(), $market->getSupplierId()])) {
						return new JSONMessage(false, __('manager.representative.inUse'));
					}
				}
			}
		}

		$result = $representativeDao->deleteObject($representative);

		if ($result) {
			$currentUser = $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedRepresentative')));
			return DAO::getDataChangedEvent($representative->getId(), (int) $representative->getIsSupplier());
		} else {
			return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
		}
	}
}


