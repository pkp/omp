<?php

/**
 * @file plugins/blocks/languageToggle/LanguageToggleBlockPlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LanguageToggleBlockPlugin
 * @ingroup plugins_blocks_languageToggle
 *
 * @brief Class for language selector block plugin
 */



import('lib.pkp.classes.plugins.BlockPlugin');

class LanguageToggleBlockPlugin extends BlockPlugin {

	/**
	 * Install default settings on system install.
	 * @return string
	 */
	function getInstallSitePluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Install default settings on press creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.block.languageToggle.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.block.languageToggle.description');
	}

	/**
	 * @copydoc BlockPlugin::getContents()
	 */
	function getContents($templateMgr, $request = null) {
		if (!defined('SESSION_DISABLE_INIT')) {
			$press = $request->getPress();
			if (isset($press)) {
				$locales = $press->getSupportedLocaleNames();

			} else {
				$site = $request->getSite();
				$locales = $site->getSupportedLocaleNames();
			}
		} else {
			$locales =& AppLocale::getAllLocales();
			if (isset($_SERVER['HTTP_REFERER'])) {
				$templateMgr->assign('languageToggleNoUser', true);
				$templateMgr->assign('referrerUrl', $_SERVER['HTTP_REFERER']);
			} else {
				unset($locales); // Disable; we're not sure what URL to use
			}
		}

		if (isset($locales) && count($locales) > 1) {
			$templateMgr->assign('enableLanguageToggle', true);
			$templateMgr->assign('languageToggleLocales', $locales);
		}

		return parent::getContents($templateMgr);
	}
}
