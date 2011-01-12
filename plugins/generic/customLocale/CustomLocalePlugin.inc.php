<?php

/**
 * @file CustomLocalePlugin.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomLocalePlugin
 *
 * @brief This plugin enables customization of locale strings.
 */



define('CUSTOM_LOCALE_DIR', 'customLocale');
import('lib.pkp.classes.plugins.GenericPlugin');

class CustomLocalePlugin extends GenericPlugin {
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				import('lib.pkp.classes.file.FileManager');
				$press = Request::getPress();
				$pressId = $press->getId();
				$locale = Locale::getLocale();
				$localeFiles = Locale::getLocaleFiles($locale);
				$publicFilesDir = Config::getVar('files', 'public_files_dir');
				$customLocaleDir = $publicFilesDir . DIRECTORY_SEPARATOR . 'presses' . DIRECTORY_SEPARATOR . $pressId . DIRECTORY_SEPARATOR . CUSTOM_LOCALE_DIR;

				foreach ($localeFiles as $localeFile) {
					$localeFilename = $localeFile->getFilename();
					$customLocalePath = $customLocaleDir . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $localeFilename;
					if (FileManager::fileExists($customLocalePath)) {
						Locale::registerLocaleFile($locale, $customLocalePath, true);
					}
				}
			}

			return true;
		}
		return false;
	}

	function getDisplayName() {
		return Locale::translate('plugins.generic.customLocale.name');
	}

	function getDescription() {
		return Locale::translate('plugins.generic.customLocale.description');
	}

	function smartyPluginUrl($params, &$smarty) {
		$path = array($this->getCategory(), $this->getName());
		if (is_array($params['path'])) {
			$params['path'] = array_merge($path, $params['path']);
		} elseif (!empty($params['path'])) {
			$params['path'] = array_merge($path, array($params['path']));
		} else {
			$params['path'] = $path;
		}

		if (!empty($params['key'])) {
			$params['path'] = array_merge($params['path'], array($params['key']));
			unset($params['key']);
		}

		if (!empty($params['file'])) {
			$params['path'] = array_merge($params['path'], array($params['file']));
			unset($params['file']);
		}

		return $smarty->smartyUrl($params, $smarty);
	}

	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('index', Locale::translate('plugins.generic.customLocale.customize'));
		}
		return parent::getManagementVerbs($verbs);
	}

	function manage($verb, $args) {
		if (!parent::manage($verb, $args, $message)) return false;

		$this->import('CustomLocaleHandler');
		$customLocaleHandler = new CustomLocaleHandler();
		switch ($verb) {
			case 'edit':
				$customLocaleHandler->edit($args);
				return true;
			case 'saveLocaleChanges':
				$customLocaleHandler->saveLocaleChanges($args);
				return true;
			case 'editLocaleFile':
				$customLocaleHandler->editLocaleFile($args);
				return true;
			case 'saveLocaleFile':
				$customLocaleHandler->saveLocaleFile($args);
				return true;
			default:
				$customLocaleHandler->index();
				return true;
		}
	}
}

?>
