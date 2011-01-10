<?php

/**
 * @file controllers/grid/files/submissionFiles/SubmissionFilesGridRow.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridRow
 * @ingroup controllers_grid_files_submissionFiles
 *
 * @brief Handle submission file grid row requests.
 */

// Import grid base classes.
import('lib.pkp.classes.controllers.grid.GridRow');

// Import UI base classes.
import('lib.pkp.classes.linkAction.request.ConfirmationModal');
import('lib.pkp.classes.linkAction.request.AjaxModal');

class SubmissionFilesGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function SubmissionFilesGridRow() {
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

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFile =& $submissionFileDao->getLatestRevision($rowId);
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
					new ConfirmationModal(
						'common.confirmDelete',
						null,
						$router->url($request, null, null, 'deleteFile', null,
								array('monographId' => $monographFile->getMonographId(), 'fileId' => $monographFile->getFileId()))
					),
					'grid.action.delete',
					'delete'
				));
			$this->addAction(
				new LinkAction(
					'moreInfo',
					new AjaxModal($router->url($request, null,
						'informationCenter.FileInformationCenterHandler', 'viewInformationCenter', null,
						array('monographId' => $monographFile->getMonographId(), 'itemId' => $monographFile->getFileId(),
								'stageId' => $monographFile->getFileStage()))),
					'grid.action.moreInformation',
					'more_info'
				));
		}
	}
}