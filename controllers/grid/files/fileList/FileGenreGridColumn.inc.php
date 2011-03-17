<?php
/**
 * @file controllers/grid/files/fileList/FileGenreGridColumn.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileGenreGridColumn
 * @ingroup controllers_grid_files_fileList
 *
 * @brief Implements a file name column.
 */

import('lib.pkp.classes.controllers.grid.GridColumn');

class FileGenreGridColumn extends GridColumn {

	/**
	 * Constructor
	 */
	function FileGenreGridColumn() {
		import('lib.pkp.classes.controllers.grid.ColumnBasedGridCellProvider');
		$cellProvider = new ColumnBasedGridCellProvider();
		parent::GridColumn('type', 'common.type', null, 'controllers/grid/gridCell.tpl', $cellProvider);
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
		$monographFile =& $row->getData();
		assert(is_a($monographFile, 'MonographFile'));

		// Retrieve the genre label for the monograph file.
		$genreDao =& DAORegistry::getDAO('GenreDAO');
		$genre = $genreDao->getById($monographFile->getGenreId());
		return array('label' => $genre->getLocalizedName());
	}
}

?>
