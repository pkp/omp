<?php

/**
 * @file controllers/grid/files/signoff/SignoffFilesGridCellProvider.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffFilesGridCellProvider
 * @ingroup controllers_grid_files_signoff
 *
 * @brief Cell provider for name column of a signoff (editor/auditor) grid (i.e. editorial/production).
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class SignoffFilesGridCellProvider extends GridCellProvider {
	/** @var int */
	var $_monographId;

	/** @var int */
	var $_stageId;

	/**
	 * Constructor
	 */
	function SignoffFilesGridCellProvider($monographId, $stageId) {
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


	//
	// Implemented methods from GridCellProvider.
	//
	/**
	 * @see GridCellProvider::getCellActions()
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		$actions = array();
		$monographFile =& $row->getData();
		assert(is_a($monographFile, 'MonographFile'));

		switch ($column->getId()) {
			case 'name':
				import('lib.pkp.controllers.grid.files.FileNameGridColumn');
				$fileNameColumn = new FileNameGridColumn(true, WORKFLOW_STAGE_ID_PRODUCTION, true);

				// Set the row data as expected in FileNameGridColumn object.
				$rowData = array('submissionFile' => $monographFile);
				$row->setData($rowData);
				$actions = $fileNameColumn->getCellActions($request, $row);

				// Back the row data as expected by the signoff grid.
				$row->setData($monographFile);
				break;
			case 'approved';
				$actions[] = $this->_getApprovedCellAction($request, $monographFile, $this->getCellState($row, $column));
				break;
		}
		return $actions;
	}

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, &$column) {
		$columnId = $column->getId();
		$rowData =& $row->getData(); /* @var $rowData MonographFile */
		assert(is_a($rowData, 'MonographFile') && !empty($columnId));
		switch ($columnId) {
			case 'name':
				// The cell will contain only a link action. See getCellActions().
				return array('status' => '', 'label' => '');
			case 'approved':
				return array('status' => $this->getCellState($row, $column));
			default:
				return array('status' => '');
		};
	}


	function getCellState(&$row, &$column) {
		$columnId = $column->getId();
		$rowData =& $row->getData();
		switch ($columnId) {
			case 'approved':
				return $rowData->getViewable()?'completed':'new';
			default:
				return '';
		}
	}

	/**
	 * Get the approved column cell action, based on stage id.
	 * @param $request Request
	 * @param $monographFile MonographFile
	 */
	function _getApprovedCellAction(&$request, &$monographFile, $cellState) {
		$router =& $request->getRouter();
		$actionArgs = array(
			'submissionId' => $monographFile->getMonographId(),
			'fileId' => $monographFile->getFileId(),
			'stageId' => $this->getStageId()
		);
		import('lib.pkp.classes.linkAction.LinkAction');
		import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');

		switch ($this->getStageId()) {
			case WORKFLOW_STAGE_ID_EDITING:
				$remoteActionUrl = $router->url(
					$request, null, 'grid.files.copyedit.CopyeditingFilesGridHandler',
					'approveCopyedit', null, $actionArgs
				);
				if ($cellState == 'new') {
					$approveText = __('editor.monograph.editorial.approveCopyeditDescription');
				} else {
					$approveText = __('editor.monograph.editorial.disapproveCopyeditDescription');
				}

				$modal = new RemoteActionConfirmationModal($approveText, __('editor.monograph.editorial.approveCopyeditFile'),
					$remoteActionUrl, 'modal_approve_file');

				return new LinkAction('approveCopyedit-' . $monographFile->getFileId(),
					$modal, __('editor.monograph.decision.approveProofs'), 'task ' . $cellState);

			case WORKFLOW_STAGE_ID_PRODUCTION:
				$remoteActionUrl = $router->url(
					$request, null, 'modals.editorDecision.EditorDecisionHandler',
					'saveApproveProof', null, $actionArgs
				);

				if ($cellState == 'new') {
					$approveText = __('editor.monograph.decision.approveProofsDescription');
				} else {
					$approveText = __('editor.monograph.decision.disapproveProofsDescription');
				}

				$modal = new RemoteActionConfirmationModal($approveText, __('editor.monograph.decision.approveProofs'),
					$remoteActionUrl, 'modal_approve_file');

				$toolTip = ($cellState == 'completed') ? __('grid.action.pageProofApproved') : null;
				return new LinkAction('approveProof-' . $monographFile->getFileId(),
					$modal, __('editor.monograph.decision.approveProofs'), 'task ' . $cellState, $toolTip);
		}
	}
}

?>
