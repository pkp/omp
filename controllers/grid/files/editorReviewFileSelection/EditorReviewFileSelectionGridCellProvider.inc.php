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

class EditorReviewFileSelectionGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function EditorReviewFileSelectionGridCellProvider() {
		parent::GridCellProvider();
	}

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $element mixed
	 * @param $columnId string
	 * @return array
	 */
	function getTemplateVarsFromElement(&$element, $columnId) {
		assert(is_a($element, 'DataObject') && !empty($columnId));
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