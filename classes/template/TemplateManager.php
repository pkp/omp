<?php

/**
 * @file classes/template/TemplateManager.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TemplateManager
 *
 * @ingroup template
 *
 * @brief Class for accessing the underlying template engine.
 * Currently integrated with Smarty (from http://smarty.php.net/).
 *
 */

namespace APP\template;

use APP\core\Application;
use APP\core\Request;
use APP\file\PublicFileManager;
use PKP\config\Config;
use PKP\context\Context;
use PKP\core\PKPSessionGuard;
use PKP\facades\Locale;
use PKP\i18n\LocaleMetadata;
use PKP\security\Role;
use PKP\site\Site;
use PKP\template\PKPTemplateManager;

class TemplateManager extends PKPTemplateManager
{
    /**
     * Initialize template engine and assign basic template variables.
     *
     * @param Request $request
     */
    public function initialize($request)
    {
        parent::initialize($request);
        if (!PKPSessionGuard::isSessionDisable()) {
            /**
             * Kludge to make sure no code that tries to connect to
             * the database is executed (e.g., when loading
             * installer pages).
             */

            $context = $request->getContext(); /** @var Context $context */
            $site = $request->getSite(); /** @var Site $site */

            $publicFileManager = new PublicFileManager();
            $siteFilesDir = $request->getBaseUrl() . '/' . $publicFileManager->getSiteFilesPath();
            $this->assign('sitePublicFilesDir', $siteFilesDir);
            $this->assign('publicFilesDir', $siteFilesDir); // May be overridden by press

            // Pass app-specific details to template
            $this->assign([
                'brandImage' => 'templates/images/omp_brand.png',
                'packageKey' => 'common.software',
            ]);

            if (isset($context)) {
                $this->assign([
                    'currentPress' => $context,
                    'siteTitle' => $context->getLocalizedName(),
                    'publicFilesDir' => $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getId()),
                    'primaryLocale' => $context->getPrimaryLocale(),
                    'supportedLocales' => $context->getSupportedLocaleNames(LocaleMetadata::LANGUAGE_LOCALE_ONLY),
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
                        ['priority' => self::STYLE_SEQUENCE_LAST]
                    );
                }

                $this->assign('pageFooter', $context->getLocalizedData('pageFooter'));
            } else {
                // Check if registration is open for any contexts
                $contextDao = Application::getContextDAO();
                $contexts = $contextDao->getAll(true)->toArray();
                $contextsForRegistration = [];
                foreach ($contexts as $context) {
                    if (!$context->getData('disableUserReg')) {
                        $contextsForRegistration[] = $context;
                    }
                }

                $this->assign([
                    'contexts' => $contextsForRegistration,
                    'disableUserReg' => empty($contextsForRegistration),
                    'siteTitle' => $site->getLocalizedTitle(),
                    'primaryLocale' => $site->getPrimaryLocale(),
                    'supportedLocales' => Locale::getFormattedDisplayNames(
                        $site->getSupportedLocales(),
                        Locale::getLocales(),
                        LocaleMetadata::LANGUAGE_LOCALE_ONLY
                    ),
                    'pageFooter' => $site->getLocalizedData('pageFooter'),
                ]);
            }
        }
    }

    /**
     * @copydoc PKPTemplateManager::setupBackendPage()
     */
    public function setupBackendPage()
    {
        parent::setupBackendPage();

        $request = Application::get()->getRequest();
        if (PKPSessionGuard::isSessionDisable() || !$request->getContext() || !$request->getUser()) {
            return;
        }

        $router = $request->getRouter();
        $handler = $router->getHandler();
        $userRoles = (array) $handler->getAuthorizedContextObject(Application::ASSOC_TYPE_USER_ROLES);

        $menu = (array) $this->getState('menu');

        // Add catalog after submissions items
        if (count(array_intersect([Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN], $userRoles))) {
            $catalogLink = [
                'name' => __('navigation.catalog'),
                'url' => $router->url($request, null, 'manageCatalog'),
                'isCurrent' => $request->getRequestedPage() === 'manageCatalog',
                'icon' => 'Catalog'
            ];

            $index = false;
            if (Config::getVar('features', 'enable_new_submission_listing')) {
                $reviewAssignmentsIndex = array_search('reviewAssignments', array_keys($menu));
                $mySubmissionsIndex = array_search('mySubmissions', array_keys($menu));
                if ($mySubmissionsIndex !== false) {
                    $index = $mySubmissionsIndex;
                } elseif ($reviewAssignmentsIndex !== false) {
                    $index = $reviewAssignmentsIndex;
                } else {
                    $index = array_search('dashboards', array_keys($menu));
                }
            } else {
                $index = array_search('submissions', array_keys($menu));
            }

            if ($index === false || count($menu) <= $index + 1) {
                $menu['catalog'] = $catalogLink;
            } else {
                $menu = array_slice($menu, 0, $index + 1, true)
                    + ['catalog' => $catalogLink]
                    + array_slice($menu, $index + 1, null, true);
            }
        }

        $this->setState(['menu' => $menu]);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\template\TemplateManager', '\TemplateManager');
}
