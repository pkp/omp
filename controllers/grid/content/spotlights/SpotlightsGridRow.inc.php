<?php

/**
 * @file controllers/grid/content/spotlights/SpotlightsGridRow.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightsGridRow
 * @ingroup controllers_grid_content_spotlights
 *
 * @brief Spotlights grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class SpotlightsGridRow extends GridRow {
	/** @var Press **/
	var $_press;

	/**
	 * Constructor
	 * @param $press Press
	 */
	function __construct($press) {
		$this->setPress($press);
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

		$press = $this->getPress();

		// Is this a new row or an existing row?
		$spotlight = $this->_data;
		if ($spotlight != null && is_numeric($spotlight->getId())) {
			$router = $request->getRouter();
			$actionArgs = array(
				'pressId' => $press->getId(),
				'spotlightId' => $spotlight->getId()
			);

			// Add row-level actions
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editSpotlight',
					new AjaxModal(
						$router->url($request, null, null, 'editSpotlight', null, $actionArgs),
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
					'deleteSpotlight',
					new RemoteActionConfirmationModal(
						$request->getSession(),
						__('common.confirmDelete'),
						__('common.delete'),
						$router->url($request, null, null, 'deleteSpotlight', null, $actionArgs),
						'modal_delete'
					),
					__('grid.action.delete'),
					'delete'
				)
			);
		}
	}

	/**
	 * Get the press for this row (already authorized)
	 * @return Press
	 */
	function getPress() {
		return $this->_press;
	}

	/**
	 * Set the press for this row (already authorized)
	 * @return Press
	 */
	function setPress($press) {
		$this->_press = $press;
	}
}

