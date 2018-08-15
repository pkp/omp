<?php

/**
 * @file classes/i18n/AppLocale.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Locale
 * @ingroup i18n
 *
 * @brief Provides methods for loading locale data and translating strings identified by unique keys
 *
 */

import('lib.pkp.classes.i18n.PKPLocale');

class AppLocale extends PKPLocale {
	/**
	 * Get all supported UI locales for the current context.
	 * @return array
	 */
	static function getSupportedLocales() {
		static $supportedLocales;
		if (!isset($supportedLocales)) {
			if (defined('SESSION_DISABLE_INIT')) {
				$supportedLocales = AppLocale::getAllLocales();
			} elseif (($press = self::$request->getPress())) {
				$supportedLocales = $press->getSupportedLocaleNames();
			} else {
				$site = self::$request->getSite();
				$supportedLocales = $site->getSupportedLocaleNames();
			}
		}
		return $supportedLocales;
	}

	/**
	 * Get all supported form locales for the current context.
	 * @return array
	 */
	static function getSupportedFormLocales() {
		static $supportedFormLocales;
		if (!isset($supportedFormLocales)) {
			if (defined('SESSION_DISABLE_INIT')) {
				$supportedFormLocales = AppLocale::getAllLocales();
			} elseif (($press = self::$request->getPress())) {
				$supportedFormLocales = $press->getSupportedFormLocaleNames();
			} else {
				$site = self::$request->getSite();
				$supportedFormLocales = $site->getSupportedLocaleNames();
			}
		}
		return $supportedFormLocales;
	}

	/**
	 * Return the key name of the user's currently selected locale (default
	 * is "en_US" for U.S. English).
	 * @return string
	 */
	static function getLocale() {
		static $currentLocale;
		if (!isset($currentLocale)) {
			if (defined('SESSION_DISABLE_INIT')) {
				// If the locale is specified in the URL, allow
				// it to override. (Necessary when locale is
				// being set, as cookie will not yet be re-set)
				$locale = self::$request->getUserVar('setLocale');
				if (empty($locale) || !in_array($locale, array_keys(AppLocale::getSupportedLocales()))) $locale = self::$request->getCookieVar('currentLocale');
			} else {
				$sessionManager = SessionManager::getManager();
				$session = $sessionManager->getUserSession();
				$locale = self::$request->getUserVar('uiLocale');

				$press = self::$request->getPress();
				$site = self::$request->getSite();

				if (!isset($locale)) {
					$locale = $session->getSessionVar('currentLocale');
				}

				if (!isset($locale)) {
					$locale = self::$request->getCookieVar('currentLocale');
				}

				if (isset($locale)) {
					// Check if user-specified locale is supported
					if ($press != null) {
						$locales = $press->getSupportedLocaleNames();
					} else {
						$locales = $site->getSupportedLocaleNames();
					}

					if (!in_array($locale, array_keys($locales))) {
						unset($locale);
					}
				}

				if (!isset($locale)) {
					// Use press/site default
					if ($press != null) {
						$locale = $press->getPrimaryLocale();
					}

					if (!isset($locale)) {
						$locale = $site->getPrimaryLocale();
					}
				}
			}

			if (!AppLocale::isLocaleValid($locale)) {
				$locale = LOCALE_DEFAULT;
			}

			$currentLocale = $locale;
		}
		return $currentLocale;
	}

	/**
	 * Get the stack of "important" locales, most important first.
	 * @return array
	 */
	static function getLocalePrecedence() {
		static $localePrecedence;
		if (!isset($localePrecedence)) {
			$localePrecedence = array(AppLocale::getLocale());

			$press = self::$request->getPress();
			if ($press && !in_array($press->getPrimaryLocale(), $localePrecedence)) $localePrecedence[] = $press->getPrimaryLocale();

			$site = self::$request->getSite();
			if ($site && !in_array($site->getPrimaryLocale(), $localePrecedence)) $localePrecedence[] = $site->getPrimaryLocale();
		}
		return $localePrecedence;
	}

	/**
	 * Retrieve the primary locale of the current context.
	 * @return string
	 */
	static function getPrimaryLocale() {
		static $locale;
		if ($locale) return $locale;

		if (defined('SESSION_DISABLE_INIT')) return $locale = LOCALE_DEFAULT;

		$press = self::$request->getPress();

		if (isset($press)) {
			$locale = $press->getPrimaryLocale();
		}

		if (!isset($locale)) {
			$site = self::$request->getSite();
			$locale = $site->getPrimaryLocale();
		}

		if (!isset($locale) || !AppLocale::isLocaleValid($locale)) {
			$locale = LOCALE_DEFAULT;
		}

		return $locale;
	}

	/**
	 * Install support for an existing locale.
	 * @param $locale string
	 */
	static function installLocale($locale) {
		parent::installLocale($locale);

		$press = self::$request->getPress();
		if (!$press) { // multiple presses, admin context
			$pressDao = DAORegistry::GetDAO('PressDAO');
			$presses = $pressDao->getAll();
			$presses = $presses->toArray();
		} else {
			$presses[] = $press;
		}

		$genreDao = DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */

		foreach ($presses as $press) {
			$genreDao->installDefaults($press->getId(), array($locale));
		}

		$userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$userGroupDao->installLocale($locale);
	}

	/**
	 * Uninstall support for a new locale.
	 * @param $locale string
	 */
	static function uninstallLocale($locale) {
		parent::uninstallLocale($locale);

		$genreDao = DAORegistry::getDAO('GenreDAO');
		$genreDao->deleteSettingsByLocale($locale);

		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$userGroupDao->deleteSettingsByLocale($locale);
	}

	/**
	 * Make a map of components to their respective files.
	 * @param $locale string
	 * @return array
	 */
	static function makeComponentMap($locale) {
		$componentMap = parent::makeComponentMap($locale);
		$baseDir = "locale/$locale/";
		$componentMap[LOCALE_COMPONENT_APP_COMMON] = $baseDir . 'locale.xml';
		$componentMap[LOCALE_COMPONENT_APP_MANAGER] = $baseDir . 'manager.xml';
		$componentMap[LOCALE_COMPONENT_APP_SUBMISSION] = $baseDir . 'submission.xml';
		$componentMap[LOCALE_COMPONENT_APP_EDITOR] = $baseDir . 'editor.xml';
		$componentMap[LOCALE_COMPONENT_APP_ADMIN] = $baseDir . 'admin.xml';
		$componentMap[LOCALE_COMPONENT_APP_DEFAULT] = $baseDir . 'default.xml';
		$componentMap[LOCALE_COMPONENT_APP_API] = $baseDir . 'api.xml';
		return $componentMap;
	}
}


