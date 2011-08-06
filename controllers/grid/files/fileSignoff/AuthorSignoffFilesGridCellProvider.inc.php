<?php

/**
 * @file controllers/grid/files/copyedit/AuthorSignoffFilesGridCellProvider.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSignoffFilesGridCellProvider
 * @ingroup controllers_grid_files_authorCopyeditingFiles
 *
 * @brief Cell provider for the response column of a file/signoff grid.
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class AuthorSignoffFilesGridCellProvider extends DataObjectGridCellProvider {
	/* @var Monograph */
	var $_monograph;

	/* @var int */
	var $_stageId;

	/**
	 * Constructor
	 */
	function AuthorSignoffFilesGridCellProvider(&$monograph, $stageId) {
		$this->_monograph =& $monograph;
		$this->_stageId = $stageId;
		parent::DataObjectGridCellProvider();
	}

	/**
	 * Get the monograph this provider refers to.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the Stage id.
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the signoff for the row.
	 * @param $row GridRow
	 * @return Signoff
	 */
	function &getSignoff(&$row) {
		$rowData =& $row->getData();
		assert(is_a($rowData['signoff'], 'Signoff'));
		return $rowData['signoff'];
	}

	/**
	 * Get the file for the row.
	 * @param $row GridRow
	 * @return MonographFile
	 */
	function &getSubmissionFile(&$row) {
		$rowData =& $row->getData();
		assert(is_a($rowData['submissionFile'], 'MonographFile'));
		return $rowData['submissionFile'];
	}
	/**
	 * Gathers the state of a given cell given a $row/$column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 */
	function getCellState(&$row, &$column) {
		$columnId = $column->getId();

		if ($columnId == 'response') {
			$signoff =& $this->getSignoff($row);

			// If a file was uploaded, show a ticked checkbox
			if($signoff->getDateCompleted()) {
				return 'completed';
			} else {
				return 'new';
			}
		} else {
			return null;
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
		return array('status' => $this->getCellState($row, $column));
	}

	/**
	 * Get cell actions associated with this row/column combination
	 * Adds a link to the file if there is an uploaded file present
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of LinkAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		if ($column->getId() == 'response') {
			$signoff =& $this->getSignoff($row);
			$monograph =& $this->getMonograph();
			if (!$signoff->getDateCompleted()) {
				import('controllers.api.signoff.linkAction.AddSignoffFileLinkAction');
				$addFileAction = new AddSignoffFileLinkAction(
								$request, $monograph->getId(),
								$this->getStageId(), $signoff->getSymbolic(), $signoff->getId(),
								__('submission.upload.signoff'), __('submission.upload.signoff')
								);
				// FIXME: This is not ideal.
				$addFileAction->_image = 'new';
				$addFileAction->_title = null;
				return array($addFileAction);
			}

			import('controllers.api.signoff.linkAction.ReadSignoffLinkAction');
			$readSignoffAction = new ReadSignoffLinkAction($request, $monograph->getId(),
															$this->getStageId(), $signoff->getId(),
															null, null);
			$readSignoffAction->_image = 'uploaded';
			$readSignoffAction->_title = null;
			return array($readSignoffAction);
		}

		return parent::getCellActions($request, $row, $column, $position);
	}
}

?>
