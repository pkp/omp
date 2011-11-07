<?php

/**
 * @file classes/monograph/PublishedMonographDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublishedMonographDAO
 * @ingroup monograph
 * @see PublishedMonograph
 *
 * @brief Operations for retrieving and modifying PublishedMonograph objects.
 */


import('classes.monograph.PublishedMonograph');

class PublishedMonographDAO extends DAO {
 	/**
	 * Constructor.
	 */
	function PublishedMonographDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve all published monographs in a press.
	 * @param $pressId int
	 * @param $rangeInfo object
	 * @return object
	 */
	function &getPublishedMonographsByPressId($pressId = null, $rangeInfo = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();
		$params = array();
		if ($pressId !== null) $params[] = (int) $pressId;
		$result =& $this->retrieveRange(
			'SELECT	pm.*
			FROM	published_monographs pm
				JOIN monographs m ON pm.monograph_id = m.monograph_id
			' . ($journalId !== null?'WHERE m.press_id = ?':'') . '
			ORDER BY pm.date_published',
			$params,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnPublishedMonographFromRow');
		return $returner;
	}

	/**
	 * Retrieve Published Monograph by monograph id
	 * @param $monographId int
	 * @return PublishedMonograph object
	 */
	function &getByMonographId($monographId) {
		$result =& $this->retrieve(
			'SELECT * FROM published_monographs WHERE monograph_id = ?', (int) $monographId
		);
		if ($result->RecordCount() == 0) {
			$returner = null;
			return $returner;
		}

		$row = $result->GetRowAssoc(false);
		$result->Close();
		unset($result);

		$publishedMonograph = $this->_fromRow($row);

		return $publishedMonograph;
	}

	/**
	 * Generate and return a new data object.
	 * @return PublishedMonograph
	 */
	function newDataObject() {
		return new PublishedMonograph();
	}

	/**
	 * Creates and returns a published monograph object from a row
	 * @param $row array
	 * @param $callHooks boolean Whether or not to call hooks
	 * @return PublishedMonograph object
	 */
	function &_fromRow($row, $callHooks = true) {
		$publishedMonograph = $this->newDataObject();
		$publishedMonograph->setPubId($row['pub_id']); // Deprecated
		$publishedMonograph->setDatePublished($this->datetimeFromDB($row['date_published']));
		$publishedMonograph->setSeq($row['seq']);

		if ($callHooks) HookRegistry::call('PublishedMonographDAO::_fromRow', array(&$publishedMonograph, &$row));
		return $publishedMonograph;
	}


	/**
	 * Inserts a new published monograph into published_monographs table
	 * @param PublishedMonograph object
	 */
	function insertObject(&$publishedMonograph) {
		$this->update(
			sprintf('INSERT INTO published_monographs
				(monograph_id, date_published, seq)
				VALUES
				(?, %s, ?)',
				$this->datetimeToDB($publishedMonograph->getDatePublished())),
			array(
				$publishedMonograph->getId(),
				(int) $publishedMonograph->getSeq()
			)
		);
	}

	/**
	 * Removes an published monograph by monograph id
	 * @param monographId int
	 */
	function deleteByMonographId($monographId) {
		$this->update(
			'DELETE FROM published_monographs WHERE monograph_id = ?',
			(int) $monographId
		);
	}

	/**
	 * Update a published monograph
	 * @param PublishedMonograph object
	 */
	function updateObject($publishedMonograph) {
		$this->update(
			sprintf('UPDATE	published_monographs
				SET	date_published = %s,
					seq = ?
				WHERE	monograph_id = ?',
				$this->datetimeToDB($publishedMonograph->getDatePublished())),
			array(
				$publishedMonograph->getSeq(),
				$publishedMonograph->getId()
			)
		);
	}
}

?>
