<?php

/**
 * @file classes/press/AcquisitionsArrangementEditorsDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AcquisitionsArrangementEditorsDAO
 * @ingroup press
 *
 * @brief Class for DAO relating acquisition arrangements to editors.
 */

// $Id$


class AcquisitionsArrangementEditorsDAO extends DAO {
	/**
	 * Insert a new arrangement editor.
	 * @param $pressId int
	 * @param $arrangementId int
	 * @param $userId int
	 * @param $canReview boolean
	 * @param $canEdit boolean
	 */
	function insertEditor($pressId, $arrangementId, $userId, $canReview, $canEdit) {
		return $this->update(
			'INSERT INTO acquisitions_arrangements_editors
				(press_id, arrangement_id, user_id, can_review, can_edit)
				VALUES
				(?, ?, ?, ?, ?)',
			array(
				$pressId,
				$arrangementId,
				$userId,
				$canReview ? 1 : 0,
				$canEdit ? 1 : 0
			)
		);
	}

	/**
	 * Delete a arrangement editor.
	 * @param $pressId int
	 * @param $arrangementId int
	 * @param $userId int
	 */
	function deleteEditor($pressId, $arrangementId, $userId) {
		return $this->update(
			'DELETE FROM acquisitions_arrangements_editors WHERE press_id = ? AND arrangement_id = ? AND user_id = ?',
			array(
				$pressId,
				$arrangementId,
				$userId
			)
		);
	}

	/**
	 * Retrieve a list of all arrangement editors assigned to the specified arrangement.
	 * @param $arrangementId int
	 * @param $pressId int
	 * @return array matching Users
	 */
	function &getEditorsByArrangementId($arrangementId, $pressId) {
		$users = array();

		$userDao =& DAORegistry::getDAO('UserDAO');

		$result =& $this->retrieve(
			'SELECT u.*, e.can_review AS can_review, e.can_edit AS can_edit FROM users AS u, acquisitions_arrangements_editors AS e WHERE u.user_id = e.user_id AND e.press_id = ? AND e.arrangement_id = ? ORDER BY last_name, first_name',
			array($pressId, $arrangementId)
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$users[] = array(
				'user' => $userDao->_returnUserFromRow($row),
				'canReview' => $row['can_review'],
				'canEdit' => $row['can_edit']
			);
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $users;
	}

	/**
	 * Retrieve a list of all arrangement editors not assigned to the specified arrangement.
	 * @param $pressId int
	 * @param $arrangementId int
	 * @return array matching Users
	 */
	function &getEditorsNotInArrangement($pressId, $arrangementId) {
		$users = array();

		$userDao =& DAORegistry::getDAO('UserDAO');

		$result =& $this->retrieve(
			'SELECT	u.*
			FROM	users u
				LEFT JOIN roles r ON (r.user_id = u.user_id)
				LEFT JOIN acquisitions_arrangements_editors e ON (e.user_id = u.user_id AND e.press_id = r.press_id AND e.arrangement_id = ?)
			WHERE	r.press_id = ? AND
				r.role_id = ? AND
				e.arrangement_id IS NULL
			ORDER BY last_name, first_name',
			array($arrangementId, $pressId, ROLE_ID_ACQUISITIONS_EDITOR)
		);

		while (!$result->EOF) {
			$users[] =& $userDao->_returnUserFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $users;
	}

	/**
	 * Delete all acquisitions arrangement editors for a specified arrangement in a press.
	 * @param $arrangementId int
	 * @param $pressId int
	 */
	function deleteEditorsByArrangementId($arrangementId, $pressId = null) {
		if (isset($pressId)) return $this->update(
			'DELETE FROM acquisitions_arrangements_editors WHERE press_id = ? AND arrangement_id = ?',
			array($pressId, $arrangementId)
		);
		else return $this->update(
			'DELETE FROM acquisitions_arrangements_editors WHERE arrangement_id = ?',
			$arrangementId
		);
	}

	/**
	 * Delete all acquisitions editors assignments for a specified press.
	 * @param $pressId int
	 */
	function deleteEditorsByPressId($pressId) {
		return $this->update(
			'DELETE FROM acquisitions_arrangements_editors WHERE press_id = ?', $pressId
		);
	}

	/**
	 * Delete all arrangement assignments for the specified user.
	 * @param $userId int
	 * @param $pressId int optional, include assignments only in this press
	 * @param $arrangementId int optional, include only this arrangement
	 */
	function deleteEditorsByUserId($userId, $pressId  = null, $arrangementId = null) {
		return $this->update(
			'DELETE FROM acquisitions_arrangements_editors WHERE user_id = ?' . (isset($pressId) ? ' AND press_id = ?' : '') . (isset($arrangementId) ? ' AND arrangement_id = ?' : ''),
			isset($pressId) && isset($arrangementId) ? array($userId, $pressId, $arrangementId)
			: (isset($pressId) ? array($userId, $pressId)
			: (isset($arrangementId) ? array($userId, $arrangementId) : $userId))
		);
	}

	/**
	 * Check if a user is assigned to a specified arrangement.
	 * @param $pressId int
	 * @param $arrangementId int
	 * @param $userId int
	 * @return boolean
	 */
	function editorExists($pressId, $arrangementId, $userId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM acquisitions_arrangements_editors WHERE press_id = ? AND arrangement_id = ? AND user_id = ?', array($pressId, $arrangementId, $userId)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}
}

?>
