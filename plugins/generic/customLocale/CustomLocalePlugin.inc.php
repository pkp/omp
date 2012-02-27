<?php

/**
 * @file plugins/generic/customLocale/CustomLocalePlugin.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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
				$fileManager = new FileManager();

				$press = Request::getPress();
				$pressId = $press->getId();
				$locale = AppLocale::getLocale();
				$localeFiles = AppLocale::getLocaleFiles($locale);
				$publicFilesDir = Config::getVar('files', 'public_files_dir');
				$customLocaleDir = $publicFilesDir . DIRECTORY_SEPARATOR . 'presses' . DIRECTORY_SEPARATOR . $pressId . DIRECTORY_SEPARATOR . CUSTOM_LOCALE_DIR;

				foreach ($localeFiles as $localeFile) {
					$localeFilename = $localeFile->getFilename();
					$customLocalePath = $customLocaleDir . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $localeFilename;
					if ($fileManager->fileExists($customLocalePath)) {
						AppLocale::registerLocaleFile($locale, $customLocalePath, true);
					}
				}
				HookRegistry::register ('LoadHandler', array(&$this, 'handleRequest'));
			}

			return true;
		}
		return false;
	}

	function handleRequest($hookName, $args) {
		$page =& $args[0];

		if ($page === 'index') {
			$this->import('CustomLocaleHandler');
			Registry::set('plugin', $this);
			define('HANDLER_CLASS', 'CustomLocaleHandler');
			return true;
		}

		return false;
	}

	function getDisplayName() {
		return __('plugins.generic.customLocale.name');
	}

	function getDescription() {
		return __('plugins.generic.customLocale.description');
	}

	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('index', __('plugins.generic.customLocale.customize'));
		}
		return parent::getManagementVerbs($verbs);
	}

	function getManagementVerbLinkAction(&$request, $verb) {
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher(); /* @var $dispatcher Dispatcher */

		list($verbName, $verbLocalized) = $verb;

		if ($verbName === 'index') {
			import('lib.pkp.classes.linkAction.request.AjaxLegacyPluginModal');
			$actionRequest = new AjaxLegacyPluginModal($dispatcher->url($request, ROUTE_PAGE, null, 'index'),
				$this->getDisplayName());
			return new LinkAction($verbName, $actionRequest, $verbLocalized, null);
		}

		return null;
	}
}

?>
