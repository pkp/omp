<?php

/**
 * @file classes/monograph/MonographAgencyDAO.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographAgencyDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying a monograph's assigned agencies
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabDAO');

define('CONTROLLED_VOCAB_MONOGRAPH_AGENCY', 'monographAgency');

class MonographAgencyDAO extends ControlledVocabDAO {
	/**
	 * Constructor
	 */
	function MonographAgencyDAO() {
		parent::ControlledVocabDAO();
	}

	/**
	 * Build/fetch and return a controlled vocabulary for agencies.
	 * @param $monographId int
	 * @return ControlledVocab
	 */
	function build($monographId) {
		return parent::build(CONTROLLED_VOCAB_MONOGRAPH_AGENCY, ASSOC_TYPE_MONOGRAPH, $monographId);
	}

	/**
	 * Get the list of non-localized additional fields to store.
	 * @return array
	 */
	function getAdditionalFieldNames() {
		return array('monographAgency');
	}

	/**
	 * Get agencies for a specified monograph ID.
	 * @param $monographId int
	 * @return array
	 */
	function getAgencies($monographId) {
		$agencies = $this->build($monographId);
		$monographAgencyEntryDao =& DAORegistry::getDAO('MonographAgencyEntryDAO');
		$monographAgencies = $monographAgencyEntryDao->getByControlledVocabId($agencies->getId());

		$returner = array();
		while ($agency =& $monographAgencies->next()) {
			$returner[] = $agency->getAgency();
			unset($agency);
		}

		return $returner;
	}

	/**
	 * Get an array of all of the monograph's agencies
	 * @return array
	 */
	function getAllUniqueAgencies() {
		$agencies = array();

		$result =& $this->retrieve(
			'SELECT DISTINCT setting_value FROM controlled_vocab_entry_settings WHERE setting_name = ?', CONTROLLED_VOCAB_MONOGRAPH_AGENCY
		);

		while (!$result->EOF) {
			$agencies[] = $result->fields[0];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $agencies;
	}

	/**
	 * Get an array of monographIds that have a given agency
	 * @param $content string
	 * @return array
	 */
	function getMonographIdsByAgency($agency) {
		$result =& $this->retrieve(
			'SELECT assoc_id
			 FROM controlled_vocabs cv
			 LEFT JOIN controlled_vocab_entries cve ON cv.controlled_vocab_id = cve.controlled_vocab_id
			 INNER JOIN controlled_vocab_entry_settings cves ON cve.controlled_vocab_entry_id = cves.controlled_vocab_entry_id
			 WHERE cves.setting_name = ? AND cves.setting_value = ?',
			array(CONTROLLED_VOCAB_MONOGRAPH_AGENCY, $agency)
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
	 * Add an array of agencies
	 * @param $agencies array
	 * @param $monographId int
	 * @param $deleteFirst boolean
	 * @return int
	 */
	function insertAgencies($agencies, $monographId, $deleteFirst = true) {
		$agencyDao =& DAORegistry::getDAO('MonographAgencyDAO');
		$monographAgencyEntryDao =& DAORegistry::getDAO('MonographAgencyEntryDAO');
		$currentAgencies = $this->build($monographId);

		if ($deleteFirst) {
			$existingEntries = $agencyDao->enumerate($currentAgencies->getId(), CONTROLLED_VOCAB_MONOGRAPH_AGENCY);

			foreach ($existingEntries as $id => $entry) {
				$entry = trim($entry);
				$monographAgencyEntryDao->deleteObjectById($id);
			}
		}

		if (is_array($agencies)) {
			$agencies = array_unique($agencies); // Remove any duplicate agencies
			$i = 1;
			foreach ($agencies as $agency) {
				$agencyEntry = $monographAgencyEntryDao->newDataObject();
				$agencyEntry->setControlledVocabId($currentAgencies->getId());
				$agencyEntry->setAgency($agency);
				$agencyEntry->setSequence($i);
				$i ++;
				$agencyEntryId = $monographAgencyEntryDao->insertObject($agencyEntry);
			}
		}
	}
}

?>
