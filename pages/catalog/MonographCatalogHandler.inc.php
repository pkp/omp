<?php

/**
 * @file pages/catalog/MonographCatalogHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographCatalogHandler
 * @ingroup pages_catalog
 *
 * @brief Handle requests for the monograph-specific parts of the public-
 *   facing catalog.
 */

import('classes.handler.Handler');

// import UI base classes
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.core.JSONMessage');

class MonographCatalogHandler extends Handler {
	/**
	 * Constructor
	 */
	function MonographCatalogHandler() {
		parent::Handler();
	}


	//
	// Public handler methods
	//
	/**
	 * Show a monograph preview.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function preview($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$json = new JSONMessage(true, $templateMgr->fetch('catalog/preview.tpl'));
		return $json->getString();
	}
}

?>
