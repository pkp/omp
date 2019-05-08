<?php

/**
 * @file controllers/grid/settings/series/SeriesGridHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesGridHandler
 * @ingroup controllers_grid_settings_series
 *
 * @brief Handle series grid requests.
 */

import('lib.pkp.controllers.grid.settings.SetupGridHandler');
import('controllers.grid.settings.series.SeriesGridRow');

class SeriesGridHandler extends SetupGridHandler {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER),
			array('fetchGrid', 'fetchRow', 'addSeries', 'editSeries', 'updateSeries', 'deleteSeries', 'saveSequence')
		);
	}


	//
	// Overridden template methods
	//
	/*
	 * @copydoc SetupGridHandler::initialize
	 */
	function initialize($request, $args = null) {
		parent::initialize($request, $args);
		$press = $request->getPress();

		// FIXME are these all required?
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APP_MANAGER,
			LOCALE_COMPONENT_PKP_COMMON,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_APP_COMMON
		);

		// Set the grid title.
		$this->setTitle('catalog.manage.series');

		// Elements to be displayed in the grid
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		DAORegistry::getDAO('CategoryDAO'); // Load constants?
		$subEditorsDao = DAORegistry::getDAO('SubEditorsDAO');
		$seriesIterator = $seriesDao->getByPressId($press->getId());

		$gridData = array();
		while ($series = $seriesIterator->next()) {
			// Get the categories data for the row
			$categories = $seriesDao->getCategories($series->getId(), $press->getId());
			$categoriesString = null;
			while ($category = $categories->next()) {
				if (!empty($categoriesString)) $categoriesString .= ', ';
				$categoriesString .= $category->getLocalizedTitle();
			}
			if (empty($categoriesString)) $categoriesString = __('common.none');

			// Get the series editors data for the row
			$assignedSeriesEditors = $subEditorsDao->getBySectionId($series->getId(), $press->getId());
			if(empty($assignedSeriesEditors)) {
				$editorsString = __('common.none');
			} else {
				$editors = array();
				foreach ($assignedSeriesEditors as $seriesEditor) {
					$editors[] = $seriesEditor->getFullName();
				}
				$editorsString = implode(', ', $editors);
			}

			$seriesId = $series->getId();
			$gridData[$seriesId] = array(
				'title' => $series->getLocalizedTitle(),
				'categories' => $categoriesString,
				'editors' => $editorsString,
				'seq' => $series->getSequence()
			);
		}

		$this->setGridDataElements($gridData);

		// Add grid-level actions
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'addSeries',
				new AjaxModal(
					$router->url($request, null, null, 'addSeries', null, array('gridId' => $this->getId())),
					__('grid.action.addSeries'),
					'modal_manage'
				),
				__('grid.action.addSeries'),
				'add_category'
			)
		);

		// Columns
		$this->addColumn(
			new GridColumn(
				'title',
				'common.title'
			)
		);
		$this->addColumn(new GridColumn('categories', 'grid.category.categories'));
		$this->addColumn(new GridColumn('editors', 'user.role.editors'));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @copydoc GridHandler::initFeatures()
	 */
	function initFeatures($request, $args) {
		import('lib.pkp.classes.controllers.grid.feature.OrderGridItemsFeature');
		return array(new OrderGridItemsFeature());
	}

	/**
	 * Get the list of "publish data changed" events.
	 * Used to update the site context switcher upon create/delete.
	 * @return array
	 */
	function getPublishChangeEvents() {
		return array('updateSidebar');
	}

	/**
	 * Get the row handler - override the default row handler
	 * @return SeriesGridRow
	 */
	function getRowInstance() {
		return new SeriesGridRow();
	}

	/**
	 * @copydoc GridHandler::getDataElementSequence()
	 */
	function getDataElementSequence($gridDataElement) {
		return $gridDataElement['seq'];
	}

	/**
	 * @copydoc GridHandler::setDataElementSequence()
	 */
	function setDataElementSequence($request, $rowId, $gridDataElement, $newSequence) {
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$press = $request->getPress();
		$series = $seriesDao->getById($rowId, $press->getId());
		$series->setSequence($newSequence);
		$seriesDao->updateObject($series);
	}

	//
	// Public Series Grid Actions
	//
	/**
	 * An action to add a new series
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addSeries($args, $request) {
		// Calling editSeries with an empty ID will add
		// a new series.
		return $this->editSeries($args, $request);
	}

	/**
	 * An action to edit a series
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editSeries($args, $request) {
		$seriesId = isset($args['seriesId']) ? $args['seriesId'] : null;
		$this->setupTemplate($request);

		import('controllers.grid.settings.series.form.SeriesForm');
		$seriesForm = new SeriesForm($request, $seriesId);
		$seriesForm->initData();
		return new JSONMessage(true, $seriesForm->fetch($request));
	}

	/**
	 * Update a series
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateSeries($args, $request) {
		$seriesId = $request->getUserVar('seriesId');

		import('controllers.grid.settings.series.form.SeriesForm');
		$seriesForm = new SeriesForm($request, $seriesId);
		$seriesForm->readInputData();

		if ($seriesForm->validate()) {
			$seriesForm->execute();
			return DAO::getDataChangedEvent($seriesForm->getSeriesId());
		} else {
			return new JSONMessage(false);
		}
	}

	/**
	 * Delete a series
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function deleteSeries($args, $request) {
		$press = $request->getPress();

		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$series = $seriesDao->getById(
			$request->getUserVar('seriesId'),
			$press->getId()
		);

		if (isset($series)) {
			$seriesDao->deleteObject($series);
			return DAO::getDataChangedEvent($series->getId());
		} else {
			AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER); // manager.setup.errorDeletingItem
			return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
		}
	}
}


