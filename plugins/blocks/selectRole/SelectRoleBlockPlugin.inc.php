<?php

/**
 * SelectRoleBlockPlugin.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectRoleBlockPlugin
 * @ingroup plugins
 *
 * @brief Class for select role block plugin
 */


import('plugins.BlockPlugin');

class SelectRoleBlockPlugin extends BlockPlugin {
	function register($category, $path) {

		if (Config::getVar('general', 'installed')) {
			$user =& Request::getUser();
			$press =& Request::getPress();
			if (!$press || !$user) return false;
		} else {
			return false;
		}

		$success = parent::register($category, $path);
		if ($success) {
			$this->addLocaleData();
		}
		return $success;
	}

	/**
	 * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
	 * @return array
	 */
	function getSupportedContexts() {
		return array(BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR);
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'SelectRoleBlockPlugin';
	}

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
	function getNewConferencePluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return Locale::translate('plugins.block.selectRole.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.block.selectRole.description');
	}

	function getContents(&$templateMgr) {
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();
		$userId =& $user->getId();

		$roles =& $roleDao->getRolesByUserId($userId, $press->getId());

		$templateMgr->assign_by_ref('roles', $roles);
		return parent::getContents($templateMgr);
	}
}

?>
