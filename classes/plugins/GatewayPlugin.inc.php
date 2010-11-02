<?php

/**
 * @file classes/plugins/GatewayPlugin.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GatewayPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for gateway plugins
 */



import('classes.plugins.Plugin');

class GatewayPlugin extends Plugin {
	function GatewayPlugin() {
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
	 * Press Manager's plugin management page, for example.
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
	 * Display verbs for the management interface.
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array(
				'disable',
				Locale::translate('manager.plugins.disable')
			);
		} else {
			$verbs[] = array(
				'enable',
				Locale::translate('manager.plugins.enable')
			);
		}
		return $verbs;
	}

	/**
	 * Determine whether or not this plugin is enabled.
	 */
	function getEnabled() {
		$press =& Request::getPress();
		if (!$press) return false;
		return $this->getSetting($press->getPressId(), 'enabled');
	}

	/**
	 * Set the enabled/disabled state of this plugin
	 */
	function setEnabled($enabled) {
		$press =& Request::getPress();
		if ($press) {
			$this->updateSetting(
				$press->getPressId(),
				'enabled',
				$enabled ? true : false
			);
			return true;
		}
		return false;
	}

	/**
	 * Perform management functions
	 */
	function manage($verb, $args) {
		$templateManager =& TemplateManager::getManager();
		$templateManager->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
		switch ($verb) {
			case 'enable': $this->setEnabled(true); break;
			case 'disable': $this->setEnabled(false); break;
		}
		return false;
	}

	/**
	 * Handle fetch requests for this plugin.
	 * @param $args array
	 * @param $request object
	 */
	function fetch($args, $request) {
		// Subclasses should override this function.
		return false;
	}
}

?>
