<?php

/**
 * @file classes/template/TemplateManager.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TemplateManager
 * @ingroup template
 *
 * @brief Class for accessing the underlying template engine.
 * Currently integrated with Smarty (from http://smarty.php.net/).
 *
 */




import('classes.file.PublicFileManager');
import('lib.pkp.classes.template.PKPTemplateManager');

class TemplateManager extends PKPTemplateManager {
	/**
	 * Constructor.
	 * Initialize template engine and assign basic template variables.
	 * @param $request PKPRequest FIXME: is optional for backwards compatibility only - make mandatory
	 */
	function TemplateManager($request = null) {
		// FIXME: for backwards compatibility only - remove
		if (!isset($request)) {
			// FIXME: Trigger a deprecation warning when enough instances of this
			// call have been fixed to not clutter the error log.
			$request =& Registry::get('request');
		}
		assert(is_a($request, 'PKPRequest'));

		parent::PKPTemplateManager($request);

		// Retrieve the router
		$router =& $request->getRouter();
		assert(is_a($router, 'PKPRouter'));

		// Are we using implicit authentication?
		$this->assign('implicitAuth', Config::getVar('security', 'implicit_auth'));

		if (!defined('SESSION_DISABLE_INIT')) {
			/**
			 * Kludge to make sure no code that tries to connect to
			 * the database is executed (e.g., when loading
			 * installer pages).
			 */

			$press =& $router->getContext($request);
			$site =& $request->getSite();

			$siteFilesDir = $request->getBaseUrl() . '/' . PublicFileManager::getSiteFilesPath();
			$this->assign('sitePublicFilesDir', $siteFilesDir);
			$this->assign('publicFilesDir', $siteFilesDir); // May be overridden by press

			$siteStyleFilename = PublicFileManager::getSiteFilesPath() . '/' . $site->getSiteStyleFilename();
			if (file_exists($siteStyleFilename)) $this->addStyleSheet($request->getBaseUrl() . '/' . $siteStyleFilename);

			$this->assign('homeContext', array());
			if (isset($press)) {
				$this->assign_by_ref('currentPress', $press);
				$pressTitle = $press->getLocalizedName();
				$this->assign('siteTitle', $pressTitle);
				$this->assign('publicFilesDir', $request->getBaseUrl() . '/' . PublicFileManager::getPressFilesPath($press->getId()));

				$this->assign('primaryLocale', $press->getPrimaryLocale());
				$this->assign('alternateLocales', $press->getSetting('alternateLocales'));

				// Assign additional navigation bar items
				$navMenuItems =& $press->getLocalizedSetting('navItems');
				$this->assign_by_ref('navMenuItems', $navMenuItems);

				// Assign press page header
				$this->assign('displayPageHeaderTitle', $press->getPressPageHeaderTitle());
				$this->assign('displayPageHeaderLogo', $press->getPressPageHeaderLogo());
				$this->assign('alternatePageHeader', $press->getLocalizedSetting('pressPageHeader'));
				$this->assign('metaSearchDescription', $press->getLocalizedSetting('searchDescription'));
				$this->assign('metaSearchKeywords', $press->getLocalizedSetting('searchKeywords'));
				$this->assign('metaCustomHeaders', $press->getLocalizedSetting('customHeaders'));
				$this->assign('numPageLinks', $press->getSetting('numPageLinks'));
				$this->assign('itemsPerPage', $press->getSetting('itemsPerPage'));
				$this->assign('enableAnnouncements', $press->getSetting('enableAnnouncements'));

				// Assign stylesheets and footer
				$pressStyleSheet = $press->getSetting('pressStyleSheet');
				if ($pressStyleSheet) {
					$this->addStyleSheet($request->getBaseUrl() . '/' . PublicFileManager::getPressFilesPath($press->getId()) . '/' . $pressStyleSheet['uploadName']);
				}

				$this->assign('pageFooter', $press->getLocalizedSetting('pressPageFooter'));
			} else {
				// Add the site-wide logo, if set for this locale or the primary locale
				$displayPageHeaderTitle = $site->getLocalizedPageHeaderTitle();
				$this->assign('displayPageHeaderTitle', $displayPageHeaderTitle);
				if (isset($displayPageHeaderTitle['altText'])) $this->assign('displayPageHeaderTitleAltText', $displayPageHeaderTitle['altText']);

				$this->assign('siteTitle', $site->getLocalizedTitle());
			}

			// Check for multiple presses.
			$pressDao =& DAORegistry::getDAO('PressDAO');
			$presses =& $pressDao->getPresses();
			$hasOtherPresses = false;
			if ($presses->getCount() > 1) {
				$this->assign('hasOtherPresses', true);
				$hasOtherPresses = true;
			}

			$user =& $request->getUser();
			if (is_a($user, 'User')) {
				// Decide to assign or not the press switcher data
				// to the template manager.
				if ($hasOtherPresses) {
					$this->_assignPressSwitcherData($request, $presses, $press);
				}
				// Check for administrator and manager roles.
				$this->assign('isAdmin', Validation::isSiteAdmin());
				$this->assign('isPressManager', Validation::isPressManager());
			}
		}
	}

