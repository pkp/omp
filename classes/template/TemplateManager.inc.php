<?php

/**
 * @file classes/template/TemplateManager.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
		$router = $this->request->getRouter();
		assert(is_a($router, 'PKPRouter'));

		// Are we using implicit authentication?
		$this->assign('implicitAuth', Config::getVar('security', 'implicit_auth'));

		if (!defined('SESSION_DISABLE_INIT')) {
			/**
			 * Kludge to make sure no code that tries to connect to
			 * the database is executed (e.g., when loading
			 * installer pages).
			 */

			$context = $router->getContext($this->request);
			$site = $this->request->getSite();

			$publicFileManager = new PublicFileManager();
			$siteFilesDir = $this->request->getBaseUrl() . '/' . $publicFileManager->getSiteFilesPath();
			$this->assign('sitePublicFilesDir', $siteFilesDir);
			$this->assign('publicFilesDir', $siteFilesDir); // May be overridden by press

			$siteStyleFilename = $publicFileManager->getSiteFilesPath() . '/' . $site->getSiteStyleFilename();
			if (file_exists($siteStyleFilename)) $this->addStyleSheet($this->request->getBaseUrl() . '/' . $siteStyleFilename, STYLE_SEQUENCE_LAST);

			if (isset($context)) {
				$this->assign('currentPress', $context);

				$this->assign('siteTitle', $context->getLocalizedName());
				$this->assign('publicFilesDir', $this->request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getAssocType(), $context->getId()));

				$this->assign('primaryLocale', $context->getPrimaryLocale());
				$this->assign('alternateLocales', $context->getSetting('alternateLocales'));

				// Assign page header
				$this->assign('displayPageHeaderTitle', $context->getPageHeaderTitle());
				$this->assign('displayPageHeaderLogo', $context->getPageHeaderLogo());
				$this->assign('alternatePageHeader', $context->getLocalizedSetting('pageHeader'));
				$this->assign('metaSearchDescription', $context->getLocalizedSetting('searchDescription'));
				$this->assign('metaSearchKeywords', $context->getLocalizedSetting('searchKeywords'));
				$this->assign('metaCustomHeaders', $context->getLocalizedSetting('customHeaders'));
				$this->assign('numPageLinks', $context->getSetting('numPageLinks'));
				$this->assign('itemsPerPage', $context->getSetting('itemsPerPage'));
				$this->assign('enableAnnouncements', $context->getSetting('enableAnnouncements'));

				// Assign stylesheets and footer
				$contextStyleSheet = $context->getSetting('styleSheet');
				if ($contextStyleSheet) {
					$this->addStyleSheet($this->request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath(ASSOC_TYPE_PRESS, $context->getId()) . '/' . $contextStyleSheet['uploadName'], STYLE_SEQUENCE_LAST);
				}

				// Include footer links if they have been defined.
				$footerCategoryDao = DAORegistry::getDAO('FooterCategoryDAO');
				$footerCategories = $footerCategoryDao->getNotEmptyByContextId($context->getId());
				$this->assign('footerCategories', $footerCategories->toArray());

				$footerLinkDao = DAORegistry::getDAO('FooterLinkDAO');
				$this->assign('maxLinks', $footerLinkDao->getLargestCategoryTotalbyContextId($context->getId()));
				$this->assign('pageFooter', $context->getLocalizedSetting('pageFooter'));
			} else {
				// Add the site-wide logo, if set for this locale or the primary locale
				$displayPageHeaderTitle = $site->getLocalizedPageHeaderTitle();
				$this->assign('displayPageHeaderTitle', $displayPageHeaderTitle);
				if (isset($displayPageHeaderTitle['altText'])) $this->assign('displayPageHeaderTitleAltText', $displayPageHeaderTitle['altText']);

				$this->assign('siteTitle', $site->getLocalizedTitle());
			}
		}
	}

	/**
	 * Initialize the template manager.
	 */
	function initialize() {
		// Add uncompilable styles
		$this->addStyleSheet($this->request->getBaseUrl() . '/styles/lib.css', STYLE_SEQUENCE_CORE);

		parent::initialize();
	}
}

?>
