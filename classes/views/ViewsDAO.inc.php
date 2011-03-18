<?php

/**
 * @file classes/views/ViewsDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ViewsDAO
 * @ingroup views
 *
 * @brief Class for keeping track of item views.
 */

import('classes.views.ViewsDAO');

class ViewsDAO extends DAO {
	/**
	 * Constructor
	 */
	function ViewsDAO() {
		parent::DAO();
	}

	/**
	 * Mark an item as viewed.
	 * @param $assocType integer The associated type for the item being marked.
	 * @param $assocId string The id of the object being marked.
	 * @param $userId integer The id of the user viewing the item.
	 * @return boolean
	 */
	function recordView($assocType, $assocId, $userId) {
		if ($this->getLastViewDate($assocType, $assocId, $userId)) {
			$sql =
				'UPDATE	views set date_last_viewed = %s
				WHERE 	assoc_type = ? AND assoc_id = ? and user_id = ?';
		} else {
			$sql =
				'INSERT INTO	views (assoc_type, assoc_id, user_id, date_last_viewed)
				VALUES	(?, ?, ?, %s)';
		}
		return $this->update(
			sprintf($sql, $this->datetimeToDB(Core::getCurrentDate())),
			array((int)$assocType, $assocId, (int)$userId)
		);
	}

	/**
	 * Get the timestamp of the last view.
	 * @param $assocType integer
	 * @param $assocId string
	 * @param $userId integer
	 * @return string|boolean Datetime of last view. False if no view found.
	 */
	function getLastViewDate($assocType, $assocId, $userId) {
		$result = $this->retrieve(
			'SELECT	date_last_viewed
			FROM	views
			WHERE	assoc_type = ?
				AND	assoc_id = ?
				AND	user_id = ?',
			array((int)$assocType, $assocId, (int)$userId)
		);
		return (isset($result->fields[0])) ? $result->fields[0] : false;
	}

	/**
	 * Move views from one assoc object to another.
	 * @param $assocType integer One of the ASSOC_TYPE_* constants.
	 * @param $oldAssocId string
	 * @param $newAssocId string
	 */
	function moveViews($assocType, $oldAssocId, $newAssocId) {
		return $this->update(
			'UPDATE views SET assoc_id = ? WHERE assoc_type = ? AND assoc_id = ?',
			array($newAssocId, (int)$assocType, $oldAssocId)
		);
	}

	/**
	 * Delete views of an assoc object.
	 * @param $assocType integer One of the ASSOC_TYPE_* constants.
	 * @param $assocId string
	 */
	function deleteViews($assocType, $assocId) {
		return $this->update(
			'DELETE FROM views WHERE assoc_type = ? AND assoc_id = ?',
			array((int)$assocType, $assocId)
		);
	}
}

?>
