<?php

/**
 * @file pages/management/ManagementHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManagementHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for management pages.
 */

// Import the base Handler.
import('classes.handler.Handler');

class ManagementHandler extends Handler {
	/**
	 * Constructor.
	 */
	function ManagementHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array('index')
		);
	}
}