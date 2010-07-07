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
	function authorize($request) {
		import('classes.security.authorization.OmpPressSetupPolicy');
		$this->addPolicy(new OmpPressSetupPolicy($request));
		return parent::authorize($request);
	}
}
?>