<?php

/**
 * @file classes/press/SeriesEditorsDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorsDAO
 * @ingroup press
 *
 * @brief Class for DAO relating series to editors.
 */


class SeriesEditorsDAO extends DAO {
	/**
	 * Constructor
	 */
	function SeriesEditorsDAO() {
		parent::DAO();
	}

	/**
	 * Insert a new series editor.
	 * @param $pressId int
	 * @param $seriesId int
	 * @param $userId int
	 * @param $canReview boolean
	 * @param $canEdit boolean
	 */
	function insertEditor($pressId, $seriesId, $userId, $canReview, $canEdit) {
		return $this->update(
			'INSERT INTO series_editors
				(press_id, series_id, user_id, can_review, can_edit)
				VALUES
				(?, ?, ?, ?, ?)',
			array(
				(int) $pressId,
				(int) $seriesId,
				(int) $userId,
				$canReview ? 1 : 0,
				$canEdit ? 1 : 0
			)
		);
	}

	/**
	 * Delete a series editor.
	 * @param $pressId int
	 * @param $seriesId int
	 * @param $userId int
	 */
	function deleteEditor($pressId, $seriesId, $userId) {
		return $this->update(
			'DELETE FROM series_editors WHERE press_id = ? AND series_id = ? AND user_id = ?',
			array(
				(int) $pressId,
				(int) $seriesId,
				(int) $userId
			)
		);
	}

	/**
	 * Retrieve a list of all series editors assigned to the specified series.
	 * @param $seriesId int
	 * @param $pressId int
	 * @return array matching Users
	 */
	function getEditorsBySeriesId($seriesId, $pressId) {
		$users = array();

		$userDao = DAORegistry::getDAO('UserDAO');

		$result = $this->retrieve(
			'SELECT	u.*, e.can_review AS can_review, e.can_edit AS can_edit FROM users AS u, series_editors AS e WHERE u.user_id = e.user_id AND e.press_id = ? AND e.series_id = ? ORDER BY last_name, first_name',
			array((int) $pressId, (int) $seriesId)
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$users[] = array(
				'user' => $userDao->_returnUserFromRow($row),
				'canReview' => $row['can_review'],
				'canEdit' => $row['can_edit']
			);
			$result->MoveNext();
		}

		$result->Close();
		return $users;
	}

	/**
	 * Retrieve a list of all series editors not assigned to the specified series.
	 * @param $pressId int
	 * @param $seriesId int
	 * @return array matching Users
	 */
	function getEditorsNotInSeries($pressId, $seriesId) {
		$users = array();

		$userDao = DAORegistry::getDAO('UserDAO');

		$result = $this->retrieve(
			'SELECT	u.*
			FROM	users u
				JOIN user_user_groups uug ON (u.user_id = uug.user_id)
				JOIN user_groups ug ON (uug.user_group_id = ug.user_group_id AND ug.role_id = ? AND ug.context_id = ?)
				LEFT JOIN series_editors e ON (e.user_id = u.user_id AND e.press_id = ug.context_id AND e.series_id = ?)
			WHERE	e.series_id IS NULL
			ORDER BY last_name, first_name',
			array(ROLE_ID_SUB_EDITOR, (int) $pressId, (int) $seriesId)
		);

		while (!$result->EOF) {
			$users[] = $userDao->_returnUserFromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $users;
	}

	/**
	 * Delete all series editors for a specified series in a press.
	 * @param $seriesId int
	 * @param $pressId int
	 */
	function deleteEditorsBySeriesId($seriesId, $pressId = null) {
		if (isset($pressId)) return $this->update(
			'DELETE FROM series_editors WHERE press_id = ? AND series_id = ?',
			array($pressId, $seriesId)
		);
		else return $this->update(
			'DELETE FROM series_editors WHERE series_id = ?',
			(int) $seriesId
		);
	}

	/**
	 * Delete all series editors assignments for a specified press.
	 * @param $pressId int
	 */
	function deleteEditorsByPressId($pressId) {
		return $this->update(
			'DELETE FROM series_editors WHERE press_id = ?',
			(int) $pressId
		);
	}

	/**
	 * Delete all series assignments for the specified user.
	 * @param $userId int
	 * @param $pressId int optional, include assignments only in this press
	 * @param $seriesId int optional, include only this series
	 */
	function deleteEditorsByUserId($userId, $pressId  = null, $seriesId = null) {
		return $this->update(
			'DELETE FROM series_editors WHERE user_id = ?' . (isset($pressId) ? ' AND press_id = ?' : '') . (isset($seriesId) ? ' AND series_id = ?' : ''),
			isset($pressId) && isset($seriesId) ? array((int) $userId, (int) $pressId, (int) $seriesId)
			: (isset($pressId) ? array((int) $userId, (int) $pressId)
			: (isset($seriesId) ? array((int) $userId, (int) $seriesId) : (int) $userId))
		);
	}

	/**
	 * Check if a user is assigned to a specified series.
	 * @param $pressId int
	 * @param $seriesId int
	 * @param $userId int
	 * @return boolean
	 */
	function editorExists($pressId, $seriesId, $userId) {
		$result = $this->retrieve(
			'SELECT COUNT(*) FROM series_editors WHERE press_id = ? AND series_id = ? AND user_id = ?',
			array((int) $pressId, (int) $seriesId, (int) $userId)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		return $returner;
	}
}

?>
