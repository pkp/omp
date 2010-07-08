<?php

/**
 * @file classes/views/ViewsDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ViewsDAO
 * @ingroup views
 *
 * @brief Class for keeping track of item views.
 */

import('classes.views.ViewsDAO');

class ViewsDAO extends DAO {
	/*
	 * Constructor
	 */
	function ViewsDAO() {
		parent::DAO();
	}

	/*
	 * Mark an item as viewed
	 * @param $assocType int The associated type for the item being marked
	 * @param $assocId int the ID of the object being marked
	 * @param $userId int the ID of the user viewing the item
	 * @return bool
	 */
	function recordView($assocType, $assocId, $userId) {
		if ( !$this->getLastViewDate($assocType, $assocId, $userId) ) {
			return $this->update(sprintf('INSERT INTO views (assoc_type, assoc_id, user_id, date_last_viewed)
										VALUES (?, ?, ?, %s)',
										$this->datetimeToDB(Core::getCurrentDate())),
									array($assocType, $assocId, $userId));
		} else {
			return $this->update(sprintf('UPDATE views set date_last_viewed = %s
										WHERE assoc_type = ? AND assoc_id = ? and user_id = ?',
										$this->datetimeToDB(Core::getCurrentDate())),
									array($assocType, $assocId, $userId));
		}
	}

	/**
	 * Get the timestamp of the last view
	 * @param int $assocType
	 * @param int $assocId
	 * @param int $userId
	 * @return string datetime of last view.  False if no view found.
	 */
	function getLastViewDate($assocType, $assocId, $userId) {
		$result = $this->retrieve('SELECT date_last_viewed
									FROM views
									WHERE assoc_type = ?
										AND assoc_id = ?
										AND user_id = ?',
									array($assocType, $assocId, $userId));
		return (isset($result->fields[0])) ? $result->fields[0] : false;
	}

}

?>
