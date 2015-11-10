<?php

/**
 * @file plugins/generic/addThis/AddThisPlugin.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddThisPlugin
 *
 * @brief This plugin provides the AddThis social media sharing options for submissions.
 */


import('lib.pkp.classes.plugins.GenericPlugin');

class AddThisPlugin extends GenericPlugin {
	/**
	 * Register the plugin.
	 * @param $category string
	 * @param $path string
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				HookRegistry::register('Templates::Catalog::Book::Details', array($this, 'callbackSharingDisplay'));
				// Register the components this plugin implements
				HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Permit requests to the statistics grid handler
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	 */
	function setupGridHandler($hookName, $params) {
		$component =& $params[0];
		if ($component == 'plugins.generic.addThis.controllers.grid.AddThisStatisticsGridHandler') {
			// Allow the static page grid handler to get the plugin object
			import($component);
			AddThisStatisticsGridHandler::setPlugin($this);
			return true;
		}
		return false;
	}

	/**
	 * Get the name of the settings file to be installed on new press
	 * creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * @copydoc PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.addThis.displayName');
	}

	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.addThis.description');
	}

	/**
	 * @copydoc PKPPlugin::getTemplatePath
	 */
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}

	/**
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $actionArgs) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array_merge($actionArgs, array('verb' => 'settings'))),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			):array(),
			parent::getActions($request, $actionArgs)
		);
	}

	/**
	 * @copydoc PKPPlugin::manage()
	 */
	function manage($args, $request) {
		$press = $request->getPress();
		$templateMgr = TemplateManager::getManager($request);

		switch ($request->getUserVar('verb')) {
			case 'showTab':
				switch ($request->getUserVar('tab')) {
					case 'settings':
						$this->import('AddThisSettingsForm');
						$form = new AddThisSettingsForm($this, $press);
						if ($request->getUserVar('save')) {
							$form->readInputData();
							if ($form->validate()) {
								$form->execute();
								return new JSONMessage();
							}
						} else {
							$form->initData();
						}
						return new JSONMessage(true, $form->fetch($request));
					case 'statistics':
						return $templateMgr->fetchJson($this->getTemplatePath() . 'statistics.tpl');
					default: assert(false);
				}
			case 'settings':
				$templateMgr->assign('statsConfigured', $this->statsConfigured($press));
				$templateMgr->assign('pluginName', $this->getName());
				return $templateMgr->fetchJson($this->getTemplatePath() . 'settingsTabs.tpl');

		}
		return parent::manage($args, $request);
	}

	/**
	 * Hook against Templates::Catalog::Book::BookInfo::Sharing, for including the
	 * addThis code on submission display.
	 * @param $hookName string
	 * @param $params array
	 */
	function callbackSharingDisplay($hookName, $params) {
		$templateMgr = $params[1];
		$output =& $params[2];

		$request = $this->getRequest();
		$press = $request->getPress();

		$templateMgr->assign('addThisProfileId', $press->getSetting('addThisProfileId'));
		$templateMgr->assign('addThisUsername', $press->getSetting('addThisUsername'));
		$templateMgr->assign('addThisPassword', $press->getSetting('addThisPassword'));
		$templateMgr->assign('addThisDisplayStyle', $press->getSetting('addThisDisplayStyle'));

		$output .= $templateMgr->fetch($this->getTemplatePath() . 'addThis.tpl');
		return false;
	}

	/**
	 * Determines if statistics settings have been enabled for this plugin.
	 * @param $press Press
	 * @return boolean
	 */
	function statsConfigured($press) {
		$addThisUsername = $press->getSetting('addThisUsername');
		$addThisPassword = $press->getSetting('addThisPassword');
		$addThisProfileId = $press->getSetting('addThisProfileId');
		return (isset($addThisUsername) && isset($addThisPassword) && isset($addThisProfileId));
	}
}

?>
