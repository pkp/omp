<?php

/**
 * @file classes/template/TemplateManager.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
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
	 * Initialize template engine and assign basic template variables.
	 * @param $request PKPRequest
	 */
	public function initialize($request) {
		parent::initialize($request);
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
				$this->assign([
					'currentPress' => $context,
					'siteTitle' => $context->getLocalizedName(),
					'publicFilesDir' => $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getId()),
					'primaryLocale' => $context->getPrimaryLocale(),
					'supportedLocales' => $context->getSupportedLocaleNames(),
					'displayPageHeaderTitle' => $context->getPageHeaderTitle(),
					'displayPageHeaderLogo' => $context->getPageHeaderLogo(),
					'numPageLinks' => $context->getData('numPageLinks'),
					'itemsPerPage' => $context->getData('itemsPerPage'),
					'enableAnnouncements' => $context->getData('enableAnnouncements'),
					'disableUserReg' => $context->getData('disableUserReg'),
				]);

				// Assign stylesheets and footer
				$contextStyleSheet = $context->getData('styleSheet');
				if ($contextStyleSheet) {
					$this->addStyleSheet(
						'contextStylesheet',
						$request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getId()) . '/' . $contextStyleSheet['uploadName'],
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

				$this->assign('pageFooter', $context->getLocalizedData('pageFooter'));
			}
		}
	}
}
