<?php

/**
 * @file pages/gateway/GatewayHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GatewayHandler
 * @ingroup pages_gateway
 *
 * @brief Handle external gateway requests. 
 */

import('classes.handler.Handler');

class GatewayHandler extends Handler {
	/**
	 * Constructor
	 */
	function GatewayHandler() {
		parent::Handler();
	}

	function index($args, $request) {
		$request->redirect(null, 'index');
	}

	/**
	 * Handle requests for gateway plugins.
	 */
	function plugin($args, $request) {
		$this->validate();
		$pluginName = array_shift($args);

		$plugins =& PluginRegistry::loadCategory('gateways');
		if (isset($pluginName) && isset($plugins[$pluginName])) {
			$plugin =& $plugins[$pluginName];
			if (!$plugin->fetch($args, $request)) {
				$request->redirect(null, 'index');
			}
		} else {
			$request->redirect(null, 'index');
		}
	}
}

?>
