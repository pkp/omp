<?php

/**
 * @file classes/monograph/ChapterDAO.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterDAO
 * @ingroup monograph
 * @see Chapter
 * @see AuthorDAO
 *
 * @brief Operations for retrieving and modifying Chapter objects.
 */

import('classes.monograph.Chapter');
import('classes.monograph.ChapterAuthor');

class ChapterDAO extends DAO implements PKPPubIdPluginDAO {
	/**
	 * Retrieve a chapter by ID.
	 * @param $chapterId int
	 * @param $assocType int optional
	 * @param $monographId int optional
	 * @return Chapter
	 */
	function getChapter($chapterId, $monographId = null) {
		$params = array((int) $chapterId);
		if ($monographId !== null) {
			$params[] = (int) $monographId;
		}

		$result = $this->retrieve(
			'SELECT * FROM submission_chapters WHERE chapter_id = ?' . ($monographId !== null?' AND submission_id = ? ':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_returnFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Get all chapters for a given monograph.
	 * @param $monographId int
	 * @param $rangeInfo object RangeInfo object (optional)
	 * @return DAOResultFactory
	 */
	function getChapters($monographId, $rangeInfo = null) {
		$result = $this->retrieveRange(
			'SELECT chapter_id, submission_id, seq FROM submission_chapters WHERE submission_id = ? ORDER BY seq',
			(int) $monographId,
			$rangeInfo
		);

		return new DAOResultFactory($result, $this, '_returnFromRow', array('id'));
	}

	/**
	 * Retrieve all chapters of a press.
	 * @param $pressId int
	 * @return DAOResultFactory
	 */
	function getByContextId($pressId) {
		$result = $this->retrieve(
			'SELECT	sc.*
			FROM submission_chapters sc
				INNER JOIN submissions s ON (sc.submission_id = s.submission_id)
			WHERE s.context_id = ?',
			(int) $pressId
		);

		return new DAOResultFactory($result, $this, '_returnFromRow');
	}

	/**
	 * Get the list of fields for which locale data is stored.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'subtitle');
	}

	/**
	 * Get a list of additional fields that do not have
	 * dedicated accessors.
	 * @return array
	 */
	function getAdditionalFieldNames() {
		$additionalFields = parent::getAdditionalFieldNames();
		// FIXME: Move this to a PID plug-in.
		$additionalFields[] = 'pub-id::publisher-id';
		return $additionalFields;
	}

	/**
	 * Get a new data object
	 * @return Chapter
	 */
	function newDataObject() {
		return new Chapter();
	}

	/**
	 * Internal function to return a Chapter object from a row.
	 * @param $row array
	 * @return Chapter
	 */
	function _returnFromRow($row) {
		$chapter = $this->newDataObject();
		$chapter->setId($row['chapter_id']);
		$chapter->setMonographId($row['submission_id']);
		$chapter->setSequence($row['seq']);
		$this->getDataObjectSettings('submission_chapter_settings', 'chapter_id', $row['chapter_id'], $chapter);

		HookRegistry::call('ChapterDAO::_returnFromRow', array(&$chapter, &$row));

		return $chapter;
	}

	/**
	 * Update the settings for this object
	 * @param $chapter object
	 */
	function updateLocaleFields($chapter) {
		$this->updateDataObjectSettings('submission_chapter_settings', $chapter, array(
			'chapter_id' => $chapter->getId()
		));
	}

	/**
	 * Insert a new board chapter.
	 * @param $chapter Chapter
	 */
	function insertChapter($chapter) {
		$this->update(
			'INSERT INTO submission_chapters
				(submission_id, seq)
				VALUES
				(?, ?)',
			array(
				(int) $chapter->getMonographId(),
				(int) $chapter->getSequence()
			)
		);

		$chapter->setId($this->getInsertId());
		$this->updateLocaleFields($chapter);
		return $chapter->getId();
	}

	/**
	 * Update an existing board chapter.
	 * @param $chapter Chapter
	 */
	function updateObject($chapter) {
		$this->update(
			'UPDATE submission_chapters
				SET	submission_id = ?,
					seq = ?
				WHERE
					chapter_id = ?',
			array(
				(int) $chapter->getMonographId(),
				(int) $chapter->getSequence(),
				(int) $chapter->getId()
			)
		);
		$this->updateLocaleFields($chapter);
	}

	/**
	 * Delete a board chapter, including membership info
	 * @param $chapter Chapter
	 */
	function deleteObject($chapter) {
		$this->deleteById($chapter->getId());
	}

	/**
	 * Delete a board chapter, including membership info
	 * @param $chapterId int
	 */
	function deleteById($chapterId) {
		$this->update('DELETE FROM submission_chapter_authors WHERE chapter_id = ?', (int) $chapterId);
		$this->update('DELETE FROM submission_chapter_settings WHERE chapter_id = ?', (int) $chapterId);
		$this->update('DELETE FROM submission_chapters WHERE chapter_id = ?', (int) $chapterId);
	}

	/**
	 * Delete board chapters by assoc ID, including membership info
	 * @param $assocType int
	 * @param $monographId int
	 */
	function deleteByMonographId($monographId) {
		$chapters = $this->getChapters($monographId);
		while ($chapter = $chapters->next()) {
			$this->deleteObject($chapter);
		}
	}

	/**
	 * Sequentially renumber  chapters in their sequence order, optionally by monographId
	 * @param $monographId int
	 */
	function resequenceChapters($monographId = null) {
		$result = $this->retrieve(
			'SELECT chapter_id FROM submission_chapters' .
			($monographId !== null?' WHERE submission_id = ?':'') .
			' ORDER BY seq',
			($monographId !== null)?(int) $monographId:null
		);

		for ($i=1; !$result->EOF; $i++) {
			list($chapterId) = $result->fields;
			$this->update(
				'UPDATE submission_chapters SET seq = ? WHERE chapter_id = ?',
				array(
					(int) $i,
					(int) $chapterId
				)
			);

			$result->MoveNext();
		}

		$result->Close();
	}

	/**
	 * Get the ID of the last inserted board chapter.
	 * @return int
	 */
	function getInsertId() {
		return $this->_getInsertId('submission_chapters', 'chapter_id');
	}

	/**
	 * @copydoc PKPPubIdPluginDAO::pubIdExists()
	 */
	function pubIdExists($pubIdType, $pubId, $excludePubObjectId, $contextId) {
		$result = $this->retrieve(
			'SELECT COUNT(*)
			FROM submission_chapter_settings scs
			INNER JOIN submission_cahpers sc ON scs.chapter_id = sc.chapter_id
			INNER JOIN submissions s ON sc.submission_id = s.submission_id
			WHERE scs.setting_name = ? and scs.setting_value = ? and sc.chapter_id <> ? AND s.context_id = ?',
			array(
				'pub-id::'.$pubIdType,
				$pubId,
				(int) $excludePubObjectId,
				(int) $contextId
			)
		);
		$returner = $result->fields[0] ? true : false;
		$result->Close();
		return $returner;
	}

	/**
	 * @copydoc PKPPubIdPluginDAO::changePubId()
	 */
	function changePubId($pubObjectId, $pubIdType, $pubId) {
		$idFields = array(
			'chapter_id', 'locale', 'setting_name'
		);
		$updateArray = array(
			'chapter_id' => (int) $pubObjectId,
			'locale' => '',
			'setting_name' => 'pub-id::'.$pubIdType,
			'setting_type' => 'string',
			'setting_value' => (string)$pubId
		);
		$this->replace('submission_chapter_settings', $updateArray, $idFields);
	}

	/**
	 * @copydoc PKPPubIdPluginDAO::deletePubId()
	 */
	function deletePubId($pubObjectId, $pubIdType) {
		$settingName = 'pub-id::'.$pubIdType;
		$this->update(
			'DELETE FROM submission_chapter_settings WHERE setting_name = ? AND chapter_id = ?',
			array(
				$settingName,
				(int)$pubObjectId
			)
		);
		$this->flushCache();
	}

	/**
	 * @copydoc PKPPubIdPluginDAO::deleteAllPubIds()
	 */
	function deleteAllPubIds($contextId, $pubIdType) {
		$settingName = 'pub-id::'.$pubIdType;

		$chapters = $this->getByContextId($contextId);
		while ($chapter = $chapters->next()) {
			$this->update(
				'DELETE FROM submission_chapter_settings WHERE setting_name = ? AND chapter_id = ?',
				array(
					$settingName,
					(int)$chapter->getId()
				)
			);
		}
		$this->flushCache();
	}
}


