<?php

/**
 * @file plugins/generic/addThis/AddThisPlugin.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddThisPlugin
 *
 * @brief This plugin provides the AddThis social media sharing options for submissions.
 */


import('lib.pkp.classes.plugins.GenericPlugin');

class AddThisPlugin extends GenericPlugin {
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				HookRegistry::register('Templates::Catalog::Book::BookInfo::Sharing',array(&$this, 'callbackSharingDisplay'));
			}
			return true;
		}
		return false;
	}

	function getDisplayName() {
		return __('plugins.generic.addThis.displayName');
	}

	function getDescription() {
		return __('plugins.generic.addThis.description');
	}

	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', __('plugins.generic.addThis.settings'));
		}
		return parent::getManagementVerbs($verbs);
	}

	/**
	 * Define management link actions for the settings verb.
	 * @return LinkAction
	 */
	function getManagementVerbLinkAction(&$request, $verb, $defaultUrl) {
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();

		list($verbName, $verbLocalized) = $verb;

		if ($verbName === 'settings') {
			import('lib.pkp.classes.linkAction.request.AjaxLegacyPluginModal');
			$actionRequest = new AjaxLegacyPluginModal($defaultUrl,
					$this->getDisplayName());
			return new LinkAction($verbName, $actionRequest, $verbLocalized, null);
		}

		return null;
	}

	/**
	 * Define the management functionality for this plugin.
	 */
	function manage($verb, $args, &$message, &$messageParams, &$pluginModalContent) {

		$application =& Application::getApplication();
		$request =& $application->getRequest();
		$press =& $request->getPress();
		$templateMgr =& TemplateManager::getManager();

		switch ($verb) {

			case 'settings':
				$templateMgr->assign('statsConfigured', $this->statsConfigured($press));
				$pluginModalContent = $templateMgr->fetch($this->getTemplatePath() . 'settingsTabs.tpl');
				return true;

			case 'showTab':
				if ($request->getUserVar('tab') == 'settings') {
					$templateMgr =& TemplateManager::getManager();
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

					import('lib.pkp.classes.core.JSONManager');
					$jsonManager = new JSONManager();

					$jsonMessage = $jsonManager->decode($gridHandler->fetchGrid($args, $request));
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
	 * @param string $hookName
	 * @param array $params
	 */
	function callbackSharingDisplay($hookName, $params) {
		$templateMgr =& $params[1];
		$output =& $params[2];

		$application =& Application::getApplication();
		$request =& $application->getRequest();
		$press =& $request->getPress();

		$templateMgr->assign('addThisProfileId', $press->getSetting('addThisProfileId'));
		$templateMgr->assign('addThisUsername', $press->getSetting('addThisUsername'));
		$templateMgr->assign('addThisPassword', $press->getSetting('addThisPassword'));
		$templateMgr->assign('addThisDisplayStyle', $press->getSetting('addThisDisplayStyle'));

		$output .= $templateMgr->fetch($this->getTemplatePath() . 'addThis.tpl');
		return false;
	}

	/**
	 * Determines if statistics settings have been enabled for this plugin.
	 * @param Press $press
	 * @return boolean
	 */
	function statsConfigured($press) {
		$addThisUsername =& $press->getSetting('addThisUsername');
		$addThisPassword =& $press->getSetting('addThisPassword');
		$addThisProfileId =& $press->getSetting('addThisProfileId');

		if (isset($addThisUsername) && isset($addThisPassword) && isset($addThisProfileId)) {
			return true;
		}

		return false;
	}
}

?>