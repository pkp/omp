<?php

/**
 * @file classes/press/NotificationStatusDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationStatusDAO
 * @ingroup press
 *
 * @brief Operations for retrieving and modifying users' press notification status.
 */



class NotificationStatusDAO extends DAO {
	function &getPressNotifications($userId) {
		$returner = array();

		$result =& $this->retrieve(
			'SELECT p.press_id AS press_id, n.press_id AS notification FROM presses p LEFT JOIN notification_status n ON p.press_id = n.press_id AND n.user_id = ? ORDER BY p.seq',
			$userId
		);

		while (!$result->EOF) {
			$row =& $result->GetRowAssoc(false);
			$returner[$row['press_id']] = $row['notification'];
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Changes whether or not a user will receive email notifications about a given press.
	 * @param $pressId int
	 * @param $userId int
	 * @param $notificationStatus bool
	 */
	function setPressNotifications($pressId, $userId, $notificationStatus) {
		return $this->update(
			($notificationStatus ? 'INSERT INTO notification_status (user_id, press_id) VALUES (?, ?)':
			'DELETE FROM notification_status WHERE user_id = ? AND press_id = ?'),
			array($userId, $pressId)
		);
	}

	/**
	 * Delete notification status entries by press ID
	 * @param $pressId int
	 */
	function deleteNotificationStatusByPress($pressId) {
		return $this->update(
			'DELETE FROM notification_status WHERE press_id = ?', $pressId
		);
	}

	/**
	 * Delete notification status entries by user ID
	 * @param $userId int
	 */
	function deleteNotificationStatusByUserId($userId) {
		return $this->update(
			'DELETE FROM notification_status WHERE user_id = ?', $userId
		);
	}

	/**
	 * Retrieve a list of users who wish to receive updates about the specified press.
	 * @param $pressId int
	 * @return DAOResultFactory matching Users
	 */
	function &getNotifiableUsersByPressId($pressId) {
		$userDao =& DAORegistry::getDAO('UserDAO');

		$result =& $this->retrieve(
			'SELECT u.* FROM users u, notification_status n WHERE u.user_id = n.user_id AND n.press_id = ?',
			$pressId
		);

		$returner = new DAOResultFactory($result, $userDao, '_returnUserFromRow');
		return $returner;
	}

	/**
	 * Retrieve the number of users who wish to receive updates about the specified press.
	 * @param $pressId int
	 * @return int
	 */
	function getNotifiableUsersCount($pressId) {
		$userDao =& DAORegistry::getDAO('UserDAO');

		$result =& $this->retrieve(
			'SELECT count(*) FROM notification_status n WHERE n.press_id = ?',
			$pressId
		);

		$returner = $result->fields[0];

		$result->Close();
		unset($result);

		return $returner;
	}
}

?>