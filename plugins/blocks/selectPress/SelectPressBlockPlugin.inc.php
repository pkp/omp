<?php

/**
 * SelectPressBlockPlugin.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectPressBlockPlugin
 * @ingroup plugins
 *
 * @brief Class for select role block plugin
 */


import('lib.pkp.classes.plugins.BlockPlugin');

class SelectPressBlockPlugin extends BlockPlugin {
	/**
	 * Install default settings on system install.
	 * @return string
	 */
	function getInstallSitePluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Install default settings on conference creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return Locale::translate('plugins.block.selectPress.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.block.selectPress.description');
	}

	function getContents(&$templateMgr) {
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses =& $pressDao->getPresses();
		$presses =& $presses->toArray();

		$templateMgr->assign_by_ref('presses', $presses);
		return parent::getContents($templateMgr);
	}
}

?>
