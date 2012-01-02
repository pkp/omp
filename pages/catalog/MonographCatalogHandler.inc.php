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
		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			array('book')
		);
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPublishedMonographAccessPolicy');
		$this->addPolicy(new OmpPublishedMonographAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Show a monograph preview.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function book($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$publishedMonograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH);
		$templateMgr->assign('publishedMonograph', $publishedMonograph);

		$templateMgr->fetch('catalog/book.tpl');
	}
}

?>
