<?php

/**
 * @file controllers/grid/users/chapter/ChapterGridCategoryRowCellProvider.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterGridCategoryRowCellProvider
 * @ingroup controllers_grid
 *
 * @brief Chapter grid category rows cell provider.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class ChapterGridCategoryRowCellProvider extends GridCellProvider {

	var $_readOnly;

	/**
	 * Constructor
	 */
	function ChapterGridCategoryRowCellProvider() {
		parent::GridCellProvider();
	}

	/**
	 * @see GridCellProvider::getCellActions()
	 */
	function getCellActions($request, $row, $column) {
		if ($column->getId() == 'name' && !$row->isReadOnly()) {
			$chapter = $row->getData();
			$monograph = $row->getMonograph();

			$router = $request->getRouter();
			$actionArgs = array(
				'submissionId' => $monograph->getId(),
				'chapterId' => $chapter->getId()
			);

			return array(new LinkAction(
					'editChapter',
					new AjaxModal(
						$router->url($request, null, null, 'editChapter', null, $actionArgs),
						__('submission.chapter.editChapter'),
						'modal_edit'
					),
					$chapter->getLocalizedTitle()
				)
			);
		}
	}

	/**
	 * @see GridCellProvider::getTemplateVarsFromRowColumn()
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		// If row is not read only, the cell will contains a link
		// action. See getCellActions() above.
		if ($column->getId() == 'name' && $row->isReadOnly()) {
			$chapter = $row->getData();
			$label = $chapter->getLocalizedTitle();
		} else {
			$label = '';
		}

		return array('label' => $label);
	}
}

?>
