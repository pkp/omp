<?php

/**
 * @file classes/plugins/Plugin.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Plugin
 * @ingroup plugins
 *
 * @brief Abstract class for plugins
 */


import('lib.pkp.classes.plugins.PKPPlugin');

class Plugin extends PKPPlugin {
	/**
	 * Constructor
	 */
	function Plugin() {
		parent::PKPPlugin();
	}

	/**
	 * Backwards compatible convenience version of
	 * the generic getContextSpecificSetting() method.
	 *
	 * @see PKPPlugin::getContextSpecificSetting()
	 *
	 * @param $pressId
	 * @param $name
	 */
	function getSetting($pressId, $name) {
		if (defined('RUNNING_UPGRADE')) {
			// Bug #2504: Make sure plugin_settings table is not
			// used if it's not available.
			$versionDao =& DAORegistry::getDAO('VersionDAO');
			$version =& $versionDao->getCurrentVersion();
			if ($version->compare('2.1.0') < 0) return null;
		}
		return $this->getContextSpecificSetting(array($pressId), $name);
	}

	/**
	 * Backwards compatible convenience version of
	 * the generic updateContextSpecificSetting() method.
	 *
	 * @see PKPPlugin::updateContextSpecificSetting()
	 *
	 * @param $pressId int
	 * @param $name string The name of the setting
	 * @param $value mixed
	 * @param $type string optional
	 */
	function updateSetting($pressId, $name, $value, $type = null) {
		$this->updateContextSpecificSetting(array($pressId), $name, $value, $type);
	}

	/**
	 * Get the filename of the settings data for this plugin to install
	 * when a press is created (i.e. press-level plugin settings).
	 * Subclasses using default settings should override this.
	 *
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		// The default implementation delegates to the old
		// method for backwards compatibility.
		return $this->getNewPressPluginSettingsFile();
	}

	/**
	 * For backwards compatibility only.
	 *
	 * New plug-ins should override getContextSpecificPluginSettingsFile()
	 *
	 * @see PKPPlugin::getContextSpecificPluginSettingsFile()
	 *
	 * @return string
	 */
	function getNewPressPluginSettingsFile() {
		return null;
	}
}

?>
