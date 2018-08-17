<?php

/**
 * @file classes/user/User.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class User
 * @ingroup user
 * @see UserDAO
 *
 * @brief Basic class describing users existing in the system.
 */


import('lib.pkp.classes.user.PKPUser');

class User extends PKPUser {

	function __construct() {
		parent::__construct();
	}

	/**
	 * Retrieve array of user settings.
	 * @param pressId int
	 * @return array
	 */
	function &getSettings($pressId = null) {
		$userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
		$settings =& $userSettingsDao->getSettingsByPress($this->getId(), $pressId);
		return $settings;
	}

	/**
	 * Retrieve a user setting value.
	 * @param $name
	 * @param $pressId int
	 * @return mixed
	 */
	function &getSetting($name, $pressId = null) {
		$userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
		$setting =& $userSettingsDao->getSetting($this->getId(), $name, null, $pressId);
		return $setting;
	}

	/**
	 * Set a user setting value.
	 * @param $name string
	 * @param $value mixed
	 * @param $type string optional
	 */
	function updateSetting($name, $value, $type = null, $pressId = null) {
		$userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
		return $userSettingsDao->updateSetting($this->getId(), $name, $value, $type, null, $pressId);
	}
}


