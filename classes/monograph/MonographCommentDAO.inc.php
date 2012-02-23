<?php

/**
 * @file classes/monograph/MonographCommentDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographCommentDAO
 * @ingroup monograph
 * @see MonographComment
 *
 * @brief Operations for retrieving and modifying MonographComment objects.
 */

import('classes.monograph.MonographComment');

class MonographCommentDAO extends DAO {
	/**
	 * Constructor
	 */
	function MonographCommentDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve MonographComments by monograph id
	 * @param $monographId int
	 * @param $commentType int
	 * @return DAOResultFactory
	 */
	function &getMonographComments($monographId, $commentType = null, $assocId = null) {
		if ($commentType == null) {
			$result =& $this->retrieve(
				'SELECT a.* FROM monograph_comments a WHERE monograph_id = ? ORDER BY date_posted', $monographId
			);
		} else {
			if ($assocId == null) {
				$result =& $this->retrieve(
					'SELECT a.* FROM monograph_comments a WHERE monograph_id = ? AND comment_type = ? ORDER BY date_posted',
					array($monographId, $commentType)
				);
			} else {
				$result =& $this->retrieve(
					'SELECT a.* FROM monograph_comments a WHERE monograph_id = ? AND comment_type = ? AND assoc_id = ? ORDER BY date_posted',
					array($monographId, $commentType, $assocId)
				);
			}
		}

		$returner = new DAOResultFactory($result, $this, '_returnMonographCommentFromRow');
		return $returner;
	}

	/**
	 * Retrieve MonographComments by user id
	 * @param $userId int
	 * @return DAOResultFactory
	 */
	function &getByUserId($userId) {
		$result =& $this->retrieve(
			'SELECT a.* FROM monograph_comments a WHERE author_id = ? ORDER BY date_posted', $userId
		);

		$returner = new DAOResultFactory($result, $this, '_returnMonographCommentFromRow');
		return $returner;
	}

	/**
	 * Retrieve MonographComments made my reviewers on a monograph
	 * @param $reviewerId int The user id of the reviewer.
	 * @param $monographId int The monograph Id that was reviewered/commented on.
	 * @param $reviewId int (optional) The review assignment Id the comment pertains to.
	 * @return DAOResultFactory
	 */
	function &getReviewerCommentsByReviewerId($reviewerId, $monographId, $reviewId = null) {
		$params = array($reviewerId, $monographId);
		if (isset($reviewId)) {
			$params[] = $reviewId;
		}
		$result =& $this->retrieve(
			'SELECT a.* FROM monograph_comments a WHERE author_id = ? AND monograph_id = ?' . (isset($reviewId) ? ' AND assoc_id = ?' : '') . ' ORDER BY date_posted DESC',
			$params
		);

		$returner = new DAOResultFactory($result, $this, '_returnMonographCommentFromRow');
		return $returner;
	}

