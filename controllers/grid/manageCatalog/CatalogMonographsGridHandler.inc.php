<?php

/**
 * @file controllers/grid/manageCatalog/CatalogMonographsGridHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogMonographsGridHandler
 * @ingroup controllers_grid_manageCatalog
 *
 * @brief Handle catalog monographs grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.core.JSONMessage');

class CatalogMonographsGridHandler extends GridHandler {

	/** @var int */
	protected $assocType;

	/** @var int */
	protected $assocId;


	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR),
			array('fetchGrid', 'fetchRows', 'fetchRow', 'toggle', 'saveSequence')
		);
	}


	//
	// Extended PKPHandler methods.
	//
	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		$operation = $request->getRouter()->getRequestedOp($request);
		$siteAccessOps = array('fetchGrid', 'fetchRows', 'saveSequence');
		if (in_array($operation, $siteAccessOps)) {
			import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
			$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		} else {
			import('classes.security.authorization.OmpPublishedMonographAccessPolicy');
			$this->addPolicy(new OmpPublishedMonographAccessPolicy($request, $args, $roleAssignments, 'rowId'));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Extended GridHandler methods.
	//
	/**
	 * @copydoc GridHandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request, $args);
		import('controllers.grid.manageCatalog.CatalogMonographsGridCellProvider');
		$cellProvider = new CatalogMonographsGridCellProvider($this->assocType, $this->assocId, $this->getRequestArgs());

		$this->addColumn(
			new GridColumn(
				'id',
				'common.id',
				null,
				null,
				$cellProvider,
				array('alignment' => COLUMN_ALIGNMENT_LEFT,
					'width' => 5)
			)
		);

		$this->addColumn(
			new GridColumn(
				'title',
				'grid.submission.itemTitle',
				null,
				null,
				$cellProvider
			)
		);

		$this->addColumn(
			new GridColumn(
				'isFeatured',
				$this->getIsFeaturedColumnTitle(),
				null,
				'controllers/grid/common/cell/selectStatusCell.tpl',
				$cellProvider,
				array('width' => 20)
			)
		);

		$this->addColumn(
			new GridColumn(
				'isNewRelease',
				$this->getNewReleaseColumnTitle(),
				null,
				'controllers/grid/common/cell/selectStatusCell.tpl',
				$cellProvider,
				array('width' => 20)
			)
		);
	}

	/**
	 * @copydoc GridHandler::getRowInstance()
	 */
	function getRowInstance() {
		import('controllers.grid.manageCatalog.CatalogMonographsGridRow');
		return new CatalogMonographsGridRow();
	}

	/**
	 * @copydoc GridHandler::getFilterSelectionData()
	 */
	function getFilterSelectionData($request) {
		return array(
			'searchText' => $request->getUserVar('searchText'),
			'featured' => $request->getUserVar('featured'),
			'newReleased' => $request->getUserVar('newReleased')
		);
	}

	/**
	 * @copydoc GridHandler::getFilterForm()
	 */
	function getFilterForm() {
		return 'controllers/grid/manageCatalog/form/catalogMonographsFilterForm.tpl';
	}

	/**
	 * @copydoc GridHandler::renderFilter()
	 */
	function renderFilter($request, $filterData = array()) {
		$press = $request->getRouter()->getContext($request);
		$filterData = array_merge($filterData, array(
			'searchText' => $request->getUserVar('searchText'),
			'featured' => $request->getUserVar('featured'),
			'newReleased' => $request->getUserVar('newReleased')
		));
		return parent::renderFilter($request, $filterData);
	}

	/**
	 * @copydoc GridHandler::initFeatures()
	 */
	function initFeatures($request, $args) {
		import('lib.pkp.classes.controllers.grid.feature.OrderGridItemsFeature');
		import('lib.pkp.classes.controllers.grid.feature.InfiniteScrollingFeature');
		return array(new OrderGridItemsFeature(true, __('catalog.manage.nonOrderable')));
	}

	/**
	 * @copydoc GridHandler::getDataElementSequence()
	 */
	function getDataElementSequence($gridDataElement) {
		$featureDao = DAORegistry::getDAO('FeatureDAO');
		return $featureDao->getSequencePosition($gridDataElement->getId(), $this->assocType, $this->assocId);
	}

	/**
	 * @copydoc GridHandler::setDataElementSequence()
	 */
	function setDataElementSequence($request, $rowId, $gridDataElement, $newSequence) {
		$featureDao = DAORegistry::getDAO('FeatureDAO');
		$featureDao->setSequencePosition($rowId, $this->assocType, $this->assocId, $newSequence);
	}

	//
	// Public handler methods.
	//
	/**
	 * Set featured or new release status for a submission.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function toggle($args, $request) {
		$press = $request->getPress();

		// Identification of item to set new state state on
		$monographId = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH)->getId();
		$assocType = (int) $request->getUserVar('assocType');
		$assocId = (int) $request->getUserVar('assocId');

		// toggle type
		$toggleType = $request->getUserVar('toggleType');

		// Description of new state
		$newState = (int) $request->getUserVar('newState');
		$newSeq = (int) REALLY_BIG_NUMBER;

		// Determine the assoc type and ID to be used.
		switch ($assocType) {
			case ASSOC_TYPE_PRESS:
				// Force assocId to press
				$assocId = $press->getId();
				break;
			case ASSOC_TYPE_CATEGORY:
				// Validate specified assocId
				$categoryDao = DAORegistry::getDAO('CategoryDAO');
				$category = $categoryDao->getById($assocId, $press->getId());
				if (!$category) fatalError('Invalid category!');
				break;
			case ASSOC_TYPE_SERIES:
				// Validate specified assocId
				$seriesDao = DAORegistry::getDAO('SeriesDAO');
				$series = $seriesDao->getById($assocId, $press->getId());
				if (!$series) fatalError('Invalid series!');
				break;
			default:
				fatalError('Invalid feature specified.');
		}

		$returner = null;

		$notificationMgr = new NotificationManager();
		$user = $request->getUser();
		switch ($toggleType) {
			case 'setFeatured':
				$featureDao = DAORegistry::getDAO('FeatureDAO');
				$featureDao->deleteFeature($monographId, $assocType, $assocId);

				// If necessary, insert the new featured state and resequence.
				if ($newState) {
					$featureDao->insertFeature($monographId, $assocType, $assocId, $newSeq);
					$featureDao->resequenceByAssoc($assocType, $assocId);
					$notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('catalog.manage.featuredSuccess')));
				} else {
					$notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('catalog.manage.notFeaturedSuccess')));
				}

				break;
			case 'setNewRelease':
				$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
				$newReleaseDao->deleteNewRelease($monographId, $assocType, $assocId);
				if ($newState) {
					$newReleaseDao->insertNewRelease($monographId, $assocType, $assocId);
					$notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('catalog.manage.newReleaseSuccess')));
				} else {
					$notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('catalog.manage.notNewReleaseSuccess')));
				}
				break;
			default:
				fatalError('Invalid toggle type specified.');
		}

		return DAO::getDataChangedEvent($monographId);
	}


	//
	// Protected methods.
	//
	/**
	 * Get the is featured column title.
	 * @return string Locale key for the column title.
	 */
	protected function getIsFeaturedColumnTitle() {
		return 'catalog.manage.featured';
	}

	/**
	 * Get the new release column title.
	 * @return string Locale key for the column title.
	 */
	protected function getNewReleaseColumnTitle() {
		return 'catalog.manage.feature.newRelease';
	}
}

?>
