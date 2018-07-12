<?php

/**
 * @file controllers/grid/users/chapter/ChapterGridCategoryRow.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
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
import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');

class ChapterGridCategoryRow extends GridCategoryRow {
	/** @var Monograph **/
	var $_monograph;

	/** @var Chapter **/
	var $_chapter;

	/** @var $_readOnly **/
	var $_readOnly;

	/**
	 * Constructor
	 */
	function __construct($monograph, $readOnly = false) {
		$this->_monograph = $monograph;
		$this->_readOnly = $readOnly;
		parent::__construct();
	}

	//
	// Overridden methods from GridCategoryRow
	//
	/**
	 * @copydoc GridCategoryRow::initialize()
	 */
	function initialize($request, $template = null) {
		// Do the default initialization
		parent::initialize($request, $template);

		// Retrieve the monograph id from the request
		$monograph = $this->getMonograph();

		// Is this a new row or an existing row?
		$chapterId = $this->getId();
		if (!empty($chapterId) && is_numeric($chapterId)) {
			$chapter = $this->getData();
			$this->_chapter = $chapter;

			// Only add row actions if this is an existing row and the grid is not 'read only'
			if (!$this->isReadOnly()) {
				$router = $request->getRouter();
				$actionArgs = array(
					'submissionId' => $monograph->getId(),
					'chapterId' => $chapterId
				);

				$this->addAction(
					new LinkAction(
						'deleteChapter',
						new RemoteActionConfirmationModal(
							$request->getSession(),
							__('common.confirmDelete'),
							__('common.delete'),
							$router->url($request, null, null, 'deleteChapter', null, $actionArgs),
							'modal_delete'
						),
						__('common.delete'),
						'delete'
					)
				);
			}
		}
	}

	/**
	 * Get the monograph for this row (already authorized)
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the chapter for this row
	 * @return Chapter
	 */
	function getChapter() {
		return $this->_chapter;
	}

	/**
	 * Determine if this grid row should be read only.
	 * @return boolean
	 */
	function isReadOnly() {
		return $this->_readOnly;
	}
}
?>
