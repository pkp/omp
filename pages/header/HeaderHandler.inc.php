<?php

/**
 * @file pages/header/HeaderHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HeaderHandler
 * @ingroup pages_header
 *
 * @brief Handle site header requests.
 */

import('lib.pkp.pages.header.PKPHeaderHandler');

class HeaderHandler extends PKPHeaderHandler {
	/**
	 * Constructor
	 */
	function HeaderHandler() {
		parent::PKPHeaderHandler();
	}


	//
	// Private methods
	//
	/**
	 * Get the iterator of working contexts.
	 * @param $request PKPRequest
	 * @return ItemIterator
	 */
	function _getWorkingContexts($request) {
		// Check for multiple presses.
		$pressDao = DAORegistry::getDAO('PressDAO');

		$user = $request->getUser();
		if (is_a($user, 'User')) {
			return $pressDao->getAll();
		} else {
			return $pressDao->getEnabledPresses();
		}
	}
}

?>
