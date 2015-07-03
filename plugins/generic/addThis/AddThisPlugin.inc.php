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
				HookRegistry::register('Templates::Catalog::Book::BookInfo::Sharing',array(&$this, 'callbackSharingDisplay'));
			}
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
	 * @copydoc PKPPlugin::getManagementVerbs()
	 */
	function getManagementVerbs() {
		$verbs = parent::getManagementVerbs();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', __('plugins.generic.addThis.settings'));
		}
		return $verbs;
	}

	/**
	 * Define management link actions for the settings verb.
	 * @param $request PKPRequest
	 * @param $verb string
	 * @return LinkAction
	 */
	function getManagementVerbLinkAction($request, $verb) {
		$router = $request->getRouter();

		list($verbName, $verbLocalized) = $verb;

		if ($verbName === 'settings') {
			import('lib.pkp.classes.linkAction.request.AjaxLegacyPluginModal');
			$actionRequest = new AjaxLegacyPluginModal(
				$router->url($request, null, null, 'plugin', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
				$this->getDisplayName()
			);
			return new LinkAction($verbName, $actionRequest, $verbLocalized, null);
		}

		return null;
	}

	/**
	 * @copydoc PKPPlugin::manage()
	 */
	function manage($verb, $args, &$message, &$messageParams, &$pluginModalContent = null) {
		$request = $this->getRequest();
		$press = $request->getPress();
		$templateMgr = TemplateManager::getManager($request);

		switch ($verb) {

			case 'settings':
				$templateMgr->assign('statsConfigured', $this->statsConfigured($press));
				$pluginModalContent = $templateMgr->fetch($this->getTemplatePath() . 'settingsTabs.tpl');
				return true;

			case 'showTab':
				if ($request->getUserVar('tab') == 'settings') {
					$this->import('AddThisSettingsForm');
					$form = new AddThisSettingsForm($this, $press);
					if ($request->getUserVar('save')) {
						$form->readInputData();
						if ($form->validate()) {
							$form->execute();
							$message = NOTIFICATION_TYPE_SUCCESS;
							$messageParams = array('contents' => __('plugins.generic.addThis.form.saved'));
							return false;
						} else {
							$pluginModalContent = $form->fetch($request);
						}
					} else {
						$form->initData();
						$pluginModalContent = $form->fetch($request);
					}
				} else {
					$pluginModalContent = $templateMgr->fetch($this->getTemplatePath() . 'statistics.tpl');
				}
				return true;

			case 'showStatistics':
					$this->import('AddThisStatisticsGridHandler');
					$gridHandler = new AddThisStatisticsGridHandler($this);
					$gridHandler->initialize($request);

					$jsonMessage = json_decode($gridHandler->fetchGrid($args, $request));
					$pluginModalContent = $jsonMessage->content;
				return true;

			default:
				// let the parent handle it.
				return parent::manage($verb, $args, $message, $messageParams);
		}
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

		if (isset($addThisUsername) && isset($addThisPassword) && isset($addThisProfileId)) {
			return true;
		}

		return false;
	}
}

?>
