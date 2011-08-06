<?php

/**
 * @file controllers/grid/files/copyedit/CopyeditingFilesGridCellProvider.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingFilesGridCellProvider
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief Subclass class for a CopyeditingFiles grid column's cell provider
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class CopyeditingFilesGridCellProvider extends GridCellProvider {
	/** @var int */
	var $_monographId;

	/** @var int */
	var $_stageId;

	/**
	 * Constructor
	 */
	function CopyeditingFilesGridCellProvider($monographId, $stageId) {
		$this->_monographId = $monographId;
		$this->_stageId = $stageId;
		parent::GridCellProvider();
	}

	//
	// Getters
	//
	function getMonographId() {
		return $this->_monographId;
	}

	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get cell actions associated with this row/column combination
	 * Adds a link to the file if there is an uploaded file present
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of LinkAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		if ($column->getId() == 'name') {
			$signoff =& $row->getData();
			if($signoff->getDateCompleted()) {
				$label = $this->_getLabel($signoff);

				import('controllers.api.signoff.linkAction.ReadSignoffLinkAction');
				$readSignoffAction = new ReadSignoffLinkAction($request, $this->getMonographId(),
																$this->getStageId(), $signoff->getId(),
																$label, $label);

				return array($readSignoffAction);
			}
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
		$signoff =& $row->getData();  /* @var $element Signoff */
		$columnId = $column->getId();
		assert(is_a($signoff, 'Signoff') && !empty($columnId));

		if ($columnId == 'name' && !$signoff->getDateCompleted()) {
			return array('label' => $this->_getLabel($signoff));
		}

		return parent::getTemplateVarsFromRowColumn($row, $column);
	}

	/**
	 * Build the cell label from the signoff object
	 */
	function _getLabel(&$signoff) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$userGroup =& $userGroupDao->getById($signoff->getUserGroupId());
		$user =& $userDao->getUser($signoff->getUserId());

	 	return $user->getFullName() . ' (' . $userGroup->getLocalizedName() . ')';
	}
}

?>
