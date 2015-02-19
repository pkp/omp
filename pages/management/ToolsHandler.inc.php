<?php

/**
 * @file pages/management/ToolsHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ToolsHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for Tool pages.
 */

// Import the base ManagementHandler.
import('lib.pkp.pages.management.PKPToolsHandler');

class ToolsHandler extends PKPToolsHandler {
	/**
	 * Constructor.
	 */
	function ToolsHandler() {
		parent::PKPToolsHandler();
	}
}

?>
