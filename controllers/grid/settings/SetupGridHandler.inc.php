<?php

/**
 * @file controllers/grid/settings/SetupGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SetupGridHandler
 * @ingroup controllers_grid
 *
 * @brief Base class for setup grid handlers
 */

import('lib.pkp.classes.controllers.grid.GridHandler');

class SetupGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function SetupGridHandler() {
		parent::GridHandler();
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpPressSetupPolicy');
		$this->addPolicy(new OmpPressSetupPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}
}