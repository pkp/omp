<?php

/**
 * @file controllers/grid/settings/series/SeriesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesGridHandler
 * @ingroup controllers_grid_settings_series
 *
 * @brief Handle series grid requests.
 */

import('controllers.grid.settings.SetupGridHandler');
import('controllers.grid.settings.series.SeriesGridRow');

class SeriesGridHandler extends SetupGridHandler {
	/**
	 * Constructor
	 */
	function SeriesGridHandler() {
		parent::SetupGridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
			array('fetchGrid', 'fetchRow', 'addSeries', 'editSeries', 'updateSeries', 'deleteSeries')
		);
	}


	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);
		$press =& $request->getPress();

		// FIXME are these all required?
		Locale::requireComponents(array(
			LOCALE_COMPONENT_OMP_MANAGER,
			LOCALE_COMPONENT_PKP_COMMON,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_APPLICATION_COMMON
		));

		// Basic grid configuration
		$this->setTitle('series.series');

		// Elements to be displayed in the grid
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		$seriesIterator = $seriesDao->getByPressId($press->getId());

		$gridData = array();
		while ($series =& $seriesIterator->next()) {
			// Get the categories data for the row
			$categories = $seriesDao->getCategories($series->getId(), $press->getId());
			while ($category =& $categories->next()) {
				if (!empty($categoriesString)) $categoriesString .= ', ';
				$categoriesString .= $category->getLocalizedTitle();
				unset($category);
			}
			if (empty($categoriesString)) $categoriesString = __('common.none');

			// Get the series editors dta for the row
			$assignedSeriesEditors =& $seriesEditorsDao->getEditorsBySeriesId($series->getId(), $press->getId());
			if(empty($assignedSeriesEditors)) {
				$editorsString = __('common.none');
			} else {
				$editors = array();
				foreach ($assignedSeriesEditors as $seriesEditor) {
					$user = $seriesEditor['user'];
					$editors[] = $user->getLastName();
				}
				$editorsString = implode(', ', $editors);
			}

			$seriesId = $series->getId();
			$gridData[$seriesId] = array(
				'title' => $series->getLocalizedTitle(),
				'categories' => $categoriesString,
				'editors' => $editorsString
			);
			unset($series);
			unset($editorsString);
		}

		$this->setGridDataElements($gridData);

		// Add grid-level actions
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'addSeries',
				new AjaxModal(
					$router->url($request, null, null, 'addSeries', null, array('gridId' => $this->getId())),
					__('grid.action.addSeries'),
					null,
					true
				),
				__('grid.action.addSeries'),
				'add_category'
			)
		);
		
		// Columns
		$this->addColumn(
			new GridColumn(
				'title',
				'common.title',
				null,
				'controllers/grid/gridCell.tpl'
			)
		);
		$this->addColumn(new GridColumn('categories', 'grid.category.categories'));
		$this->addColumn(new GridColumn('editors', 'user.role.editors'));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return SeriesGridRow
	 */
	function &getRowInstance() {
		$row = new SeriesGridRow();
		return $row;
	}

	//
	// Public Series Grid Actions
	//
	/**
	 * An action to add a new series
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addSeries($args, &$request) {
		// Calling editSeries with an empty ID will add
		// a new series.
		return $this->editSeries($args, $request);
	}

	/**
	 * An action to edit a series
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editSeries($args, &$request) {
		$seriesId = isset($args['seriesId']) ? $args['seriesId'] : null;
		$this->setupTemplate();

		import('controllers.grid.settings.series.form.SeriesForm');
		$seriesForm = new SeriesForm($seriesId);
		$seriesForm->initData($args, $request);
		$json = new JSONMessage(true, $seriesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a series
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateSeries($args, &$request) {
		$seriesId = $request->getUserVar('seriesId');
		$press =& $request->getPress();

		import('controllers.grid.settings.series.form.SeriesForm');
		$seriesForm = new SeriesForm($seriesId);
		$seriesForm->readInputData();

		if ($seriesForm->validate()) {
			$seriesForm->execute($args, $request);
			return DAO::getDataChangedEvent($seriesForm->getSeriesId());
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}

	/**
	 * Delete a series
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteSeries($args, &$request) {
		$press =& $request->getPress();

		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$series = $seriesDao->getById(
			$request->getUserVar('seriesId'),
			$press->getId()
		);

		if (isset($series)) {
			$seriesDao->deleteObject($series);
			return DAO::getDataChangedEvent($series->getId());
		} else {
			Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER)); // manager.setup.errorDeletingItem
			$json = new JSONMessage(false, Locale::translate('manager.setup.errorDeletingItem'));
		}
		return $json->getString();
	}
}

?>
