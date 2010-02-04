<?php

/**
 * @file controllers/grid/series/SeriesRowHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesRowHandler
 * @ingroup controllers_grid_series
 *
 * @brief Handle series grid row requests.
 */

import('controllers.grid.GridRowHandler');

class SeriesRowHandler extends GridRowHandler {
	/**
	 * Constructor
	 */
	function SeriesRowHandler() {
		parent::GridRowHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(),
				array('editSeries', 'updateSeries', 'deleteSeries'));
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		// Only initialize once
		if ($this->getInitialized()) return;

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		$this->setupTemplate();

		$emptyActions = array();
		// Basic grid row configuration
		$this->addColumn(new GridColumn('title', 'common.title', $emptyActions, 'controllers/grid/gridCellInSpan.tpl'));
		$this->addColumn(new GridColumn('division', 'manager.setup.division'));
		$this->addColumn(new GridColumn('editors', 'user.role.editors'));
		$this->addColumn(new GridColumn('affiliation', 'user.affiliation'));

		parent::initialize($request);
	}

	function setupTemplate() {
		// Load manager translations
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_APPLICATION_COMMON));
	}

	function _configureRow(&$request, $args = null) {
		// assumes row has already been initialized
		// do the default configuration
		parent::_configureRow($request, $args);

		// Actions
		$router =& $request->getRouter();
		$actionArgs = array(
			'gridId' => $this->getGridId(),
			'rowId' => $this->getId()
		);
		$this->addAction(
			new GridAction(
				'editSeries',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_REPLACE,
				$router->url($request, null, 'grid.series.SeriesRowHandler', 'editSeries', null, $actionArgs),
				'grid.action.edit',
				'edit'
			));
		$this->addAction(
			new GridAction(
				'deleteSeries',
				GRID_ACTION_MODE_CONFIRM,
				GRID_ACTION_TYPE_REMOVE,
				$router->url($request, null, 'grid.series.SeriesRowHandler', 'deleteSeries', null, $actionArgs),
				'grid.action.delete',
				'delete'
			));
	}

	//
	// Public Series Row Actions
	//
	/**
	 * An action to edit a series
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editSeries(&$args, &$request) {
		//FIXME: add validation here?
		$this->_configureRow($request, $args);
		$this->setupTemplate();

		import('controllers.grid.series.form.SeriesForm');
		$seriesForm = new SeriesForm($this->getId());

		if ($seriesForm->isLocaleResubmit()) {
			$seriesForm->readInputData();
		} else {
			$seriesForm->initData($args, $request);
		}
		$seriesForm->display();
	}

	/**
	 * Update a series
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateSeries(&$args, &$request) {
		//FIXME: add validation here?
		// -> seriesId must be present and valid
		// -> htmlId must be present and valid
		$this->_configureRow($request, $args);
		$press =& $request->getPress();

		import('controllers.grid.series.form.SeriesForm');
		$seriesForm = new SeriesForm($this->getId());
		$seriesForm->readInputData();

		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		if ($seriesForm->validate()) {
			$seriesForm->execute($args, $request);

			$divisionDao =& DAORegistry::getDAO('DivisionDAO');
			$division = $divisionDao->getById($seriesForm->getData('division'), $press->getId());
			if (isset($division)) {
				$divisionTitle = $division->getLocalizedTitle();
			} else {
				$divisionTitle = Locale::translate('common.none');
			}

			$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
			$assignedSeriesEditors =& $seriesEditorsDao->getEditorsBySeriesId($seriesId, $press->getId());
			if(isset($assignedSeriesEditors)) {
				$editorsString = Locale::translate('common.none');
			} else {
				foreach ($assignedSeriesEditors as $seriesEditor) {
					$user = $seriesEditor['user'];
					$editorsString .= $user->getInitials() . '  ';
				}
			}

			// prepare the grid row data
			$rowData = array('title' => $seriesForm->getData('title'),
							'division' => $divisionTitle,
							'editors' => $editorsString,
							'affiliation' => $seriesForm->getData('affiliation'));
			$this->setId($seriesForm->seriesId);
			$this->setData($rowData);

			$json = new JSON('true', $this->renderRowInternally($request));
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}

	/**
	 * Delete a series
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteSeries(&$args, &$request) {
		// FIXME: add validation here?
		$this->_configureRow($request, $args);

		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$series = $seriesDao->getById($this->getId(), $press->getId());

		if (isset($series)) {
			$seriesDao->deleteObject($series);
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('manager.setup.errorDeletingItem'));
		}
		echo $json->getString();
	}
}