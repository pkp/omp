<?php

/**
 * @file classes/monograph/MonographDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying Monograph objects.
 */

// $Id$


import ('monograph.Monograph');

define('MONOGRAPH_STATUS_UPCOMING', 0x00000001);
define('MONOGRAPH_STATUS_PUBLISHED', 0x00000002);

define('ARRANGEMENT_UNASSIGNED', 0);

class MonographDAO extends DAO {
	/**
	 * Retrieve Monograph by monograph id
	 * @param $monographId int
	 * @return Monograph object
	 */
	function &getMonograph($monographId, $pressId = null) {

		$sql = 'SELECT m.*
			FROM monographs m
			WHERE m.monograph_id = ?';
		$sqlParams[] = $monographId;

		if (isset($pressId)) {
			$sql .= ' AND m.press_id = ?';
			$sqlParams[] = $pressId;
		}

		$result =& $this->retrieve($sql, $sqlParams);

		$monograph = null;
		if ($result->RecordCount() != 0) {
			$monograph =& $this->_fromRow($result->GetRowAssoc(false));

		}


		$result->Close();
		unset($result);

		return $monograph;
	}

	/**
	 * Retrieve Monograph by public monograph id
	 * @param $publicMonographId string
	 * @return Monograph object
	 */
	function &getMonographByPublicMonographId($publicMonographId, $pressId = null) {
		if (isset($pressId)) {
			$result =& $this->retrieve(
				'SELECT m.* FROM monographs m WHERE public_monograph_id = ? AND press_id = ?',
				array($publicMonographId, $pressId)
			);
		} else {
			$result =& $this->retrieve(
				'SELECT m.* FROM monographs m WHERE public_monograph_id = ?', $publicMonographId
			);
		}

		$monograph = null;
		if ($result->RecordCount() != 0) {
			$monograph =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $monograph;
	}

	/**
	 * Get all monographs for a user.
	 * @param $userId int
	 * @param $pressId int optional
	 * @return array Monographs
	 */
	function &getByUserId($userId, $pressId = null) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$params = array(
			'title',
			$primaryLocale,
			'title',
			$locale,
			'abbrev',
			$primaryLocale,
			'abbrev',
			$locale,
			$userId
		);
		if ($pressId) $params[] = $pressId;
		$monographs = array();

		$result =& $this->retrieve(
			'SELECT	m.*,
				COALESCE(atl.setting_value, atpl.setting_value) AS arrangement_title,
				COALESCE(aal.setting_value, aapl.setting_value) AS arrangement_abbrev
			FROM	monographs m
				LEFT JOIN acquisitions_arrangements aa ON (aa.arrangement_id = m.arrangement_id)
				LEFT JOIN acquisitions_arrangements_settings atpl ON (aa.arrangement_id = atpl.arrangement_id AND atpl.setting_name = ? AND atpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings atl ON (aa.arrangement_id = atl.arrangement_id AND atl.setting_name = ? AND atl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings aapl ON (aa.arrangement_id = aapl.arrangement_id AND aapl.setting_name = ? AND aapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings aal ON (aa.arrangement_id = aal.arrangement_id AND aal.setting_name = ? AND aal.locale = ?)
			WHERE	m.user_id = ?' .
			(isset($pressId)?' AND m.journal_id = ?':''),
			$params
		);

		while (!$result->EOF) {
			$monographs[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $monographs;
	}

	/**
	 * Retrieve Monograph by "best" monograph id -- public ID if it exists,
	 * falling back on the internal monograph ID otherwise.
	 * @param $monographId string
	 * @return Monograph object
	 */
	function &getMonographByBestMonographId($monographId, $pressId = null) {
		$monograph =& $this->getMonographByPublicMonographId($monographId, $pressId);
		if (!isset($monograph)) $monograph =& $this->getMonographById((int) $monographId, $pressId);
		return $monograph;
	}

	/**
	 * Retrieve the last created monograph
	 * @param $pressId int
	 * @return Monograph object
	 */
	function &getLastCreatedMonograph($pressId) {
		$result =& $this->retrieveLimit(
			'SELECT m.* FROM monographs m WHERE press_id = ? ORDER BY monograph_id DESC', $pressId, 1
		);

		$monograph = null;
		if ($result->RecordCount() != 0) {
			$monograph =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $monograph;
	}

	/**
	 * Retrieve upcoming monograph
	 * @param $pressId int
	 * @param $rangeInfo result ranges
	 * @return Monograph object 
	 */
	function &getUpcomingMonographs($pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT m.* FROM monographs m WHERE press_id = ? AND status = ?', array($pressId, MONOGRAPH_STATUS_UPCOMING), $rangeInfo
		);

		$returner =& new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}	

	/**
	 * Get a list of fields for which localized data is supported
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'abstract', 'sponsor', 'discipline', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type');
	}

	/**
	 * Get the ID of the press a monograph is associated with.
	 * @param $monographId int
	 * @return int
	 */
	function getMonographPressId($monographId) {
		$result =& $this->retrieve(
			'SELECT press_id FROM monographs WHERE monograph_id = ?', $monographId
		);
		$returner = isset($result->fields[0]) ? $result->fields[0] : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Update the localized fields for this object.
	 * @param $monograph
	 */
	function updateLocaleFields(&$monograph) {
		$this->updateDataObjectSettings('monograph_settings', $monograph, array(
			'monograph_id' => $monograph->getMonographId()
		));
	}

	/**
	 * inserts a new monograph into monographs table
	 * @param Monograph object
	 * @return Monograph Id int
	 */
	function insertMonograph(&$monograph) {

		$monograph->stampModified();
		$this->update(
			sprintf('INSERT INTO monographs
				(user_id, press_id, language, comments_to_ed, date_submitted, date_status_modified, last_modified, status, submission_progress, submission_file_id, revised_file_id, review_file_id, editor_file_id, pages, fast_tracked, hide_author, comments_status, edited_volume, arrangement_id, current_review_type, current_round)
				VALUES
				(?, ?, ?, ?, %s, %s, %s, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($monograph->getDateSubmitted()), $this->datetimeToDB($monograph->getDateStatusModified()), $this->datetimeToDB($monograph->getLastModified())),
			array(
				$monograph->getUserId(),
				$monograph->getPressId(),
				$monograph->getLanguage(),
				$monograph->getCommentsToEditor(),
				$monograph->getStatus() === null ? 1 : $monograph->getStatus(),
				$monograph->getSubmissionProgress() === null ? 1 : $monograph->getSubmissionProgress(),
				$monograph->getSubmissionFileId(),
				$monograph->getRevisedFileId(),
				$monograph->getReviewFileId(),
				$monograph->getEditorFileId(),
				$monograph->getPages(),
				$monograph->getFastTracked() ? 1 : 0,
				$monograph->getHideAuthor() === null ? 0 : $monograph->getHideAuthor(),
				$monograph->getCommentsStatus() === null ? 0 : $monograph->getCommentsStatus(),
				$monograph->getWorkType(),
				$monograph->getArrangementId() ,
				$monograph->getCurrentReviewType() === null ? 6 : $monograph->getCurrentReviewType(),				
				$monograph->getCurrentRound() === null ? 1 : $monograph->getCurrentRound()
			)
		);

		$monograph->setMonographId($this->getInsertMonographId());
		$this->updateLocaleFields($monograph);

		// Insert authors and monograph components for this monograph
		$this->_updateMonographPeripherals($monograph);

		return $monograph->getMonographId();

	}

	/**
	 * Get the ID of the last inserted monograph.
	 * @return int
	 */
	function getInsertMonographId() {
		return $this->getInsertId('monographs', 'monograph_id');
	}

	/**
	 * Check if the monograph is already in the database
	 * @param $pressId int
	 * @param $monographId int
	 * @return boolean
	 */
	function monographExists($pressId, $monographId) {
		$result =& $this->retrieve(
			'SELECT m.* FROM monographs m WHERE press_id = ? AND monograph_id <> ?', 
			array($pressId, $monographId)
		);
		$returner = $result->RecordCount() != 0 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}


	/**
	 * updates a monograph
	 * @param Monograph object
	 */
	function updateMonograph($monograph) {

		$this->update(
			sprintf('UPDATE monographs
				SET
					user_id = ?,
					language = ?,
					comments_to_ed = ?,
					date_submitted = %s,
					date_status_modified = %s,
					last_modified = %s,
					status = ?,
					press_id = ?,
					submission_progress = ?,
					edited_volume = ?,
					submission_file_id = ?,
					revised_file_id = ?,
					review_file_id = ?,
					editor_file_id = ?,
					hide_author = ?,
					arrangement_id = ?,
					current_review_type = ?,
					current_round = ?
				WHERE monograph_id = ?',
				$this->datetimeToDB($monograph->getDateSubmitted()), $this->datetimeToDB($monograph->getDateStatusModified()), $this->datetimeToDB($monograph->getLastModified())),
			array(
				$monograph->getUserId(),
				$monograph->getLanguage(),
				$monograph->getCommentsToEditor(),
				$monograph->getStatus(),
				$monograph->getPressId(),
				$monograph->getSubmissionProgress(),
				$monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME ? 1 : 0,
				$monograph->getSubmissionFileId(),
				$monograph->getRevisedFileId(),
				$monograph->getReviewFileId(),
				$monograph->getEditorFileId(),
				$monograph->getHideAuthor() == null ? 0 : $monograph->getHideAuthor(),
				$monograph->getArrangementId(),
				$monograph->getCurrentReviewType(),
				$monograph->getCurrentRound(),
				$monograph->getMonographId()
			)
		);
		$this->updateLocaleFields($monograph);
		
		$this->_updateMonographPeripherals($monograph);
	}

	/**
	 * Delete monograph by id.
	 * @param $monograph object Monograph
	 */
	function deleteMonographById($monographId) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph((int) $monographId);
		MonographDAO::deleteMonograph($monograph);
	}

	/**
	 * Delete monograph. Deletes associated published monographs and cover file.
	 * @param $monograph object Monograph
	 */
	function deleteMonograph(&$monograph) {
		import('file.PublicFileManager');
		$publicFileManager =& new PublicFileManager();
		
		if (is_array($monograph->getFileName(null))) foreach ($monograph->getFileName(null) as $fileName) {
			if ($fileName != '') {
				$publicFileManager->removePressFile($monograph->getPressId(), $fileName);
			}
		}
	/*	if (($fileName = $monograph->getStyleFileName()) != '') {
			$publicFileManager->removePressFile($monograph->getPressId(), $fileName);
		}*/

		$this->update('DELETE FROM monograph_settings WHERE monograph_id = ?', $monograph->getMonographId());
		$this->update('DELETE FROM monographs WHERE monograph_id = ?', $monograph->getMonographId());
	}

	/**
	 * Delete monographs by press id. Deletes dependent entities.
	 * @param $pressId int
	 */
	function deleteMonographsByPress($pressId) {
		$monographs =& $this->getMonographs($pressId);
		while (($monograph =& $monographs->next())) {
			$this->deleteMonograph($monograph);
			unset($monograph);
		}
	}

	/**
	 * Checks if monograph exists
	 * @param $publicMonographId string
	 * @return boolean
	 */
	function monographIdExists($monographId, $pressId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM monographs WHERE monograph_id = ? AND press_id = ?',
			array($monographId, $pressId)
		);
		return $result->fields[0] ? true : false;
	}

	/**
	 * Checks if public identifier exists
	 * @param $publicMonographId string
	 * @return boolean
	 */
	function publicMonographIdExists($publicMonographId, $monographId, $pressId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM monographs WHERE public_monograph_id = ? AND monograph_id <> ? AND press_id = ?', array($publicMonographId, $monographId, $pressId)
		);
		$returner = $result->fields[0] ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get all monographs organized by published date
	 * @param $pressId int
	 * @param $rangeInfo object DBResultRange (optional)
	 * @return monographs object ItemIterator
	 */
	function &getMonographs($pressId, $rangeInfo = null) {

		$sql = 'SELECT m.* FROM monographs m WHERE press_id = ? ORDER BY date_submitted DESC';
		$result =& $this->retrieveRange($sql, $pressId, $rangeInfo);

		$returner =& new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * return current round for each review type
	 * @param $row array
	 * @return array ($returned[review_type]=current_round_value)
	 */
	function &getReviewRoundsInfoById($monographId) {
		$returner = array();

		$result =& $this->retrieve(
				'SELECT MAX(round) AS current_round, review_type, review_revision 
				FROM review_rounds r 
				WHERE monograph_id = ? 
				GROUP BY review_type, r.review_revision', 
				$monographId
			);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[$row['review_type']] = $row['current_round'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * creates and returns a monograph object from a row
	 * @param $row array
	 * @return Monograph object
	 */
	function &_fromRow(&$row) {
		$monograph =& new Monograph();
		$this->_monographFromRow($monograph, $row);
		return $monograph;
	}
	function _monographFromRow(&$monograph, &$row) {

		$authorDao =& DAORegistry::getDAO('AuthorDAO');

		$monograph->setMonographId($row['monograph_id']);
		$monograph->setPressId($row['press_id']);
		$monograph->setUserId($row['user_id']);
		$monograph->setArrangementId($row['arrangement_id']);
		$monograph->setSubmissionProgress($row['submission_progress']);
		$monograph->setStatus($row['status']);
		$monograph->setCommentsToEditor($row['comments_to_ed']);
		$monograph->setDateSubmitted($row['date_submitted']);
		$monograph->setLanguage($row['language']);
		$monograph->setSubmissionFileId($row['submission_file_id']);
		$monograph->setRevisedFileId($row['revised_file_id']);
		$monograph->setReviewFileId($row['review_file_id']);
		$monograph->setEditorFileId($row['editor_file_id']);
		$monograph->setStatus($row['status']);
		$monograph->setDateStatusModified($this->datetimeFromDB($row['date_status_modified']));
		$monograph->setCurrentReviewType($row['current_review_type']);
		$monograph->setCurrentRound($row['current_round']);

		$monograph->setWorkType($row['edited_volume']);
		$monograph->setLastModified($this->datetimeFromDB($row['last_modified']));

		if (isset($row['arrangement_abbrev']))
			$monograph->setArrangementAbbrev($row['arrangement_abbrev']);
		if (isset($row['arrangement_title']))
			$monograph->setArrangementTitle($row['arrangement_title']);

		$this->getDataObjectSettings('monograph_settings', 'monograph_id', $row['monograph_id'], $monograph);
		$monograph->setAuthors($authorDao->getAuthorsByMonographId($row['monograph_id']));

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$authors =& $authorDao->getAuthorsByMonographId($monograph->getMonographId());
		$monograph->setAuthors($authors);
		
		$monographComponentDao =& DAORegistry::getDAO('MonographComponentDAO');
		$monographComponents =& $monographComponentDao->getMonographComponents($monograph->getMonographId());
		$monograph->setMonographComponents($monographComponents);

		// set review rounds info
		$reviewRoundsInfo = $this->getReviewRoundsInfoById($row['monograph_id']);
		if ( empty($reviewRoundsInfo) ) $reviewRoundsInfo[$row['current_review_type']] = $row['current_round'];
		$monograph->setReviewRoundsInfo($reviewRoundsInfo);

		HookRegistry::call('MonographDAO::_fromRow', array(&$monograph, &$row));

	}
	/**
	 * Update/insert monograph component and author related data.
	 * @param $monograph Monograph
	 */
	function _updateMonographPeripherals(&$monograph) {
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$authors =& $monograph->getAuthors();
		$oldAuthors =& $authorDao->getAuthorIdsByMonographId($monograph->getMonographId());

		$count = max(array(count($authors),count($oldAuthors)));

		//FIXME: this is not pretty.
		for ($i=0; $i < $count; $i++) {
			if (isset($authors[$i]) && isset($oldAuthors[$i])) {
				$gnash[$authors[$i]->getId()] = $oldAuthors[$i];
				$authors[$i]->setId($oldAuthors[$i]);
				$authorDao->updateAuthor($authors[$i]);
			} else if (!isset($authors[$i]) && isset($oldAuthors[$i])) {
				$authorDao->deleteAuthorById($oldAuthors[$i], $monograph->getMonographId());
			} else if (isset($authors[$i]) && !isset($oldAuthors[$i])) {
				$authors[$i]->setMonographId($monograph->getMonographId());
				$contribution = $authors[$i]->getContributionType();
				if (!isset($contribution)) $authors[$i]->setContributionType(AUTHOR);
				$gnash[$authors[$i]->getId()] = $authorDao->insertAuthor($authors[$i]);
			}
		}

		$monographComponentDao =& DAORegistry::getDAO('MonographComponentDAO');
		$monographComponents =& $monograph->getMonographComponents();
		$oldMonographComponents =& $monographComponentDao->getMonographComponentIdsByMonographId($monograph->getMonographId());

		$count = max(array(count($monographComponents),count($oldMonographComponents)));

		for ($i=0; $i < $count; $i++) {

			if (isset($monographComponents[$i]) && isset($oldMonographComponents[$i])) {

				foreach ($monographComponents[$i]->getMonographComponentAuthors() as $ca) {
					if (isset($gnash[$ca->getId()])) {
						$ca->setId($gnash[$ca->getId()]);
					}
				}
				$monographComponents[$i]->setPrimaryContact($gnash[$monographComponents[$i]->getPrimaryContact()]);
				$monographComponents[$i]->setMonographComponentId($oldMonographComponents[$i]);
				$monographComponentDao->updateMonographComponent($monographComponents[$i]);

			} else if (!isset($monographComponents[$i]) && isset($oldMonographComponents[$i])) {

				$monographComponentDao->deleteMonographComponentById($oldMonographComponents[$i]);

			} else if (isset($monographComponents[$i]) && !isset($oldMonographComponents[$i])) {

				foreach ($monographComponents[$i]->getMonographComponentAuthors() as $chau) {
					$chau->setId($gnash[$chau->getId()]);
				}
				$monographComponents[$i]->setPrimaryContact($gnash[$monographComponents[$i]->getPrimaryContact()]);
				$monographComponentDao->insertMonographComponent($monographComponents[$i]);
			}
		}
	}

	/**
	 * Remove all monographs from an acquisitions arrangement.
	 * @param $arrangementId int
	 */
	function removeMonographsFromAcquisitionsArrangement($arrangementId) {
		return $this->update(
				'UPDATE monographs
				SET arrangement_id = ?
				WHERE arrangement_id = ?',
				array(ARRANGEMENT_UNASSIGNED, $arrangementId)
			);
	}
}

?>