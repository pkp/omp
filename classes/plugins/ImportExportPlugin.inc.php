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

import('lib.pkp.classes.plugins.PKPImportExportPlugin');

abstract class ImportExportPlugin extends PKPImportExportPlugin {
	/**
	 * Constructor
	 */
	function ImportExportPlugin() {
		parent::PKPImportExportPlugin();
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
		$request = $this->getRequest();
		if ($verb === 'importexport') {
			$request->redirectUrl($this->getManagementVerbUrl($verb));
		}
		return false;
	}

	/**
	 * @copydoc PKPImportExportPlugin::smartyPluginUrl
	 */
	function smartyPluginUrl($params, $smarty) {
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
