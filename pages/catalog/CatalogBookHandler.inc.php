<?php

/**
 * @file pages/catalog/CatalogBookHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogBookHandler
 * @ingroup pages_catalog
 *
 * @brief Handle requests for the book-specific part of the public-facing
 *   catalog.
 */

import('classes.handler.Handler');

// import UI base classes
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.core.JSONMessage');

class CatalogBookHandler extends Handler {
	/**
	 * Constructor
	 */
	function CatalogBookHandler() {
		parent::Handler();
	}


	//
	// Overridden functions from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Display a published monograph in the public catalog.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function book($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();
		$this->setupTemplate();
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_SUBMISSION); // submission.synopsis

		$publishedMonograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH);
		$templateMgr->assign('publishedMonograph', $publishedMonograph);

		// Get book categories
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$categories =& $publishedMonographDao->getCategories($publishedMonograph->getId(), $press->getId());
		$templateMgr->assign('categories', $categories);

		// Display
		$templateMgr->display('catalog/book/book.tpl');
	}
}

?>
