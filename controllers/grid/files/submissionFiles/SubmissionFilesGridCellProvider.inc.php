<?php

/**
 * @file classes/controllers/grid/files/SubmissionFilesGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridCellProvider
 * @ingroup controllers_grid_files_submissionFiles
 *
 * @brief Class for a cell provider that can retrieve labels from submission files
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class SubmissionFilesGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function SubmissionFilesGridCellProvider() {
		parent::DataObjectGridCellProvider();
	}

	/**
	 * Get cell actions associated with this row/column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of LegacyLinkAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		if ($column->getId() == 'name') {
			$monographFile =& $row->getData();
			$router =& $request->getRouter();
			$dispatcher =& $router->getDispatcher();

			$fileName = $monographFile->getLocalizedName() != '' ? $monographFile->getLocalizedName() : Locale::translate('common.untitled');
			if ($monographFile->getRevision() > 1) $fileName .= ' (' . $monographFile->getRevision() . ')'; // Add revision number to label
			if (empty($fileName) ) $fileName = Locale::translate('common.untitled');

			$actionArgs = array(
				'monographId' => $monographFile->getMonographId(),
				'fileId' => $monographFile->getFileId()
			);

			$genreDao =& DAORegistry::getDAO('GenreDAO');
			$genre = $genreDao->getById($monographFile->getGenreId());
			$action =& new LegacyLinkAction(
							'downloadFile',
							LINK_ACTION_MODE_LINK,
							LINK_ACTION_TYPE_NOTHING,
							$router->url($request, null, null, 'downloadFile', null, $actionArgs),
							null,
							$fileName,
							($genre->getCategory() == GENRE_CATEGORY_ARTWORK)?'imageFile':null
						);
			return array($action);
		}
		return parent::getCellActions($request, $row, $column, $position);
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$monographFile =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($monographFile, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'name':
				$fileName = $monographFile->getLocalizedName() != '' ? $monographFile->getLocalizedName() : Locale::translate('common.untitled');
				if ($monographFile->getRevision() > 1) $fileName .= ' (' . $monographFile->getRevision() . ')'; // Add revision number to label
				if ( empty($title) ) $title = Locale::translate('common.untitled');
				return array('label' => $fileName);
				break;
			case 'fileType':
				return array('label' => $monographFile->getExtension());
				break;
			case 'type':
				$genreDao =& DAORegistry::getDAO('GenreDAO');
				$genre = $genreDao->getById($monographFile->getGenreId());
				return array('label' => $genre->getLocalizedName());
				break;
		}
	}
}