<?php

/**
 * @file pages/gateway/GatewayHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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
	 * Index handler.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, $request) {
		$request->redirect(null, 'index');
	}

	/**
	 * Handle requests for gateway plugins.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function plugin($args, $request) {
		$this->validate($request);
		$pluginName = array_shift($args);

		$plugins = PluginRegistry::loadCategory('gateways');
		if (isset($pluginName) && isset($plugins[$pluginName])) {
			$plugin = $plugins[$pluginName];
			if (!$plugin->fetch($args, $request)) {
				$request->redirect(null, 'index');
			}
		} else {
			$request->redirect(null, 'index');
		}
	}
}


