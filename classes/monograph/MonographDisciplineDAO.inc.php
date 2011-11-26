<?php

/**
 * @file classes/monograph/MonographDisciplineDAO.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographDisciplineDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying a monograph's assigned
 * disciplines
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabDAO');

define('CONTROLLED_VOCAB_MONOGRAPH_DISCIPLINE', 'monographDiscipline');

class MonographDisciplineDAO extends ControlledVocabDAO {
	/**
	 * Constructor
	 */
	function MonographDisciplineDAO() {
		parent::ControlledVocabDAO();
	}

	/**
	 * Build/fetch a monograph discipline controlled vocabulary.
	 * @pararm $monographId int
	 * @return ControlledVocabulary
	 */
	function build($monographId) {
		return parent::build(CONTROLLED_VOCAB_MONOGRAPH_DISCIPLINE, ASSOC_TYPE_MONOGRAPH, $monographId);
	}

	/**
	 * Get the list of non-localized additional fields to store.
	 * @return array
	 */
	function getAdditionalFieldNames() {
		return array('monographDiscipline');
	}

	/**
	 * Get disciplines for a monograph.
	 * @param $monographId int
	 * @return array
	 */
	function getDisciplines($monographId) {
		$disciplines = $this->build($monographId);
		$monographDisciplineEntryDao =& DAORegistry::getDAO('MonographDisciplineEntryDAO');
		$monographDisciplines = $monographDisciplineEntryDao->getByControlledVocabId($disciplines->getId());

		$returner = array();
		while ($discipline =& $monographDisciplines->next()) {
			$returner[] = $discipline->getDiscipline();
			unset($discipline);
		}

		return $returner;
	}

	/**
	 * Get an array of all of the monograph's disciplines
	 * @return array
	 */
	function getAllUniqueDisciplines() {
		$disciplines = array();

		$result =& $this->retrieve(
			'SELECT DISTINCT setting_value FROM controlled_vocab_entry_settings WHERE setting_name = ?', CONTROLLED_VOCAB_MONOGRAPH_DISCIPLINE
		);

		while (!$result->EOF) {
			$disciplines[] = $result->fields[0];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $disciplines;
	}

	/**
	 * Get an array of monographIds that have a given discipline
	 * @param $content string
	 * @return array
	 */
	function getMonographIdsByDiscipline($discipline) {
		$result =& $this->retrieve(
			'SELECT assoc_id
			 FROM controlled_vocabs cv
			 LEFT JOIN controlled_vocab_entries cve ON cv.controlled_vocab_id = cve.controlled_vocab_id
			 INNER JOIN controlled_vocab_entry_settings cves ON cve.controlled_vocab_entry_id = cves.controlled_vocab_entry_id
			 WHERE cves.setting_name = ? AND cves.setting_value = ?',
			array(CONTROLLED_VOCAB_MONOGRAPH_DISCIPLINE, $discipline)
		);

		$returner = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[] = $row['assoc_id'];
			$result->MoveNext();
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Add an array of disciplines
	 * @param $disciplines array
	 * @param $monographId int
	 * @param $deleteFirst boolean
	 * @return int
	 */
	function insertDisciplines($disciplines, $monographId, $deleteFirst = true) {
		$disciplineDao =& DAORegistry::getDAO('MonographDisciplineDAO');
		$monographDisciplineEntryDao =& DAORegistry::getDAO('MonographDisciplineEntryDAO');
		$currentDisciplines = $this->build($monographId);

		if ($deleteFirst) {
			$existingEntries = $disciplineDao->enumerate($currentDisciplines->getId(), CONTROLLED_VOCAB_MONOGRAPH_DISCIPLINE);

			foreach ($existingEntries as $id => $entry) {
				$entry = trim($entry);
				$monographDisciplineEntryDao->deleteObjectById($id);
			}
		}

		if (is_array($disciplines)) {
			$disciplines = array_unique($disciplines); // Remove any duplicate disciplines
			$i = 1;
			foreach ($disciplines as $discipline) {
				$disciplineEntry = $monographDisciplineEntryDao->newDataObject();
				$disciplineEntry->setControlledVocabId($currentDisciplines->getId());
				$disciplineEntry->setDiscipline($discipline);
				$disciplineEntry->setSequence($i);
				$i ++;
				$disciplineEntryId = $monographDisciplineEntryDao->insertObject($disciplineEntry);
			}
		}
	}
}

?>
