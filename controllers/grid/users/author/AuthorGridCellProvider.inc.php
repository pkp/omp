<?php

/**
 * @file controllers/grid/users/author/AuthorGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataObjectGridCellProvider
 * @ingroup controllers_grid_users_author
 *
 * @brief A cell provider that can retrieve labels for submission contributors
 */

import('lib.pkp.controllers.grid.users.author.PKPAuthorGridCellProvider');

class AuthorGridCellProvider extends PKPAuthorGridCellProvider {

	/**
	 * @copydoc PKPAuthorGridCellProvider::getTemplateRowsFromRowColumn()
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$element = $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'isVolumeEditor':
				return array('isChecked' => $element->getIsVolumeEditor());
		}
		parent::getTemplateVarsFromRowColumn($row, $column);
	}
}

?>
