<?php

/**
 * @file controllers/grid/files/finalDraftFiles/FinalDraftFilesGridRow.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FinalDraftFilesGridRow
 * @ingroup controllers_grid_files_finalDraftFiles
 *
 * @brief Handle final draft file grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class FinalDraftFilesGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function FinalDraftFilesGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		// Is this a new row or an existing row?
		$rowId = $this->getId();

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($rowId);
		$monographId = $monographFile->getMonographId();
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);

		if (!empty($rowId) && is_numeric($rowId)) {
			// Actions
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'fileId' => $rowId,
				'monographId' => $monographId,
			);

			$this->addAction(
				new LinkAction(
					'deleteFile',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteFile', null, $actionArgs),
					'grid.action.delete',
					null,
					'delete',
					Locale::translate('common.confirmDelete')
				));
			$this->addAction(
				new LinkAction(
					'moreInfo',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_NOTHING,
					$router->url($request, null, 'informationCenter.FileInformationCenterHandler', 'viewInformationCenter', null, array('monographId' => $monographId, 'itemId' => $rowId, 'stageId' => $monograph->getCurrentStageId())),
					'grid.action.moreInformation',
					null,
					'more_info'
				));
		}
	}
}