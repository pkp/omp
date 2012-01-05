<?php

/**
 * @file classes/monograph/MonographSubjectDAO.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographSubjectDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying a monograph's assigned subjects
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabDAO');

define('CONTROLLED_VOCAB_MONOGRAPH_SUBJECT', 'monographSubject');

class MonographSubjectDAO extends ControlledVocabDAO {
	/**
	 * Constructor
	 */
	function MonographSubjectDAO() {
		parent::ControlledVocabDAO();
	}

	/**
	 * Build/fetch and return a controlled vocabulary for subjects.
	 * @param $monographId int
	 * @return ControlledVocab
	 */
	function build($monographId) {
		// may return an array of ControlledVocabs
		return parent::build(CONTROLLED_VOCAB_MONOGRAPH_SUBJECT, ASSOC_TYPE_MONOGRAPH, $monographId);
	}

	/**
	 * Get the list of localized additional fields to store.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('monographSubject');
	}

	/**
	 * Get Subjects for a monograph.
	 * @param $monographId int
	 * @param $locales array
	 * @return array
	 */
	function getSubjects($monographId, $locales) {

		$returner = array();
		foreach ($locales as $locale) {
			$returner[$locale] = array();
			$subjects = $this->build($monographId);
			$monographSubjectEntryDao =& DAORegistry::getDAO('MonographSubjectEntryDAO');
			$monographSubjects = $monographSubjectEntryDao->getByControlledVocabId($subjects->getId());

			while ($subject =& $monographSubjects->next()) {
				$subject = $subject->getSubject();
				if (array_key_exists($locale, $subject)) { // quiets PHP when there are no Subjects for a given locale
					$returner[$locale][] = $subject[$locale];
					unset($subject);
				}
			}
		}
		return $returner;
	}

	/**
	 * Get an array of all of the monograph's Subjects
	 * @return array
	 */
	function getAllUniqueSubjects() {
		$subjects = array();

		$result =& $this->retrieve(
			'SELECT DISTINCT setting_value FROM controlled_vocab_entry_settings WHERE setting_name = ?', CONTROLLED_VOCAB_MONOGRAPH_SUBJECT
		);

		while (!$result->EOF) {
			$subjects[] = $result->fields[0];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $subjects;
	}

	/**
	 * Get an array of monographIds that have a given subject
	 * @param $content string
	 * @return array
	 */
	function getMonographIdsBySubject($subject) {
		$result =& $this->retrieve(
			'SELECT assoc_id
			 FROM controlled_vocabs cv
			 LEFT JOIN controlled_vocab_entries cve ON cv.controlled_vocab_id = cve.controlled_vocab_id
			 INNER JOIN controlled_vocab_entry_settings cves ON cve.controlled_vocab_entry_id = cves.controlled_vocab_entry_id
			 WHERE cves.setting_name = ? AND cves.setting_value = ?',
			array(CONTROLLED_VOCAB_MONOGRAPH_SUBJECT, $subject)
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
	 * Add an array of subjects
	 * @param $subjects array
	 * @param $monographId int
	 * @param $deleteFirst boolean
	 * @return int
	 */
	function insertSubjects($subjects, $monographId, $deleteFirst = true) {
		$subjectDao =& DAORegistry::getDAO('MonographSubjectDAO');
		$monographSubjectEntryDao =& DAORegistry::getDAO('MonographSubjectEntryDAO');
		$currentSubjects = $this->build($monographId);

		if ($deleteFirst) {
			$existingEntries = $subjectDao->enumerate($currentSubjects->getId(), CONTROLLED_VOCAB_MONOGRAPH_SUBJECT);

			foreach ($existingEntries as $id => $entry) {
				$entry = trim($entry);
				$monographSubjectEntryDao->deleteObjectById($id);
			}
		}
		if (is_array($subjects)) { // localized, array of arrays

			foreach ($subjects as $locale => $list) {
				if (is_array($list)) {
					$list = array_unique($list); // Remove any duplicate Subjects
					$i = 1;
					foreach ($list as $subject) {
						$subjectEntry = $monographSubjectEntryDao->newDataObject();
						$subjectEntry->setControlledVocabId($currentSubjects->getID());
						$subjectEntry->setSubject(urldecode($subject), $locale);
						$subjectEntry->setSequence($i);
						$i ++;
						$subjectEntryId = $monographSubjectEntryDao->insertObject($subjectEntry);
					}
				}
			}
		}
	}
}
?>