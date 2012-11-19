<?php

/**
 * @file pages/index/HeaderHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HeaderHandler
 * @ingroup pages_header
 *
 * @brief Handle site header requests.
 */


import('classes.handler.Handler');

class HeaderHandler extends Handler {
	/**
	 * Constructor
	 */
	function HeaderHandler() {
		parent::Handler();
	}


	//
	// Public handler operations
	//
	/**
	 * Display the header.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		$this->setupTemplate($request);
		$templateMgr =& TemplateManager::getManager($request);
		return $templateMgr->fetchJson('header/index.tpl');
	}
}

?>
