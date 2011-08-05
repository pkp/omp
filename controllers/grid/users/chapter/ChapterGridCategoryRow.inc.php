<?php

/**
 * @file controllers/grid/users/chapter/ChapterGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterGridCategoryRow
 * @ingroup controllers_grid_users_chapter
 *
 * @brief Chapter grid category row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

// Link actions
import('lib.pkp.classes.linkAction.request.AjaxModal');

class ChapterGridCategoryRow extends GridCategoryRow {
	/** @var Monograph **/
	var $_monograph;

	/**
	 * Constructor
	 */
	function ChapterGridCategoryRow(&$monograph) {
		$this->_monograph =& $monograph;
		parent::GridCategoryRow();
	}

	//
	// Overridden methods from GridCategoryRow
	//
	/**
	 * @see GridCategoryRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Retrieve the monograph id from the request
		$monograph =& $this->getMonograph();

		// Is this a new row or an existing row?
		$chapterId = $this->getId();
		if (!empty($chapterId) && is_numeric($chapterId)) {
			$chapter =& $this->getData();

			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $monograph->getId(),
				'chapterId' => $chapterId
			);


			$this->addAction(new LinkAction(
				'editChapter',
				new AjaxModal(
					$router->url($request, null, null, 'editChapter', null, $actionArgs),
					$chapter->getLocalizedTitle()
				),
				$chapter->getLocalizedTitle()
			));
		}
	}

	/**
	 * Get the monograph for this row (already authorized)
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}
}

?>
