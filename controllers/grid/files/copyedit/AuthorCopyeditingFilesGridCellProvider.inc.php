<?php

/**
 * @file controllers/grid/files/copyedit/AuthorCopyeditingFilesGridCellProvider.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorCopyeditingFilesGridCellProvider
 * @ingroup controllers_grid_files_authorCopyeditingFiles
 *
 * @brief Subclass class for a AuthorCopyeditingFiles grid column's cell provider
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class AuthorCopyeditingFilesGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function AuthorCopyeditingFilesGridCellProvider() {
		parent::DataObjectGridCellProvider();
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

		if ($columnId == 'responded') {
			// If a file was uploaded, show a ticked checkbox
			if($element->getFileId()) {
				return 'completed';
			} else return 'new'; // Else show an empty checkbox
		} else return null;
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

		if ($columnId == 'responded') {
			return array('status' => $this->getCellState($row, $column));
		}
	}

	/**
	 * Get cell actions associated with this row/column combination
	 * Adds a link to the file if there is an uploaded file present
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of LinkAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		$signoff =& $row->getData();

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		import('controllers.api.file.linkAction.DownloadFileLinkAction');

		if ($column->getId() == 'name') {
			if($fileId = $signoff->getAssocId()) {
				$monographFile =& $submissionFileDao->getLatestRevision($fileId);
				return array(new DownloadFileLinkAction($request, $monographFile, WORKFLOW_STAGE_ID_EDITING));
			} else {
				return null;
			}
		}

		if ($column->getId() == 'responded') {
			if($fileId = $signoff->getFileId()) {
				// Let the user download the file
				$monographFile =& $submissionFileDao->getLatestRevision($fileId);
				return array(new DownloadFileLinkAction($request, $monographFile, WORKFLOW_STAGE_ID_EDITING));
			} else {
				// If there is no file, let the user open the copyediting file upload modal
				$router =& $request->getRouter();
				$dispatcher =& $router->getDispatcher();
				$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
				$monographFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId());
				$actionArgs = array('monographId' => $monographFile->getMonographId());
				$addCopyeditedFileAction = new LegacyLinkAction(
					'addCopyeditedFile',
					$dispatcher->url($request, ROUTE_COMPONENT, null, 'grid.files.authorCopyeditingFiles.AuthorCopyeditingFilesGridHandler', 'addCopyeditedFile', null, array_merge($actionArgs, array('gridId' => 'authorcopyeditingfilesgrid'))),
					null,
					'new'
				);
				return array($addCopyeditedFileAction);
			}
		}

		return parent::getCellActions($request, $row, $column, $position);
	}
}

?>
