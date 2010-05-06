<?php

/**
 * @file controllers/grid/files/reviewAttachments/ReviewAttachmentsGridCellProvder.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridCellProvider
 * @ingroup controllers_grid_reviewAttachments
 *
 * @brief Subclass class for a ReviewAttachments grid column's cell provider
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class ReviewAttachmentsGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function ReviewAttachmentsGridCellProvider() {
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
			case 'files':
				$label = $element->getOriginalFileName() != '' ? $element->getOriginalFileName() : Locale::translate('common.untitled');
				return array('label' => $label);
		}
	}
}