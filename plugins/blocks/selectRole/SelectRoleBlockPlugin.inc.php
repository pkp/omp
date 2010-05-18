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


import('lib.pkp.classes.plugins.BlockPlugin');

class SelectRoleBlockPlugin extends BlockPlugin {

	/** @var User */
	var $_user;

	/** @var Press */
	var $_press;

	function register($category, $path) {
		if (Config::getVar('general', 'installed')) {
			$request =& Registry::get('request');
			$this->_user = $request->getUser();
			if ($this->_user) {
				$router =& $request->getRouter();
				$this->_press =& $router->getContext($request);
				if (!$this->_press) return false;
			} else return false;
		} else {
			return false;
		}

		$success = parent::register($category, $path);
		return $success;
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
	function getContextSpecificPluginSettingsFile() {
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
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userId =& $this->_user->getId();

		$userGroups =& $userGroupDao->getByUserId($userId, $this->_press->getId());

		$templateMgr->assign_by_ref('userGroups', $userGroups);
		return parent::getContents($templateMgr);
	}
}

?>
