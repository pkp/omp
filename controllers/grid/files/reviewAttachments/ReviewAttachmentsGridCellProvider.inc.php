<?php

/**
 * @file controllers/grid/files/reviewAttachments/ReviewAttachmentsGridCellProvder.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridCellProvider
 * @ingroup controllers_grid_files_reviewAttachments
 *
 * @brief Subclass class for a ReviewAttachments grid column's cell provider
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class ReviewAttachmentsGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function ReviewAttachmentsGridCellProvider() {
		parent::DataObjectGridCellProvider();
	}

	/**
	 * Get cell actions associated with this row/column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of LegacyLinkAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		if ( $column->getId() == 'files' ) {
			$monographFile =& $row->getData();
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $row->getGridId(),
				'monographId' => $monographFile->getMonographId(),
				'fileId' => $monographFile->getFileId()
			);
			$action =& new LegacyLinkAction(
							'downloadFile',
							LINK_ACTION_MODE_LINK,
							LINK_ACTION_TYPE_NOTHING,
							$router->url($request, null, null, 'downloadFile', null, $actionArgs),
							null,
							$monographFile->getOriginalFileName()
						);
			return array($action);
		}
		return parent::getCellActions($request, $row, $column, $position);
	}

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, &$column) {
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'MonographFile') && !empty($columnId));

		// all other columns
		switch ($columnId) {
			case 'select':
				return array('rowId' => $element->getFileId());
			case 'files':
				return array('label' => $element->getOriginalFileName());
		}
	}
}