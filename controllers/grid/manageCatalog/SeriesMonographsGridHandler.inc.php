<?php

/**
 * @file controllers/grid/manageCatalog/SeriesMonographsGridHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesMonographsGridHandler
 * @ingroup controllers_grid_manageCatalog
 *
 * @brief Handle series monographs grid requests.
 */

import('controllers.grid.manageCatalog.CatalogMonographsGridHandler');

class SeriesMonographsGridHandler extends CatalogMonographsGridHandler {

	/**
	 * @copydoc GridHandler::initialize()
	 */
	function initialize($request) {
		$this->assocId = (int) $request->getUserVar('seriesId');
		if (!$this->assocId) {
			// Get the press first one, if any.
			$pressId = $request->getRouter()->getContext($request)->getId();
			$seriesFactory = DAORegistry::getDAO('SeriesDAO')->getByPressId($pressId);
			$series = $seriesFactory->next();
			if ($series) $this->assocId = $series->getId();
		}

		$this->assocType = ASSOC_TYPE_SERIES;
		parent::initialize($request);

		$userRoles = (array) $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);
		if (array_intersect(array(ROLE_ID_MANAGER), $userRoles)) {
			import('lib.pkp.classes.linkAction.LinkAction');
			import('lib.pkp.classes.linkAction.request.AjaxModal');

			$dispatcher = $request->getDispatcher();
			$manageSeriesLinkAction =
				new LinkAction(
					'manageSeries',
					new AjaxModal(
						$dispatcher->url($request, ROUTE_PAGE, null, 'management', 'series'),
						__('catalog.manage.manageSeries'),
						'modal_manage',
						true
					),
					__('catalog.manage.manageSeries'),
					'manage'
				);
			$this->addAction($manageSeriesLinkAction);
		}
	}

	/**
	 * @copydoc GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		return array('seriesId' => $this->assocId);
	}

	/**
	 * @copydoc GridHandler::isFilterFormCollapsible()
	 */
	protected function isFilterFormCollapsible() {
		return false;
	}

	/**
	 * @copydoc GridHandler::renderFilter()
	 */
	function renderFilter($request, $filterData = array()) {
		$filterData['seriesId'] = $this->assocId;
		$pressId = $request->getRouter()->getContext($request)->getId();
		$seriesFactory = DAORegistry::getDAO('SeriesDAO')->getByPressId($pressId);
		$seriesOptions = array();
		while ($series = $seriesFactory->next()) {
			$seriesOptions[$series->getId()] = $series->getLocalizedTitle();
		}

		$filterData['seriesOptions'] = $seriesOptions;

		return parent::renderFilter($request, $filterData);
	}

	/**
	 * @copydoc GridHandler::getFilterSelectionData()
	 */
	function getFilterSelectionData($request) {
		$filterData = parent::getFilterSelectionData($request);
		$filterData['seriesId'] = $this->assocId;
		return $filterData;
	}

	/**
	 * @copydoc GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		$pressId = $request->getRouter()->getContext($request)->getId();
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$monographFactory = $publishedMonographDao->getBySeriesId($filter['seriesId'], $pressId, $filter['searchText'], null, null, null, $filter['featured'], $filter['newReleased']);
		return $monographFactory->toAssociativeArray();
	}

	/**
	 * @copydoc CatalogMonographsGridHandler::getIsFeaturedColumnTitle()
	 */
	protected function getIsFeaturedColumnTitle() {
		return 'catalog.manage.seriesFeatured';
	}

	/**
	 * @copydoc CatalogMonographsGridHandler::getNewReleaseColumnTitle()
	 */
	protected function getNewReleaseColumnTitle() {
		return 'catalog.manage.feature.seriesNewRelease';
	}
}

?>
