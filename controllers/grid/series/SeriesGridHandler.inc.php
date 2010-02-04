<?php

/**
 * @file controllers/grid/series/SeriesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesGridHandler
 * @ingroup controllers_grid_series
 *
 * @brief Handle series grid requests.
 */

import('controllers.grid.GridMainHandler');

class SeriesGridHandler extends GridMainHandler {
	/**
	 * Constructor
	 */
	function SeriesGridHandler() {
		parent::GridMainHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return SeriesRowHandler
	 */
	function &getRowHandler() {
		if (!$this->_rowHandler) {
			import('controllers.grid.series.SeriesRowHandler');
			$rowHandler =& new SeriesRowHandler();
			$this->setRowHandler($rowHandler);
		}
		return parent::getRowHandler();
	}

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addSeries'));
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		$press =& $request->getPress();
		$router =& $request->getRouter();
		// Only initialize once
		if ($this->getInitialized()) return;

		// Basic grid configuration
		$this->setId('series');
		$this->setTitle('series.series');

		// Elements to be displayed in the grid
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$divisionDao =& DAORegistry::getDAO('DivisionDAO');
		$series = $seriesDao->getByPressId($press->getId());

		$seriesArray = array();
		while ($seriesItem =& $series->next()) {
			$division = $divisionDao->getById($seriesItem->getDivisionId(), $press->getId());
			if (isset($division)) {
				$divisionTitle = $division->getLocalizedTitle();
			} else {
				$divisionTitle = Locale::translate('common.none');
			}

			$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
			$assignedSeriesEditors =& $seriesEditorsDao->getEditorsBySeriesId($seriesItem->getId(), $press->getId());
			if(empty($assignedSeriesEditors)) {
				$editorsString = Locale::translate('common.none');
			} else {
				foreach ($assignedSeriesEditors as $seriesEditor) {
					$user = $seriesEditor['user'];
					$editorsString .= $user->getLastName() . ', ';
				}
			}

			$seriesId = $seriesItem->getId();
			$seriesArray[$seriesId] = array('title' => $seriesItem->getLocalizedTitle(),
							'division' => $divisionTitle,
							'editors' => $editorsString,
							'affiliation' => $seriesItem->getLocalizedAffiliation());
			unset($seriesItem);
			unset($editorsString);
		}

		$this->setData($seriesArray);

		// Add grid-level actions
		$this->addAction(
			new GridAction(
				'addSeries',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addSeries', null, array('gridId' => $this->getId())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		parent::initialize($request);
	}

	//
	// Public Series Grid Actions
	//
	/**
	 * An action to add a new series
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addSeries(&$args, &$request) {
		// Delegate to the row handler
		import('controllers.grid.series.SeriesRowHandler');
		$seriesRow =& new SeriesRowHandler();

		// Calling editSeries with an empty row id will add
		// a new series.
		$seriesRow->editSeries($args, $request);
	}
}