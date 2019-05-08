<?php

/**
 * @file controllers/grid/catalogEntry/IdentificationCodeGridRow.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IdentificationCodeGridRow
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Identification Code grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class IdentificationCodeGridRow extends GridRow {
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
		$identificationCode = $this->_data;

		if ($identificationCode != null && is_numeric($identificationCode->getId())) {
			$router = $request->getRouter();
			$actionArgs = array(
				'submissionId' => $monograph->getId(),
				'identificationCodeId' => $identificationCode->getId()
			);

			// Add row-level actions
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editCode',
					new AjaxModal(
						$router->url($request, null, null, 'editCode', null, $actionArgs),
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
					'deleteCode',
					new RemoteActionConfirmationModal(
						$request->getSession(),
						__('common.confirmDelete'),
						__('common.delete'),
						$router->url($request, null, null, 'deleteCode', null, $actionArgs),
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
	function getMonograph() {
		return $this->_monograph;
	}
}

