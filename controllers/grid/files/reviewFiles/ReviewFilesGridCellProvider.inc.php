<?php

/**
 * @file controllers/grid/files/editorReviewFileSelection/EditorReviewFileSelectionGridCellProvder.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridCellProvider
 * @ingroup controllers_grid_editorReviewFileSelection
 *
 * @brief Subclass class for a EditorReviewFileSelection grid column's cell provider
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class ReviewFilesGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function ReviewFilesGridCellProvider() {
		parent::GridCellProvider();
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
		// Numeric columns indicate a role-based column.
		if ( is_numeric($columnId) ) {
			if ( $columnId == $element->getUserGroupId() ) {
				// Show that this column's user group is the submitter
				return array('status' => 'uploaded');
			}
			// If column is not the submitter, cell is always empty.
			return array('status' => '');
		}

		// all other columns
		switch ($columnId) {
			case 'select':
				return array('rowId' => $element->getFileId());
			case 'name':
				$label = $element->getLocalizedName() != '' ? $element->getLocalizedName() : Locale::translate('common.untitled');
				return array('label' => $label);
			case 'type':
				$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
				$fileType = $bookFileTypeDao->getById($element->getAssocId());
				return array('label' => $fileType->getLocalizedName());
			}
	}
}