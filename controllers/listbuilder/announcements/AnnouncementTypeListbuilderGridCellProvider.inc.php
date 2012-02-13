<?php

/**
 * @file classes/controllers/listbuilder/announcements/AnnouncementTypeListbuilderGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementTypeListbuilderGridCellProvider
 * @ingroup controllers_listbuilder_announcements
 *
 * @brief Provide labels for announcement type listbuilder.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class AnnouncementTypeListbuilderGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function AnnouncementTypeListbuilderGridCellProvider() {
		parent::GridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * @see GridCellProvider::getTemplateVarsFromRowColumn()
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$announcementType =& $row->getData(); /* @var $announcementType AnnouncementType */
		$columnId = $column->getId();
		assert((is_a($announcementType, 'AnnouncementType')) && !empty($columnId));

		return array('labelKey' => $announcementType->getId(), 'label' => $announcementType->getData('name'));
	}
}

?>
