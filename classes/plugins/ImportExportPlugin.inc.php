<?php

/**
 * @file classes/plugins/ImportExportPlugin.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ImportExportPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for import/export plugins
 */

import('lib.pkp.classes.plugins.Plugin');

class ImportExportPlugin extends Plugin {
	function ImportExportPlugin() {
		parent::Plugin();
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		assert(false); // Should always be overridden
	}

	/**
	 * Get the display name of this plugin. This name is displayed on the
	 * Press Manager's import/export page, for example.
	 * @return String
	 */
	function getDisplayName() {
		assert(false); // Should always be overridden
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		assert(false); // Should always be overridden
	}

	/**
	 * Display the import/export plugin UI.
	 * @param $args Array The array of arguments the user supplied.
	 */
	function display($args, $request) {
		$templateManager = TemplateManager::getManager($request);
		$templateManager->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
	}

	/**
	 * Execute import/export tasks using the command-line interface.
	 * @param $scriptName The name of the command-line script (displayed as usage info)
	 * @param $args Parameters to the plugin
	 */
	function executeCLI($scriptName, $args) {
		$this->usage();
		// Implemented by subclasses
	}

	/**
	 * Display the command-line usage information
	 */
	function usage($scriptName) {
		// Implemented by subclasses
	}

	/**
	 * Display verbs for the management interface.
	 */
	function getManagementVerbs() {
		return array(
			array(
				'importexport',
				__('manager.importExport')
			)
		);
	}

	/**
	 * @see Plugin::getManagementVerbLinkAction()
	 */
	function getManagementVerbLinkAction($request, $verb) {
		$router = $request->getRouter();
		$dispatcher = $router->getDispatcher();

		list($verbName, $verbLocaleKey) = $verb;

		switch($verbName) {
			case 'importexport':
				import('lib.pkp.classes.linkAction.request.RedirectAction');
				$actionRequest = new RedirectAction($dispatcher->url($request, ROUTE_PAGE, null, 'manager',
					'importexport', array('plugin', $this->getName())));

				$linkAction = new LinkAction(
					$verbName,
					$actionRequest,
					$verbLocaleKey,
					null
				);

				return $linkAction;
			default:
				return array();
		}
	}

	/**
	 * @see PKPPlugin::manage($verb, $args, $message, $messageParams, $pluginModalContent)
	 */
	function manage($verb, $args, &$message, &$messageParams, &$pluginModalContent = null) {
		if ($verb === 'importexport') {
			$request->redirectUrl($this->getManagementVerbUrl($verb));
		}
		return false;
	}

	/**
	 * Extend the {url ...} smarty to support import/export plugins.
	 */
	function smartyPluginUrl($params, &$smarty) {
		$path = array('plugin', $this->getName());
		if (is_array($params['path'])) {
			$params['path'] = array_merge($path, $params['path']);
		} elseif (!empty($params['path'])) {
			$params['path'] = array_merge($path, array($params['path']));
		} else {
			$params['path'] = $path;
		}
		return $smarty->smartyUrl($params, $smarty);
	}

}
?>
