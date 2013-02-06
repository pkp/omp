<?php

/**
 * @file controllers/grid/admin/press/PressGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressGridRow
 * @ingroup controllers_grid_admin_press
 *
 * @brief Press grid row definition
 */

import('lib.pkp.controllers.grid.admin.context.ContextGridRow');

class PressGridRow extends ContextGridRow {
	/**
	 * Constructor
	 */
	function PressGridRow() {
		parent::ContextGridRow();
	}

	/**
	 * Get the delete context row locale key.
	 * @return string
	 */
	function initialize(&$request) {
		parent::initialize($request);
		$element = $this->getData();
		if (Validation::isPressManager($element->getId())) {

			import('lib.pkp.classes.linkAction.request.RedirectAction');
			$dispatcher = $request->getRouter()->getDispatcher();
			$this->addAction(
				new LinkAction(
					'wizard',
					new RedirectAction(
						$dispatcher->url($request, ROUTE_PAGE, $element->getPath(), 'admin', 'contexts', null, array('openWizard' => 1))),
					__('grid.action.wizard'),
					'wrench'
				)
			);
		}
	}


	//
	// Overridden methods from ContextGridRow
	//
	/**
	 * Get the delete context row locale key.
	 * @return string
	 */
	function getConfirmDeleteKey() {
		return 'admin.presses.confirmDelete';
	}
}

?>
