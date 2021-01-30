<?php
/**
 * @file controllers/grid/settings/series/SeriesGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SeriesGridCellProvider
 * @ingroup controllers_grid_settings_series
 *
* @brief Grid cell provider for series grid
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class SeriesGridCellProvider extends GridCellProvider {

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$element = $row->getData();
		$columnId = $column->getId();
		assert(!empty($columnId));
		switch ($columnId) {
			case 'inactive':
				return array('selected' => $element['inactive']);
		}
		return parent::getTemplateVarsFromRowColumn($row, $column);
	}

	/**
	 * @see GridCellProvider::getCellActions()
	 */
	function getCellActions($request, $row, $column, $position = GRID_ACTION_POSITION_DEFAULT) {
		switch ($column->getId()) {
			case 'inactive':
				$element = $row->getData(); /* @var $element DataObject */

				$router = $request->getRouter();
				import('lib.pkp.classes.linkAction.LinkAction');

				if ($element['inactive']) {
					return array(new LinkAction(
						'activateSeries',
						new RemoteActionConfirmationModal(
							$request->getSession(),
							__('manager.sections.confirmActivateSection'),
							null,
							$router->url(
								$request,
								null,
								'grid.settings.series.SeriesGridHandler',
								'activateSeries',
								null,
								array('seriesKey' => $row->getId())
							)
						)
					));
				} else {
					return array(new LinkAction(
						'deactivateSeries',
						new RemoteActionConfirmationModal(
							$request->getSession(),
							__('manager.sections.confirmDeactivateSection'),
							null,
							$router->url(
								$request,
								null,
								'grid.settings.series.SeriesGridHandler',
								'deactivateSeries',
								null,
								array('seriesKey' => $row->getId())
							)
						)
					));
				}
		}
		return parent::getCellActions($request, $row, $column, $position);
	}
}