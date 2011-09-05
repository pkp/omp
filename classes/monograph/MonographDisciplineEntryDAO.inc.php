<?php

/**
 * @file classes/monograph/MonographDisciplineEntryDAO.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographDisciplineEntryDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying a monograph's disciplines
 */


import('classes.monograph.MonographDiscipline');
import('lib.pkp.classes.controlledVocab.ControlledVocabEntryDAO');

class MonographDisciplineEntryDAO extends ControlledVocabEntryDAO {
	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return PaperTypeEntry
	 */
	function newDataObject() {
		return new MonographDiscipline();
	}

	/**
	 * Get the list of non-localized additional fields to store.
	 * @return array
	 */
	function getAdditionalFieldNames() {
		return array('monographDiscipline');
	}

	/**
	 * Retrieve an iterator of controlled vocabulary entries matching a
	 * particular controlled vocabulary ID.
	 * @param $controlledVocabId int
	 * @return object DAOResultFactory containing matching CVE objects
	 */
	function getByControlledVocabId($controlledVocabId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT cve.* FROM controlled_vocab_entries cve WHERE cve.controlled_vocab_id = ? ORDER BY seq',
			array((int) $controlledVocabId),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}
}

?>