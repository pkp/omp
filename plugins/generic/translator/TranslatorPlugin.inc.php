<?php

/**
 * @file plugins/generic/translator/TranslatorPlugin.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TranslatorPlugin
 * @ingroup plugins_generic_translator
 *
 * @brief This plugin helps with translation maintenance.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class TranslatorPlugin extends GenericPlugin {
	/**
	 * Register the plugin
	 * @param $category string Plugin category
	 * @param $path string Plugin path
	 * @return boolean True on success
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				// Add the TranslatorHandler to the list of page handlers
				HookRegistry::register ('LoadHandler', array(&$this, 'handleRequest'));

				// Allow the Translate tab to appear on website settings
				HookRegistry::register('Templates::Management::Settings::website', array($this, 'callbackShowWebsiteSettingsTabs'));

				// Register the components this plugin implements to
				// permit administration of static pages.
				HookRegistry::register('LoadComponentHandler', array($this, 'setupComponentHandlers'));

				// Bring in the TranslatorAction helper class.
				$this->import('TranslatorAction');
			}
			return true;
		}
		return false;
	}

	/**
	 * Extend the website settings tabs to include translation
	 * @param $hookName string The name of the invoked hook
	 * @param $args array Hook parameters
	 * @return boolean Hook handling status
	 */
	function callbackShowWebsiteSettingsTabs($hookName, $args) {
		$output =& $args[2];
		$request = Registry::get('request');
		$dispatcher = $request->getDispatcher();

		// Add a new tab for static pages
		$output .= '<li><a name="translate" href="' . $dispatcher->url($request, ROUTE_COMPONENT, null, 'plugins.generic.translator.controllers.grid.LocaleGridHandler', 'index') . '">' . __('plugins.generic.translator.translate') . '</a></li>';

		// Permit other plugins to continue interacting with this hook
		return false;
	}

	/**
	 * Permit requests to the static pages grid handler
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	 */
	function setupComponentHandlers($hookName, $params) {
		$component =& $params[0];
		switch ($component) {
			case 'plugins.generic.translator.controllers.grid.LocaleGridHandler':
			case 'plugins.generic.translator.controllers.grid.LocaleFileGridHandler':
			case 'plugins.generic.translator.controllers.listbuilder.LocaleFileListbuilderHandler':
				// Allow the static page grid handler to get the plugin object
				import($component);
				$className = array_pop(explode('.', $component));
				$className::setPlugin($this);
				return true;
		}
		return false;
	}

	function handleRequest($hookName, $args) {
		$page =& $args[0];
		$op =& $args[1];
		$sourceFile =& $args[2];

		if ($page === 'translate' && in_array($op, array('index', 'edit', 'check', 'export', 'saveLocaleChanges', 'downloadLocaleFile', 'editLocaleFile', 'editMiscFile', 'saveLocaleFile', 'deleteLocaleKey', 'saveMiscFile', 'editEmail', 'createFile', 'deleteEmail', 'saveEmail'))) {
			$this->import('TranslatorHandler');
			Registry::set('plugin', $this);
			define('HANDLER_CLASS', 'TranslatorHandler');
			return true;
		}

		return false;
	}

	function getDisplayName() {
		return __('plugins.generic.translator.name');
	}

	function getDescription() {
		return __('plugins.generic.translator.description');
	}

	function isSitePlugin() {
		return true;
	}

	function getManagementVerbs() {
		$verbs = parent::getManagementVerbs();
		if ($this->getEnabled()) {
			$verbs[] = array('translate', __('plugins.generic.translator.translate'));
		}
		return $verbs;
	}

	/**
	 * @copydoc Plugin::getManagementVerbLinkAction()
	 */
	function getManagementVerbLinkAction($request, $verb) {
		list($verbName, $verbLocalized) = $verb;

		switch ($verbName) {
			case 'translate':
				// Generate a link action for the "settings" action
				$dispatcher = $request->getDispatcher();
				import('lib.pkp.classes.linkAction.request.RedirectAction');
				return new LinkAction(
					$verbName,
					new RedirectAction($dispatcher->url(
						$request, ROUTE_PAGE,
						null, 'management', 'settings', 'website',
						array('uid' => uniqid()), // Force reload
						'translate' // Anchor for tab
					)),
					$verbLocalized,
					null
				);
			default:
				return parent::getManagementVerbLinkAction($request, $verb);
		}
	}

	/**
	 * Get the JavaScript URL for this plugin.
	 */
	function getJavaScriptURL($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath() . '/js';
	}

	/**
	 * @copydoc PKPPlugin::getTemplatePath
	 */
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}
}

?>
