<?php

/**
 * @file classes/user/UserSettingsDAO.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserSettingsDAO
 * @ingroup user
 * @see User
 *
 * @brief Operations for retrieving and modifying user settings.
 */

import('lib.pkp.classes.user.PKPUserSettingsDAO');

class UserSettingsDAO extends PKPUserSettingsDAO {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Retrieve a user setting value.
	 * @param $userId int
	 * @param $name
	 * @param $pressId int
	 * @return mixed
	 */
	function &getSetting($userId, $name, $pressId = null) {
		return parent::getSetting($userId, $name, ASSOC_TYPE_PRESS, $pressId);
	}

	/**
	 * Retrieve all users by setting name and value.
	 * @param $name string
	 * @param $value mixed
	 * @param $type string
	 * @param $pressId int
	 * @return DAOResultFactory matching Users
	 */
	function &getUsersBySetting($name, $value, $type = null, $pressId = null) {
		return parent::getUsersBySetting($name, $value, $type, ASSOC_TYPE_PRESS, $pressId);
	}

	/**
	 * Retrieve all settings for a user for a press.
	 * @param $userId int
	 * @param $pressId int
	 * @return array
	 */
	function &getSettingsByPress($userId, $pressId = null) {
		return parent::getSettingsByAssoc($userId, ASSOC_TYPE_PRESS, $pressId);
	}

	/**
	 * Add/update a user setting.
	 * @param $userId int
	 * @param $name string
	 * @param $value mixed
	 * @param $type string data type of the setting. If omitted, type will be guessed
	 * @param $pressId int
	 */
	function updateSetting($userId, $name, $value, $type = null, $pressId = null) {
		return parent::updateSetting($userId, $name, $value, $type, ASSOC_TYPE_PRESS, $pressId);
	}

	/**
	 * Delete a user setting.
	 * @param $userId int
	 * @param $name string
	 * @param $pressId int
	 */
	function deleteSetting($userId, $name, $pressId = null) {
		return parent::deleteSetting($userId, $name, ASSOC_TYPE_PRESS, $pressId);
	}
}


