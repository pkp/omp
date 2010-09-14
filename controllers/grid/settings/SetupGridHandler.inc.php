<?php

/**
 * @file controllers/grid/settings/SetupGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SetupGridHandler
 * @ingroup controllers_grid_settings
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
	 * @see GridHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SETTINGS));;
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}
}