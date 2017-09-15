<?php

/**
 * @file classes/template/TemplateManager.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
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
	 * @param $request PKPRequest
	 */
	function __construct($request) {
		parent::__construct($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON);

		if (!defined('SESSION_DISABLE_INIT')) {
			/**
			 * Kludge to make sure no code that tries to connect to
			 * the database is executed (e.g., when loading
			 * installer pages).
			 */

			$context = $request->getContext();
			$site = $request->getSite();

			$publicFileManager = new PublicFileManager();
			$siteFilesDir = $request->getBaseUrl() . '/' . $publicFileManager->getSiteFilesPath();
			$this->assign('sitePublicFilesDir', $siteFilesDir);
			$this->assign('publicFilesDir', $siteFilesDir); // May be overridden by press

			$siteStyleFilename = $publicFileManager->getSiteFilesPath() . '/' . $site->getSiteStyleFilename();
			if (file_exists($siteStyleFilename)) {
				$this->addStyleSheet(
					'siteStylesheet',
					$request->getBaseUrl() . '/' . $siteStyleFilename,
					array(
						'priority' => STYLE_SEQUENCE_LAST
					)
				);
			}

			// Pass app-specific details to template
			$this->assign(array(
				'brandImage' => 'templates/images/omp_brand.png',
				'packageKey' => 'common.openMonographPress',
			));

			// Get a count of unread tasks.
			if ($user = $request->getUser()) {
				$notificationDao = DAORegistry::getDAO('NotificationDAO');
				// Exclude certain tasks, defined in the notifications grid handler
				import('lib.pkp.controllers.grid.notifications.TaskNotificationsGridHandler');
				$this->assign('unreadNotificationCount', $notificationDao->getNotificationCount(false, $user->getId(), null, NOTIFICATION_LEVEL_TASK));
			}

			if (isset($context)) {
				$this->assign('currentPress', $context);

				$this->assign('siteTitle', $context->getLocalizedName());
				$this->assign('publicFilesDir', $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getAssocType(), $context->getId()));

				$this->assign('primaryLocale', $context->getPrimaryLocale());
				$this->assign('supportedLocales', $context->getSupportedLocaleNames());

				// Assign page header
				$this->assign('displayPageHeaderTitle', $context->getPageHeaderTitle());
				$this->assign('displayPageHeaderLogo', $context->getPageHeaderLogo());
				$this->assign('numPageLinks', $context->getSetting('numPageLinks'));
				$this->assign('itemsPerPage', $context->getSetting('itemsPerPage'));
				$this->assign('enableAnnouncements', $context->getSetting('enableAnnouncements'));
				$this->assign('disableUserReg', $context->getSetting('disableUserReg'));

				// Assign stylesheets and footer
				$contextStyleSheet = $context->getSetting('styleSheet');
				if ($contextStyleSheet) {
					$this->addStyleSheet(
						'contextStylesheet',
						$request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath(ASSOC_TYPE_PRESS, $context->getId()) . '/' . $contextStyleSheet['uploadName'],
						array(
							'priority' => STYLE_SEQUENCE_LAST
						)
					);
				}

				// Get a link to the settings page for the current context.
				// This allows us to reduce template duplication by using this
				// variable in templates/common/header.tpl, instead of
				// reproducing a lot of OMP/OJS-specific logic there.
				$dispatcher = $request->getDispatcher();
				$this->assign( 'contextSettingsUrl', $dispatcher->url($request, ROUTE_PAGE, null, 'management', 'settings', 'context') );

				$this->assign('pageFooter', $context->getLocalizedSetting('pageFooter'));
			} else {
				// Add the site-wide logo, if set for this locale or the primary locale
				$this->assign('displayPageHeaderTitle', $site->getLocalizedPageHeaderTitle());
				$this->assign('displayPageHeaderLogo', $site->getLocalizedSetting('pageHeaderTitleImage'));
				$this->assign('siteTitle', $site->getLocalizedTitle());
				$this->assign('primaryLocale', $site->getPrimaryLocale());
				$this->assign('supportedLocales', $site->getSupportedLocaleNames());

				// Check if registration is open for any contexts
				$contextDao = Application::getContextDAO();
				$contexts = $contextDao->getAll(true)->toArray();
				$contextsForRegistration = array();
				foreach($contexts as $context) {
					if (!$context->getSetting('disableUserReg')) {
						$contextsForRegistration[] = $context;
					}
				}
				$this->assign('contexts', $contextsForRegistration);
				$this->assign('disableUserReg', empty($contextsForRegistration));
			}
		}
	}
}

?>