	/**
	 * Display page links for a listing of items that has been
	 * divided onto multiple pages.
	 * Usage:
	 * {page_links
	 * 	name="nameMustMatchGetRangeInfoCall"
	 * 	iterator=$myIterator
	 *	additional_param=myAdditionalParameterValue
	 * }
	 */
	function smartyPageLinks($params, &$smarty) {
		$iterator = $params['iterator'];
		$name = $params['name'];
		if (isset($params['params']) && is_array($params['params'])) {
			$extraParams = $params['params'];
			unset($params['params']);
			$params = array_merge($params, $extraParams);
		}
		if (isset($params['anchor'])) {
			$anchor = $params['anchor'];
			unset($params['anchor']);
		} else {
			$anchor = null;
		}
		if (isset($params['all_extra'])) {
			$allExtra = ' ' . $params['all_extra'];
			unset($params['all_extra']);
		} else {
			$allExtra = '';
		}

		unset($params['iterator']);
		unset($params['name']);

		$numPageLinks = $smarty->get_template_vars('numPageLinks');
		if (!is_numeric($numPageLinks)) $numPageLinks=10;

		$page = $iterator->getPage();
		$pageCount = $iterator->getPageCount();
		$itemTotal = $iterator->getCount();

		$pageBase = max($page - floor($numPageLinks / 2), 1);
		$paramName = $name . 'Page';

		if ($pageCount<=1) return '';

		$value = '';

		if ($page>1) {
			$params[$paramName] = 1;
			$value .= '<a href="' . Request::url(null, null, null, Request::getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&lt;&lt;</a>&nbsp;';
			$params[$paramName] = $page - 1;
			$value .= '<a href="' . Request::url(null, null, null, Request::getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&lt;</a>&nbsp;';
		}

		for ($i=$pageBase; $i<min($pageBase+$numPageLinks, $pageCount+1); $i++) {
			if ($i == $page) {
				$value .= "<strong>$i</strong>&nbsp;";
			} else {
				$params[$paramName] = $i;
				$value .= '<a href="' . Request::url(null, null, null, Request::getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>' . $i . '</a>&nbsp;';
			}
		}
		if ($page < $pageCount) {
			$params[$paramName] = $page + 1;
			$value .= '<a href="' . Request::url(null, null, null, Request::getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&gt;</a>&nbsp;';
			$params[$paramName] = $pageCount;
			$value .= '<a href="' . Request::url(null, null, null, Request::getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&gt;&gt;</a>&nbsp;';
		}

		return $value;
	}


	//
	// Private helper methods.
	//
	/**
	 * Get the press switcher data and assign it to
	 * the template manager.
	 * @param $request Request
	 * @param $presses Array
	 * @param $currentPress Press
	 */
	function _assignPressSwitcherData($request, &$presses, $currentPress = null) {
		$workingPresses =& $presses->toArray();

		$dispatcher = $request->getDispatcher();
		$pressesNameAndUrl = array();
		foreach ($workingPresses as $workingPress) {
			$pressUrl = $dispatcher->url($request, ROUTER_PAGE, $workingPress->getPath());
			$pressesNameAndUrl[$pressUrl] = $workingPress->getLocalizedName();
		};

		// Get the current press switcher value. We don´t need to worry about the
		// value when there is no current press, because then the switcher will not
		// be visible.
		$currentPressUrl = null;
		if ($currentPress) {
			$currentPressUrl = $dispatcher->url($request, ROUTER_PAGE, $currentPress->getPath());
		}

		$this->assign('currentPressUrl', $currentPressUrl);
		$this->assign('pressesNameAndUrl', $pressesNameAndUrl);
	}
}

?>
