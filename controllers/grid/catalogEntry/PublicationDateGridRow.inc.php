<?php

/**
 * @file controllers/grid/catalogEntry/PublicationDateGridRow.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationDateGridRow
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Publication Date grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class PublicationDateGridRow extends GridRow {
	/** @var Monograph **/
	var $_monograph;

	/**
	 * Constructor
	 * @param $monograph Monograph
	 */
	function PublicationDateGridRow($monograph) {
		$this->_monograph = $monograph;
		parent::GridRow();
	}

	//
	// Overridden methods from GridRow
	//
	/**
	 * @copydoc GridRow::initialize()
	 */
	function initialize($request, $template = null) {
		// Do the default initialization
		parent::initialize($request, $template);

		$monograph = $this->getMonograph();

		// Is this a new row or an existing row?
		$publicationDate = $this->_data;

		if ($publicationDate != null && is_numeric($publicationDate->getId())) {
			$router = $request->getRouter();
			$actionArgs = array(
				'submissionId' => $monograph->getId(),
				'publicationDateId' => $publicationDate->getId()
			);

			// Add row-level actions
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editDate',
					new AjaxModal(
						$router->url($request, null, null, 'editDate', null, $actionArgs),
						__('grid.action.edit'),
						'modal_edit'
					),
					__('grid.action.edit'),
					'edit'
				)
			);

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			$this->addAction(
				new LinkAction(
					'deleteDate',
					new RemoteActionConfirmationModal(
						__('common.confirmDelete'),
						__('common.delete'),
						$router->url($request, null, null, 'deleteDate', null, $actionArgs),
						'modal_delete'
					),
					__('grid.action.delete'),
					'delete'
				)
			);
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
