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

class MonographDAO extends DAO {
	/**
	 * Retrieve Monograph by monograph id
	 * @param $monographId int
	 * @return Monograph object
	 */
	function &getMonograph($monographId, $pressId = null) {
		if (isset($pressId)) {
			$result =& $this->retrieve(
				'SELECT m.* FROM monographs m WHERE monograph_id = ? AND press_id = ?',
				array($monographId, $pressId)
			);
		} else {
			$result =& $this->retrieve(
				'SELECT m.* FROM monographs m WHERE monograph_id = ?', $monographId
			);
		}

		$monograph = null;
		if ($result->RecordCount() != 0) {
			$monograph =& $this->_returnMonographFromRow($result->GetRowAssoc(false));
			$authorDao =& DAORegistry::getDAO('AuthorDAO');
			$authors =& $authorDao->getAuthorsByMonographId($monograph->getMonographId());
			$monograph->setAuthors($authors);
			$monographComponentDao =& DAORegistry::getDAO('MonographComponentDAO');
			$monographComponents =& $monographComponentDao->getMonographComponents($monograph->getMonographId());
			$monograph->setMonographComponents($monographComponents);
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
			$monograph =& $this->_returnMonographFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $monograph;
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
			$monograph =& $this->_returnMonographFromRow($result->GetRowAssoc(false));
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

		$returner =& new DAOResultFactory($result, $this, '_returnMonographFromRow');
		return $returner;
	}	

	/**
	 * Get a list of fields for which localized data is supported
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'abstract', 'sponsor', 'discipline', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample');
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
				(user_id, press_id, language, comments_to_ed, date_submitted, date_status_modified, last_modified, status, submission_progress, current_round, submission_file_id, revised_file_id, review_file_id, editor_file_id, copyedit_file_id, pages, fast_tracked, hide_author, comments_status, edited_volume)
				VALUES
				(?, ?, ?, ?, %s, %s, %s, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($monograph->getDateSubmitted()), $this->datetimeToDB($monograph->getDateStatusModified()), $this->datetimeToDB($monograph->getLastModified())),
			array(
				$monograph->getUserId(),
				$monograph->getPressId(),
				$monograph->getLanguage(),
				$monograph->getCommentsToEditor(),
				$monograph->getStatus() === null ? 1 : $monograph->getStatus(),
				$monograph->getSubmissionProgress() === null ? 1 : $monograph->getSubmissionProgress(),
				$monograph->getCurrentRound() === null ? 1 : $monograph->getCurrentRound(),
				$monograph->getSubmissionFileId(),
				$monograph->getRevisedFileId(),
				$monograph->getReviewFileId(),
				$monograph->getEditorFileId(),
				$monograph->getCopyeditFileId(),
				$monograph->getPages(),
				$monograph->getFastTracked() ? 1 : 0,
				$monograph->getHideAuthor() === null ? 0 : $monograph->getHideAuthor(),
				$monograph->getCommentsStatus() === null ? 0 : $monograph->getCommentsStatus(),
				$monograph->getWorkType()
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
			'UPDATE monographs
				SET
					date_submitted = ?,
					press_id = ?,
					submission_progress = ?,
					edited_volume = ?,
					comments_to_ed = ?,
					language = ?,
					submission_file_id = ?
				WHERE monograph_id = ?',
			array(
				$monograph->getDateSubmitted(),
				$monograph->getPressId(),
				$monograph->getSubmissionProgress(),
				$monograph->getWorkType() == EDITED_VOLUME ? 1 : 0,
				$monograph->getCommentsToEditor(),
				$monograph->getLanguage(),
				$monograph->getSubmissionFileId(),
				$monograph->getMonographId()
			)
		);
		$this->updateLocaleFields($monograph);
		
		$this->_updateMonographPeripherals($monograph);
	}

	/**
	 * Delete monograph. Deletes associated published monographs and cover file.
	 * @param $monograph object Monograph
	 */
	function deleteMonograph(&$monograph) {
		import('file.PublicFileManager');
		$publicFileManager =& new PublicFileManager();
		
/*		if (is_array($monograph->getFileName(null))) foreach ($monograph->getFileName(null) as $fileName) {
			if ($fileName != '') {
				$publicFileManager->removePressFile($monograph->getPressId(), $fileName);
			}
		}
		if (($fileName = $monograph->getStyleFileName()) != '') {
			$publicFileManager->removePressFile($monograph->getPressId(), $fileName);
		}
*/

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

		$sql = 'SELECT m.* FROM monographs m WHERE press_id = ? ORDER BY date_published DESC';
		$result =& $this->retrieveRange($sql, $pressId, $rangeInfo);

		$returner =& new DAOResultFactory($result, $this, '_returnMonographFromRow');
		return $returner;
	}

	/**
	 * Get published monographs organized by published date
	 * @param $pressId int
	 * @param $rangeInfo object DBResultRange
	 * @return monographs ItemIterator
	 */
	function &getPublishedMonographs($pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT m.* FROM monographs m WHERE m.press_id = ? AND m.status = '.MONOGRAPH_STATUS_PUBLISHED.' ORDER BY m.date_published DESC',
			$pressId, $rangeInfo
		);

		$returner =& new DAOResultFactory($result, $this, '_returnMonographFromRow');
		return $returner;
	}
	/**
	 * creates and returns a monograph object from a row
	 * @param $row array
	 * @return Monograph object
	 */
	function &_returnMonographFromRow(&$row) {
		$monograph =& new Monograph();
		$this->_monographFromRow($monograph, $row);
		return $monograph;
	}
	function _monographFromRow(&$monograph, &$row) {
		$monograph->setMonographId($row['monograph_id']);
		$monograph->setPressId($row['press_id']);
		$monograph->setUserId($row['user_id']);
		$monograph->setSubmissionProgress($row['submission_progress']);
		$monograph->setStatus($row['status']);
		$monograph->setCommentsToEditor($row['comments_to_ed']);
		$monograph->setDateSubmitted($row['date_submitted']);
		$monograph->setLanguage($row['language']);
		$monograph->setSubmissionFileId($row['submission_file_id']);
    //$monograph->setDatePublished($this->datetimeFromDB($row['date_published']));
//		//$monograph->setPublicMonographId($row['public_monograph_id']);
		$monograph->setWorkType($row['edited_volume']);
		$this->getDataObjectSettings('monograph_settings', 'monograph_id', $row['monograph_id'], $monograph);
//		$article->setAuthors($this->authorDao->getAuthorsByArticle($row['article_id']));
		HookRegistry::call('MonographDAO::_returnMonographFromRow', array(&$monograph, &$row));

	}
	/**
	 * creates and returns a monograph object from a row
	 * @param $row array
	 * @return Monograph object
	 */
	function _updateMonographPeripherals(&$monograph) {
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$authors =& $monograph->getAuthors();
		$oldAuthors =& $authorDao->getAuthorIdsByMonographId($monograph->getMonographId());

		$count = max(array(count($authors),count($oldAuthors)));

		for ($i=0; $i < $count; $i++) {
			if (isset($authors[$i]) && isset($oldAuthors[$i])) {
				$gnash[$authors[$i]->getAuthorId()] = $oldAuthors[$i];
				$authors[$i]->setAuthorId($oldAuthors[$i]);
				$authorDao->updateAuthor($authors[$i]);
			} else if (!isset($authors[$i]) && isset($oldAuthors[$i])) {
				$authorDao->deleteAuthorById($oldAuthors[$i], $monograph->getMonographId());
			} else if (isset($authors[$i]) && !isset($oldAuthors[$i])) {
				$gnash[$authors[$i]->getAuthorId()] = $authorDao->insertAuthor($authors[$i]);
			}
		}

		$monographComponentDao =& DAORegistry::getDAO('MonographComponentDAO');
		$monographComponents =& $monograph->getMonographComponents();
		$oldMonographComponents =& $monographComponentDao->getMonographComponentIdsByMonographId($monograph->getMonographId());

		$count = max(array(count($monographComponents),count($oldMonographComponents)));

		for ($i=0; $i < $count; $i++) {

			if (isset($monographComponents[$i]) && isset($oldMonographComponents[$i])) {

				foreach ($monographComponents[$i]->getMonographComponentAuthors() as $ca) {
					if (isset($gnash[$ca->getAuthorId()])) {
						$ca->setAuthorId($gnash[$ca->getAuthorId()]);
					}
					//primary contact settings
				}
				$monographComponents[$i]->setMonographComponentId($oldMonographComponents[$i]);
				$monographComponentDao->updateMonographComponent($monographComponents[$i]);
			} else if (!isset($monographComponents[$i]) && isset($oldMonographComponents[$i])) {

				$monographComponentDao->deleteMonographComponentById($oldMonographComponents[$i]);
			} else if (isset($monographComponents[$i]) && !isset($oldMonographComponents[$i])) {

				foreach ($monographComponents[$i]->getMonographComponentAuthors() as $chau) {
					$chau->setAuthorId($gnash[$chau->getAuthorId()]);
				}
				$monographComponentDao->insertMonographComponent($monographComponents[$i]);
			}
		}
	}

}

?>
