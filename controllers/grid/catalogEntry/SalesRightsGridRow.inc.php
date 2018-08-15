<?php

/**
 * @file controllers/grid/catalogEntry/SalesRightsGridRow.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SalesRightsGridRow
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Sales Rights grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class SalesRightsGridRow extends GridRow {
	/** @var Monograph **/
	var $_monograph;

	/**
	 * Constructor
	 */
	function __construct($monograph) {
		$this->_monograph = $monograph;
		parent::__construct();
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
		$salesRights = $this->_data;

		if ($salesRights != null && is_numeric($salesRights->getId())) {
			$router = $request->getRouter();
			$actionArgs = array(
				'submissionId' => $monograph->getId(),
				'salesRightsId' => $salesRights->getId()
			);

			// Add row-level actions
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editRights',
					new AjaxModal(
						$router->url($request, null, null, 'editRights', null, $actionArgs),
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
					'deleteRights',
					new RemoteActionConfirmationModal(
						$request->getSession(),
						__('common.confirmDelete'),
						__('common.delete'),
						$router->url($request, null, null, 'deleteRights', null, $actionArgs),
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

