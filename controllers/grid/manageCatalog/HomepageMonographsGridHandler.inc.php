<?php

/**
 * @file controllers/grid/manageCatalog/HomepageMonographsGridHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HomepageMonographsGridHandler
 * @ingroup controllers_grid_manageCatalog
 *
 * @brief Handle catalog monographs grid requests.
 */

import('controllers.grid.manageCatalog.CatalogMonographsGridHandler');

class HomepageMonographsGridHandler extends CatalogMonographsGridHandler {

	/**
	 * @copydoc GridHandler::initialize()
	 */
	function initialize($request) {
		$this->assocId = (int) $request->getRouter()->getContext($request)->getId();
		$this->assocType = ASSOC_TYPE_PRESS;
		parent::initialize($request);

		import('controllers.modals.submissionMetadata.linkAction.MonographlessCatalogEntryLinkAction');
		$catalogEntryAction = new MonographlessCatalogEntryLinkAction($request);
		$this->addAction($catalogEntryAction);
	}

	/**
	 * @copydoc GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		$press = $request->getRouter()->getContext($request);
		$rangeInfo = $this->getGridRangeInfo($request, $this->getId());
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$monographFactory = $publishedMonographDao->getByPressId($press->getId(), $filter['searchText'], $rangeInfo, null, null, $filter['featured'], $filter['newReleased']);
		return $monographFactory;
	}
}


