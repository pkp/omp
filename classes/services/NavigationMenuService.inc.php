<?php

/**
 * @file classes/services/NavigationMenuService.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NavigationMenuService
 * @ingroup services
 *
 * @brief Helper class that encapsulates NavigationMenu business logic
 */

namespace OMP\Services;

class NavigationMenuService extends \PKP\Services\PKPNavigationMenuService {

	/**
	 * Initialize hooks for extending PKPSubmissionService
	 */
    public function __construct() {

		\HookRegistry::register('NavigationMenus::itemTypes', array($this, 'getMenuItemTypesCallback'));
		\HookRegistry::register('NavigationMenus::displaySettings', array($this, 'getDisplayStatusCallback'));
	}

	/**
	 * Return all default navigationMenuItemTypes.
	 * @param $types array of types
	 */
	public function getMenuItemTypesCallback($hookName, $args) {
		$types =& $args[0];

		\AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_PKP_USER);

		$ompTypes = array(
			NMI_TYPE_CATALOG => array(
				'title' => __('navigation.catalog'),
				'description' => __('manager.navigationMenus.catalog.description'),
				'conditionalWarning' => __('manager.navigationMenus.catalog.conditionalWarning'),
			),
		);

		$types = array_merge($types, $ompTypes);
	}

	/**
	 * Callback for display menu item functionallity
	 */
	function getDisplayStatusCallback($hookName, $args) {
		$navigationMenuItem =& $args[0];

		$request = \Application::getRequest();
		$dispatcher = $request->getDispatcher();

		$isUserLoggedIn = \Validation::isLoggedIn();
		$isUserLoggedInAs = \Validation::isLoggedInAs();
		$context = $request->getContext();
		$currentUser = $request->getUser();

		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;

		// Transform an item title if the title includes a {$variable}
		$templateMgr = \TemplateManager::getManager(\Application::getRequest());
		$title = $navigationMenuItem->getLocalizedTitle();
		$prefix = '{$';
		$postfix = '}';

		$titleRepl = $title;

		$prefixPos = strpos($title, $prefix);
		$postfixPos = strpos($title, $postfix);

		if ($prefixPos !== false && $postfixPos !== false && ($postfixPos - $prefixPos) > 0){
			$titleRepl = substr($title, $prefixPos + strlen($prefix), $postfixPos - $prefixPos - strlen($prefix));

			$templateReplaceTitle = $templateMgr->get_template_vars($titleRepl);
				if ($templateReplaceTitle) {
					$navigationMenuItem->setTitle($templateReplaceTitle, \AppLocale::getLocale());
			}
		}

		$menuItemType = $navigationMenuItem->getType();

		// Conditionally hide some items
		switch ($menuItemType) {
			case NMI_TYPE_CATALOG:
				$navigationMenuItem->setIsDisplayed(true);
				break;
		}

		if ($navigationMenuItem->getIsDisplayed()) {

			// Set the URL
			switch ($menuItemType) {
				case NMI_TYPE_CATALOG:
					$navigationMenuItem->setUrl($dispatcher->url(
						$request,
						ROUTE_PAGE,
						null,
						'catalog',
						null,
						null
					));
					break;
			}
		}
	}
}
