<?php

/**
 * @file plugins/generic/translator/TranslatorPlugin.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TranslatorPlugin
 * @ingroup plugins_generic_translator
 *
 * @brief This plugin helps with translation maintenance.
 */



import('lib.pkp.classes.plugins.GenericPlugin');

class TranslatorPlugin extends GenericPlugin {
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				// FIXME Need to introduce the OMP Help class.
				// $this->addHelpData();
				HookRegistry::register ('LoadHandler', array(&$this, 'handleRequest'));
			}
			return true;
		}
		return false;
	}

	function handleRequest($hookName, $args) {
		$page =& $args[0];
		$op =& $args[1];
		$sourceFile =& $args[2];

		if ($page === 'translate') {
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
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('translate', __('plugins.generic.translator.translate'));
		}
		return parent::getManagementVerbs($verbs);
	}

	function getManagementVerbLinkAction(&$request, $verb, $defaultUrl) {
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher(); /* @var $dispatcher Dispatcher */

		list($verbName, $verbLocalized) = $verb;

		if ($verbName === 'translate') {
			import('lib.pkp.classes.linkAction.request.AjaxLegacyPluginModal');
			$actionRequest = new AjaxLegacyPluginModal($dispatcher->url($request, ROUTE_PAGE, null, 'translate'),
				$this->getDisplayName());
			return new LinkAction($verbName, $actionRequest, $verbLocalized, null);
		}

		return null;
	}
}

?>
