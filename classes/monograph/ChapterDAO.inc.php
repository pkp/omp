<?php

/**
 * @file classes/monograph/ChapterDAO.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
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

class ChapterDAO extends DAO {
	/**
	 * Constructor.
	 */
	function ChapterDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a chapter by ID.
	 * @param $chapterId int
	 * @param $assocType int optional
	 * @param $monographId int optional
	 * @return Chapter
	 */
	function &getChapter($chapterId, $monographId = null) {
		$params = array((int) $chapterId);
		if ($monographId !== null) {
			$params[] = (int) $monographId;
		}

		$result =& $this->retrieve(
			'SELECT * FROM monograph_chapters WHERE chapter_id = ?' . ($monographId !== null?' AND monograph_id = ? ':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);
		return $returner;
	}

	/**
	 * Get all chapters for a given monograph.
	 * @param $monographId int
	 * @param $rangeInfo object RangeInfo object (optional)
	 * @return array
	 */
	function &getChapters($monographId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT chapter_id, monograph_id, chapter_seq FROM monograph_chapters WHERE monograph_id = ? ORDER BY chapter_seq',
			(int) $monographId,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnFromRow', array('id'));
		return $returner;
	}

	/**
	 * Get the list of fields for which locale data is stored.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title');
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
	function &_returnFromRow(&$row) {
		$chapter = $this->newDataObject();
		$chapter->setId($row['chapter_id']);
		$chapter->setMonographId($row['monograph_id']);
		$chapter->setSequence($row['chapter_seq']);
		$this->getDataObjectSettings('monograph_chapter_settings', 'chapter_id', $row['chapter_id'], $chapter);

		HookRegistry::call('ChapterDAO::_returnFromRow', array(&$chapter, &$row));

		return $chapter;
	}

	/**
	 * Update the settings for this object
	 * @param $chapter object
	 */
	function updateLocaleFields(&$chapter) {
		$this->updateDataObjectSettings('monograph_chapter_settings', $chapter, array(
			'chapter_id' => $chapter->getId()
		));
	}

	/**
	 * Insert a new board chapter.
	 * @param $chapter Chapter
	 */
	function insertChapter(&$chapter) {
		$this->update(
			'INSERT INTO monograph_chapters
				(monograph_id, chapter_seq)
				VALUES
				(?, ?)',
			array(
				(int) $chapter->getMonographId(),
				(int) $chapter->getSequence()
			)
		);

		$chapter->setId($this->getInsertChapterId());
		$this->updateLocaleFields($chapter);
		return $chapter->getId();
	}

	/**
	 * Update an existing board chapter.
	 * @param $chapter Chapter
	 */
	function updateObject(&$chapter) {
		$returner = $this->update(
			'UPDATE monograph_chapters
				SET	monograph_id = ?,
					chapter_seq = ?
				WHERE
					chapter_id = ?',
			array(
				(int) $chapter->getMonographId(),
				(int) $chapter->getSequence(),
				(int) $chapter->getId()
			)
		);
		$this->updateLocaleFields($chapter);
		return $returner;
	}

	/**
	 * Delete a board chapter, including membership info
	 * @param $chapter Chapter
	 */
	function deleteObject(&$chapter) {
		return $this->deleteById($chapter->getId());
	}

	/**
	 * Delete a board chapter, including membership info
	 * @param $chapterId int
	 */
	function deleteById($chapterId) {
		$returner1 = $this->update('DELETE FROM monograph_chapter_authors WHERE chapter_id = ?', (int) $chapterId);
		$returner2 = $this->update('DELETE FROM monograph_chapter_settings WHERE chapter_id = ?', (int) $chapterId);
		$returner3 = $this->update('DELETE FROM monograph_chapters WHERE chapter_id = ?', (int) $chapterId);
		return ($returner1 && $returner2 && $returner3);
	}

	/**
	 * Delete board chapters by assoc ID, including membership info
	 * @param $assocType int
	 * @param $monographId int
	 */
	function deleteByMonographId($monographId) {
		$chapters =& $this->getChapters($monographId);
		while ($chapter =& $chapters->next()) {
			$this->deleteObject($chapter);
			unset($chapter);
		}
	}

	/**
	 * Sequentially renumber  chapters in their sequence order, optionally by monographId
	 * @param $monographId int
	 */
	function resequenceChapters($monographId = null) {
		$result =& $this->retrieve(
			'SELECT chapter_id FROM monograph_chapters' .
			($monographId !== null?' WHERE monograph_id = ?':'') .
			' ORDER BY seq',
			($monographId !== null)?(int) $monographId:null
		);

		for ($i=1; !$result->EOF; $i++) {
			list($chapterId) = $result->fields;
			$this->update(
				'UPDATE monograph_chapters SET chapter_seq = ? WHERE chapter_id = ?',
				array(
					(int) $i,
					(int) $chapterId
				)
			);

			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Get the ID of the last inserted board chapter.
	 * @return int
	 */
	function getInsertChapterId() {
		return $this->getInsertId('chapters', 'chapter_id');
	}
}

?>
