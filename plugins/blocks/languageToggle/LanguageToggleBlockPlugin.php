<?php

/**
 * @file plugins/blocks/languageToggle/LanguageToggleBlockPlugin.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class LanguageToggleBlockPlugin
 * @brief Class for language selector block plugin
 */

namespace APP\plugins\blocks\languageToggle;

use APP\core\Application;
use PKP\facades\Locale;
use PKP\i18n\LocaleMetadata;
use PKP\plugins\BlockPlugin;
use PKP\session\SessionManager;

class LanguageToggleBlockPlugin extends BlockPlugin
{
    /**
     * Install default settings on system install.
     *
     * @return string
     */
    public function getInstallSitePluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Install default settings on press creation.
     *
     * @return string
     */
    public function getContextSpecificPluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.block.languageToggle.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription()
    {
        return __('plugins.block.languageToggle.description');
    }

    /**
     * @copydoc BlockPlugin::getContents()
     *
     * @param null|mixed $request
     */
    public function getContents($templateMgr, $request = null)
    {
        $locales = null;

        if (!SessionManager::isDisabled()) {
            $request ??= Application::get()->getRequest();
            $context = $request->getContext();
            $locales = Locale::getFormattedDisplayNames(
                isset($context)
                    ? $context->getSupportedLocales()
                    : $request->getSite()->getSupportedLocales(),
                Locale::getLocales(),
                LocaleMetadata::LANGUAGE_LOCALE_ONLY
            );
        } else {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $locales = Locale::getFormattedDisplayNames(null, null, LocaleMetadata::LANGUAGE_LOCALE_ONLY);
                $templateMgr->assign('languageToggleNoUser', true);
                $templateMgr->assign('referrerUrl', $_SERVER['HTTP_REFERER']);
            }
        }

        if (isset($locales) && count($locales) > 1) {
            $templateMgr->assign('enableLanguageToggle', true);
            $templateMgr->assign('languageToggleLocales', $locales);
        }

        return parent::getContents($templateMgr);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\blocks\languageToggle\LanguageToggleBlockPlugin', '\LanguageToggleBlockPlugin');
}
