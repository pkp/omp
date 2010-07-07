<?php

/**
 * @file controllers/listbuilder/settings/SetupListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SetupListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Base class for setup listbuilders
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

// import validation classes
import('classes.handler.validation.HandlerValidatorPress');
import('lib.pkp.classes.handler.validation.HandlerValidatorRoles');

class SetupListbuilderHandler extends ListbuilderHandler {
	/**
	 * Constructor
	 */
	function SetupListbuilderHandler() {
		parent::ListbuilderHandler();
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize($requiredContexts, $request) {
		// Retrieve the request context
		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		// 1) Ensure we're in a press
		$this->addCheck(new HandlerValidatorPress($this, false, 'No press in context!'));

		// 2) Only Press Managers and Admins may access
		$this->addCheck(new HandlerValidatorRoles($this, false, 'Insufficient privileges!', null, array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SITE_ADMIN)));

		// Execute standard checks
		if (!parent::authorize($requiredContexts, $request)) return false;

		return true;
	}
}
?>