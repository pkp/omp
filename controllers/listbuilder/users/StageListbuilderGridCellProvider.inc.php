<?php

/**
 * @file controllers/listbuilder/users/StageListbuilderGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageListbuilderGridCellProvider
 * @ingroup controllers_listbuilder_users
 *
 * @brief Class for retrieve stage name and id to a row.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class StageListbuilderGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function StageListbuilderGridCellProvider() {
		parent::GridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * @see GridCellProvider::getTemplateVarsFromRowColumn()
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$stages = $userGroupDao->getWorkflowStageTranslationKeys();

		$stageName = $row->getData();
		$stageId = array_search($stageName, $stages);

		$columnId = $column->getId();

		assert(in_array($stageId, array_flip($stages)) && !empty($columnId));

		if($columnId == 'stage') {
			return array('labelKey' => $stageId, 'label' => Locale::translate($stageName));
		}
		// we got an unexpected column
		assert(false);
	}
}

?>
