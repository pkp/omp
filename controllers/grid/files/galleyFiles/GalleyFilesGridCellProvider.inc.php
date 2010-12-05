<?php

/**
 * @file controllers/grid/files/GalleyFiles/GalleyFilesGridCellProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GalleyFilesGridCellProvider
 * @ingroup controllers_grid_files_galleyFiles
 *
 * @brief Subclass class for a GalleyFiles grid column's cell provider
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class GalleyFilesGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function GalleyFilesGridCellProvider() {
		parent::GridCellProvider();
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
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			if($signoff->getFileId()) {
				$monographFile =& $submissionFileDao->getLatestRevision($signoff->getFileId());
				$fileId = $signoff->getFileId();

				$router =& $request->getRouter();
				$actionArgs = array(
					'gridId' => $row->getGridId(),
					'monographId' => $monographFile->getMonographId(),
					'fileId' => $fileId
				);

				$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
				$userDao =& DAORegistry::getDAO('UserDAO');
				$userGroup =& $userGroupDao->getById($signoff->getUserGroupId());
				$user =& $userDao->getUser($signoff->getUserId());

				$label = $user->getFullName() . " (" . $userGroup->getLocalizedName() . ") - " . $monographFile->getLocalizedName();
				$action =& new LinkAction(
								'downloadFile',
								LINK_ACTION_MODE_LINK,
								LINK_ACTION_TYPE_NOTHING,
								$router->url($request, null, null, 'downloadFile', null, $actionArgs),
								null,
								$label
							);
				return array($action);
			} else {
				$fileId = $monographFile = null;
				return null;
			}
		}

		return parent::getCellActions($request, $row, $column, $position);
	}

	/**
	 * Gathers the state of a given cell given a $row/$column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 */
	function getCellState(&$row, &$column) {
		$columnId = $column->getId();
		$element =& $row->getData();
		assert(is_a($element, 'Signoff') && !empty($columnId));

		// Numeric means its a userGroupId column
		if (is_numeric($columnId)) {
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$userGroup =& $userGroupDao->getById($columnId);
			$roleId = $userGroup->getRoleId();
			if ($roleId == ROLE_ID_PRESS_ASSISTANT) {
				// If there is no file, leave blank
				if(!$element->getFileId()) {
					return '';
				} else {
					// Check if the user has read the file
					$viewsDao =& DAORegistry::getDAO('ViewsDAO');
					$lastViewed = $viewsDao->getLastViewDate(ASSOC_TYPE_MONOGRAPH_FILE, $element->getFileId(), $element->getUserId());
					if($lastViewed) {
						return 'accepted'; // Green checkbox
					} else return 'new'; // Gray checkbox
				}

			} else if ($roleId == ROLE_ID_PRESS_MANAGER || $roleId == ROLE_ID_SERIES_EDITOR || $roleId == ROLE_ID_AUTHOR) {
				if ($columnId == $element->getUserGroupId()) {
					// If a file was uploaded, show that this column's user group is the submitter
					if($element->getFileId()) {
						return 'uploaded'; // File folder
					}
					if($element->getDateUnderway() > Core::getCurrentDate()) {
						// Else If the date due is past today's date, show a red envelope icon
						return 'overdue'; // Red envelope
					} else return 'new'; // Gray checkbox
				}
				// If column is not the submitter, cell is always empty.
				return '';
			}
		}
	}

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, &$column) {
		$element =& $row->getData();  /* @var $element Signoff */
		$columnId = $column->getId();
		assert(is_a($element, 'Signoff') && !empty($columnId));
		// Numeric columns indicate a user group column.
		if ( is_numeric($columnId) ) {
			$state = $this->getCellState($row, $column);
			return array('status' => $state);
		}

		// all other columns
		switch ($columnId) {
			case 'name':
				$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
				$userDao =& DAORegistry::getDAO('UserDAO');
				$userGroup =& $userGroupDao->getById($element->getUserGroupId());
				$user =& $userDao->getUser($element->getUserId());

				$label = $user->getFullName() . " (" . $userGroup->getLocalizedName() . ")";
				return array('label' => $label);
		}
	}
}