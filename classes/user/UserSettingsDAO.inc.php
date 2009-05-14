<?php

/**
 * @file classes/user/UserSettingsDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserSettingsDAO
 * @ingroup user
 * @see User
 *
 * @brief Operations for retrieving and modifying user settings.
 */

// $Id$


class UserSettingsDAO extends DAO {
	/**
	 * Retrieve a user setting value.
	 * @param $userId int
	 * @param $name
	 * @param $pressId int
	 * @return mixed
	 */
	function &getSetting($userId, $name, $pressId = null) {
		$result =& $this->retrieve(
			'SELECT setting_value, setting_type FROM user_settings WHERE user_id = ? AND setting_name = ? AND press_id = ?',
			array((int) $userId, $name, (int) $pressId)
		);

		if ($result->RecordCount() != 0) {
			$row =& $result->getRowAssoc(false);
			$returner = $this->convertFromDB($row['setting_value'], $row['setting_type']);
		} else {
			$returner = null;
		}

		return $returner;
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
		$userDao =& DAORegistry::getDAO('UserDAO');

		$value = $this->convertToDB($value, $type);
		$result =& $this->retrieve(
			'SELECT u.* FROM users u, user_settings s WHERE u.user_id = s.user_id AND s.setting_name = ? AND s.setting_value = ? AND s.press_id = ?',
			array($name, $value, (int) $pressId)
		);

		$returner =& new DAOResultFactory($result, $userDao, '_returnUserFromRow');
		return $returner;
	}

	/**
	 * Retrieve all settings for a user for a press.
	 * @param $userId int
	 * @param $pressId int
	 * @return array 
	 */
	function &getSettingsByPress($userId, $pressId = null) {
		$userSettings = array();

		$result =& $this->retrieve(
			'SELECT setting_name, setting_value, setting_type FROM user_settings WHERE user_id = ? and press_id = ?',
			array((int) $userId, (int) $pressId)
		);

		while (!$result->EOF) {
			$row =& $result->getRowAssoc(false);
			$value = $this->convertFromDB($row['setting_value'], $row['setting_type']);
			$userSettings[$row['setting_name']] = $value;
			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		return $userSettings;
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
		$result = $this->retrieve(
			'SELECT COUNT(*) FROM user_settings WHERE user_id = ? AND setting_name = ? AND press_id = ?',
			array($userId, $name, (int) $pressId)
		);

		$value = $this->convertToDB($value, $type);
		if ($result->fields[0] == 0) {
			$returner = $this->update(
				'INSERT INTO user_settings
					(user_id, setting_name, press_id, setting_value, setting_type)
					VALUES
					(?, ?, ?, ?, ?)',
				array((int) $userId, $name, (int) $pressId, $value, $type)
			);
		} else {
			$returner = $this->update(
				'UPDATE user_settings SET
					setting_value = ?,
					setting_type = ?
					WHERE user_id = ? AND setting_name = ? AND press_id = ?',
				array($value, $type, (int) $userId, $name, (int) $pressId)
			);
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Delete a user setting.
	 * @param $userId int
	 * @param $name string
	 * @param $pressId int
	 */
	function deleteSetting($userId, $name, $pressId = null) {
		return $this->update(
			'DELETE FROM user_settings WHERE user_id = ? AND setting_name = ? AND press_id = ?',
			array((int) $userId, $name, (int) $pressId)
		);
	}

	/**
	 * Delete all settings for a user.
	 * @param $userId int
	 */
	function deleteSettings($userId) {
		return $this->update(
			'DELETE FROM user_settings WHERE user_id = ?', $userId
		);
	}
}

?>
