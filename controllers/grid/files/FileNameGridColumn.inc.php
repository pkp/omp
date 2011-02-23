<?php

/**
 * @file controllers/grid/files/FileNameGridColumn.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileNameGridColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a file name column.
 */

import('lib.pkp.classes.controllers.grid.GridColumn');

class FileNameGridColumn extends GridColumn {

	/**
	 * Constructor
	 */
	function FileNameGridColumn() {
		import('lib.pkp.classes.controllers.grid.ColumnBasedGridCellProvider');
		$cellProvider = new ColumnBasedGridCellProvider();
		parent::GridColumn('name',	'common.name', null, 'controllers/grid/gridCell.tpl', $cellProvider);
	}


	//
	// Public methods
	//
	/**
	 * Method expected by ColumnBasedGridCellProvider
	 * to render a cell in this column.
	 *
	 * @see ColumnBasedGridCellProvider::getTemplateVarsFromRowColumn()
	 */
	function getTemplateVarsFromRow($row) {
		return array('label' => $this->_getFileLabel($row->getData()));
	}


	//
	// Override methods from GridColumn
	//
	/**
	 * @see GridColumn::getCellActions()
	 */
	function getCellActions(&$request, &$row, $position = GRID_ACTION_POSITION_DEFAULT) {
		// Retrieve the default cell actions.
		$cellActions = parent::getCellActions($request, $row, $position);

		// Create the cell action to download a file.
		$router =& $request->getRouter();
		$monographFile =& $row->getData();
		$actionArgs = array(
			'monographId' => $monographFile->getMonographId(),
			'fileStage' => $monographFile->getFileStage(),
			'fileId' => $monographFile->getFileId()
		);
		$cellActions[] =& new LegacyLinkAction(
				'downloadFile',
				LINK_ACTION_MODE_LINK,
				LINK_ACTION_TYPE_NOTHING,
				$router->url($request, null, 'api.file.FileApiHandler', 'downloadFile', null, $actionArgs),
				null,
				$this->_getFileLabel($monographFile),
				is_a($monographFile, 'ArtworkFile')?'imageFile':null);

		return $cellActions;
	}


	//
	// Private helper methods
	//
	/**
	 * Build a file name label for the given monograph file.
	 * @param $monographFile MonographFile
	 * @return string
	 */
	function _getFileLabel(&$monographFile) {
		assert(is_a($monographFile, 'MonographFile'));

		// Retrieve the localized file name as label.
		$fileName = $monographFile->getLocalizedName();

		// If we have no file name then use a default name.
		if (empty($fileName)) $fileName = Locale::translate('common.untitled');

		// Add the revision number to the label if we have more than one revision.
		if ($monographFile->getRevision() > 1) $fileName .= ' (' . $monographFile->getRevision() . ')';

		return $fileName;
	}
}