	/**
	 * Retrieve most recent MonographComment
	 * @param $monographId int
	 * @param $commentType int
	 * @return MonographComment
	 */
	function getMostRecentMonographComment($monographId, $commentType = null, $assocId = null) {
		if ($commentType == null) {
			$result =& $this->retrieveLimit(
				'SELECT a.* FROM monograph_comments a WHERE monograph_id = ? ORDER BY date_posted DESC',
				$monographId,
				1
			);
		} else {
			if ($assocId == null) {
				$result =& $this->retrieveLimit(
					'SELECT a.* FROM monograph_comments a WHERE monograph_id = ? AND comment_type = ? ORDER BY date_posted DESC',
					array($monographId, $commentType),
					1
				);
			} else {
				$result =& $this->retrieveLimit(
					'SELECT a.* FROM monograph_comments a WHERE monograph_id = ? AND comment_type = ? AND assoc_id = ? ORDER BY date_posted DESC',
					array($monographId, $commentType, $assocId),
					1
				);
			}
		}

		$returner = null;
		if (isset($result) && $result->RecordCount() != 0) {
			$returner =& $this->_returnMonographCommentFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve Monograph Comment by comment id
	 * @param $commentId int
	 * @return MonographComment object
	 */
	function &getById($commentId) {
		$result =& $this->retrieve(
			'SELECT a.* FROM monograph_comments a WHERE comment_id = ?', $commentId
		);

		$monographComment =& $this->_returnMonographCommentFromRow($result->GetRowAssoc(false));

		$result->Close();
		unset($result);

		return $monographComment;
	}

	/**
	 * Creates and returns a monograph comment object from a row
	 * @param $row array
	 * @return MonographComment object
	 */
	function &_returnMonographCommentFromRow($row) {
		$monographComment = new MonographComment();
		$monographComment->setCommentId($row['comment_id']);
		$monographComment->setCommentType($row['comment_type']);
		$monographComment->setRoleId($row['role_id']);
		$monographComment->setMonographId($row['monograph_id']);
		$monographComment->setAssocId($row['assoc_id']);
		$monographComment->setAuthorId($row['author_id']);
		$monographComment->setCommentTitle($row['comment_title']);
		$monographComment->setComments($row['comments']);
		$monographComment->setDatePosted($this->datetimeFromDB($row['date_posted']));
		$monographComment->setDateModified($this->datetimeFromDB($row['date_modified']));
		$monographComment->setViewable($row['viewable']);

		HookRegistry::call('MonographCommentDAO::_returnMonographCommentFromRow', array(&$monographComment, &$row));

		return $monographComment;
	}

	/**
	 * inserts a new monograph comment into monograph_comments table
	 * @param MonographNote object
	 * @return Monograph Note Id int
	 */
	function insertMonographComment(&$monographComment) {
		$this->update(
			sprintf('INSERT INTO monograph_comments
				(comment_type, role_id, monograph_id, assoc_id, author_id, date_posted, date_modified, comment_title, comments, viewable)
				VALUES
				(?, ?, ?, ?, ?, %s, %s, ?, ?, ?)',
				$this->datetimeToDB($monographComment->getDatePosted()), $this->datetimeToDB($monographComment->getDateModified())),
			array(
				$monographComment->getCommentType(),
				$monographComment->getRoleId(),
				$monographComment->getMonographId(),
				$monographComment->getAssocId(),
				$monographComment->getAuthorId(),
				$monographComment->getCommentTitle(),
				$monographComment->getComments(),
				$monographComment->getViewable() === null ? 0 : $monographComment->getViewable()
			)
		);

		$monographComment->setCommentId($this->getInsertMonographCommentId());
		return $monographComment->getCommentId();
	}

	/**
	 * Get the ID of the last inserted monograph comment.
	 * @return int
	 */
	function getInsertMonographCommentId() {
		return $this->getInsertId('monograph_comments', 'comment_id');
	}

	/**
	 * removes a monograph comment from monograph_comments table
	 * @param MonographComment object
	 */
	function deleteMonographComment($monographComment) {
		$this->deleteMonographCommentById($monographComment->getCommentId());
	}

	/**
	 * removes a monograph note by id
	 * @param noteId int
	 */
	function deleteMonographCommentById($commentId) {
		$this->update(
			'DELETE FROM monograph_comments WHERE comment_id = ?', $commentId
		);
	}

	/**
	 * Delete all comments for a monograph.
	 * @param $monographId int
	 */
	function deleteMonographComments($monographId) {
		return $this->update(
			'DELETE FROM monograph_comments WHERE monograph_id = ?', $monographId
		);
	}

	/**
	 * updates a monograph comment
	 * @param MonographComment object
	 */
	function updateObject($monographComment) {
		$this->update(
			sprintf('UPDATE monograph_comments
				SET
					comment_type = ?,
					role_id = ?,
					monograph_id = ?,
					assoc_id = ?,
					author_id = ?,
					date_posted = %s,
					date_modified = %s,
					comment_title = ?,
					comments = ?,
					viewable = ?
				WHERE comment_id = ?',
				$this->datetimeToDB($monographComment->getDatePosted()), $this->datetimeToDB($monographComment->getDateModified())),
			array(
				$monographComment->getCommentType(),
				$monographComment->getRoleId(),
				$monographComment->getMonographId(),
				$monographComment->getAssocId(),
				$monographComment->getAuthorId(),
				$monographComment->getCommentTitle(),
				$monographComment->getComments(),
				$monographComment->getViewable() === null ? 1 : $monographComment->getViewable(),
				$monographComment->getCommentId()
			)
		);
	}
}

?>
