<?php

/**
 * @file classes/template/TemplateManager.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
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
		parent::PKPTemplateManager($request);

		// Retrieve the router
		$router =& $this->request->getRouter();
		assert(is_a($router, 'PKPRouter'));

		// Are we using implicit authentication?
		$this->assign('implicitAuth', Config::getVar('security', 'implicit_auth'));

		if (!defined('SESSION_DISABLE_INIT')) {
			/**
			 * Kludge to make sure no code that tries to connect to
			 * the database is executed (e.g., when loading
			 * installer pages).
			 */

			$press =& $router->getContext($this->request);
			$site =& $this->request->getSite();

			$publicFileManager = new PublicFileManager();
			$siteFilesDir = $this->request->getBaseUrl() . '/' . $publicFileManager->getSiteFilesPath();
			$this->assign('sitePublicFilesDir', $siteFilesDir);
			$this->assign('publicFilesDir', $siteFilesDir); // May be overridden by press

			$siteStyleFilename = $publicFileManager->getSiteFilesPath() . '/' . $site->getSiteStyleFilename();
			if (file_exists($siteStyleFilename)) $this->addStyleSheet($this->request->getBaseUrl() . '/' . $siteStyleFilename);

			$this->assign('homeContext', array());
			if (isset($press)) {
				$this->assign_by_ref('currentPress', $press);

				// Assign press settings.
				$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
				$this->assign_by_ref('pressSettings', $pressSettingsDao->getPressSettings($press->getId()));

				$pressTitle = $press->getLocalizedName();
				$this->assign('siteTitle', $pressTitle);
				$this->assign('publicFilesDir', $this->request->getBaseUrl() . '/' . $publicFileManager->getPressFilesPath($press->getId()));

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
					$this->addStyleSheet($this->request->getBaseUrl() . '/' . $publicFileManager->getPressFilesPath($press->getId()) . '/' . $pressStyleSheet['uploadName']);
				}

				// Include footer links if they have been defined.
				$footerCategoryDao =& DAORegistry::getDAO('FooterCategoryDAO');
				$footerCategories =& $footerCategoryDao->getNotEmptyByPressId($press->getId());
				$this->assign_by_ref('footerCategories', $footerCategories->toArray());

				$footerLinkDao =& DAORegistry::getDAO('FooterLinkDAO');
				$this->assign('maxLinks', $footerLinkDao->getLargestCategoryTotalByPressId($press->getId()));
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

			$user =& $this->request->getUser();
			if (is_a($user, 'User')) {
				$presses =& $pressDao->getPresses();
			} else {
				$presses =& $pressDao->getEnabledPresses();
			}

			$multiplePresses = false;
			if ($presses->getCount() > 1) {
				$this->assign('multiplePresses', true);
				$multiplePresses = true;
			} else {
				if ($presses->getCount() == 0) { // no presses configured
					$this->assign('noPressesConfigured', true);
				}
			}

			if ($multiplePresses) {
				$this->_assignPressSwitcherData($presses, $press);
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
			$value .= '<a href="' . $this->request->url(null, null, null, $this->request->getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&lt;&lt;</a>&nbsp;';
			$params[$paramName] = $page - 1;
			$value .= '<a href="' . $this->request->url(null, null, null, $this->request->getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&lt;</a>&nbsp;';
		}

		for ($i=$pageBase; $i<min($pageBase+$numPageLinks, $pageCount+1); $i++) {
			if ($i == $page) {
				$value .= "<strong>$i</strong>&nbsp;";
			} else {
				$params[$paramName] = $i;
				$value .= '<a href="' . $this->request->url(null, null, null, $this->request->getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>' . $i . '</a>&nbsp;';
			}
		}
		if ($page < $pageCount) {
			$params[$paramName] = $page + 1;
			$value .= '<a href="' . $this->request->url(null, null, null, $this->request->getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&gt;</a>&nbsp;';
			$params[$paramName] = $pageCount;
			$value .= '<a href="' . $this->request->url(null, null, null, $this->request->getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&gt;&gt;</a>&nbsp;';
		}

		return $value;
	}


	//
	// Private helper methods.
	//
	/**
	 * Get the press switcher data and assign it to
	 * the template manager.
	 * @param $presses Array
	 * @param $currentPress Press
	 */
	function _assignPressSwitcherData(&$presses, $currentPress = null) {
		$workingPresses =& $presses->toArray();

		$dispatcher = $this->request->getDispatcher();
		$pressesNameAndUrl = array();
		foreach ($workingPresses as $workingPress) {
			$pressUrl = $dispatcher->url($this->request, ROUTE_PAGE, $workingPress->getPath());
			$pressesNameAndUrl[$pressUrl] = $workingPress->getLocalizedName();
		};

		// Get the current press switcher value. We don´t need to worry about the
		// value when there is no current press, because then the switcher will not
		// be visible.
		$currentPressUrl = null;
		if ($currentPress) {
			$currentPressUrl = $dispatcher->url($this->request, ROUTE_PAGE, $currentPress->getPath());
		} else {
			$pressesNameAndUrl = array(__('press.select')) + $pressesNameAndUrl;
		}

		$this->assign('currentPressUrl', $currentPressUrl);
		$this->assign('pressesNameAndUrl', $pressesNameAndUrl);
	}
}

?>
