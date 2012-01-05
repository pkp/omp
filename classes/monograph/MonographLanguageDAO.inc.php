<?php

/**
 * @file classes/monograph/MonographLanguageDAO.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographLanguageDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying a monograph's assigned languages
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabDAO');

define('CONTROLLED_VOCAB_MONOGRAPH_LANGUAGE', 'monographLanguage');

class MonographLanguageDAO extends ControlledVocabDAO {
	/**
	 * Constructor
	 */
	function MonographLanguageDAO() {
		parent::ControlledVocabDAO();
	}

	/**
	 * Build/fetch and return a controlled vocabulary for languages.
	 * @param $monographId int
	 * @return ControlledVocab
	 */
	function build($monographId) {
		// may return an array of ControlledVocabs
		return parent::build(CONTROLLED_VOCAB_MONOGRAPH_LANGUAGE, ASSOC_TYPE_MONOGRAPH, $monographId);
	}

	/**
	 * Get the list of localized additional fields to store.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('monographLanguage');
	}

	/**
	 * Get Languages for a monograph.
	 * @param $monographId int
	 * @param $locales array
	 * @return array
	 */
	function getLanguages($monographId, $locales) {

		$returner = array();
		foreach ($locales as $locale) {
			$returner[$locale] = array();
			$languages = $this->build($monographId);
			$monographLanguageEntryDao =& DAORegistry::getDAO('MonographLanguageEntryDAO');
			$monographLanguages = $monographLanguageEntryDao->getByControlledVocabId($languages->getId());

			while ($language =& $monographLanguages->next()) {
				$language = $language->getLanguage();
				if (array_key_exists($locale, $language)) { // quiets PHP when there are no Languages for a given locale
					$returner[$locale][] = $language[$locale];
					unset($language);
				}
			}
		}
		return $returner;
	}

	/**
	 * Get an array of all of the monograph's Languages
	 * @return array
	 */
	function getAllUniqueLanguages() {
		$languages = array();

		$result =& $this->retrieve(
			'SELECT DISTINCT setting_value FROM controlled_vocab_entry_settings WHERE setting_name = ?', CONTROLLED_VOCAB_MONOGRAPH_LANGUAGE
		);

		while (!$result->EOF) {
			$languages[] = $result->fields[0];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $languages;
	}

	/**
	 * Get an array of monographIds that have a given language
	 * @param $content string
	 * @return array
	 */
	function getMonographIdsByLanguage($language) {
		$result =& $this->retrieve(
			'SELECT assoc_id
			 FROM controlled_vocabs cv
			 LEFT JOIN controlled_vocab_entries cve ON cv.controlled_vocab_id = cve.controlled_vocab_id
			 INNER JOIN controlled_vocab_entry_settings cves ON cve.controlled_vocab_entry_id = cves.controlled_vocab_entry_id
			 WHERE cves.setting_name = ? AND cves.setting_value = ?',
			array(CONTROLLED_VOCAB_MONOGRAPH_SUBJECT, $language)
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
	 * Add an array of languages
	 * @param $languages array
	 * @param $monographId int
	 * @param $deleteFirst boolean
	 * @return int
	 */
	function insertLanguages($languages, $monographId, $deleteFirst = true) {
		$languageDao =& DAORegistry::getDAO('MonographLanguageDAO');
		$monographLanguageEntryDao =& DAORegistry::getDAO('MonographLanguageEntryDAO');
		$currentLanguages = $this->build($monographId);

		if ($deleteFirst) {
			$existingEntries = $languageDao->enumerate($currentLanguages->getId(), CONTROLLED_VOCAB_MONOGRAPH_LANGUAGE);

			foreach ($existingEntries as $id => $entry) {
				$entry = trim($entry);
				$monographLanguageEntryDao->deleteObjectById($id);
			}
		}
		if (is_array($languages)) { // localized, array of arrays

			foreach ($languages as $locale => $list) {
				if (is_array($list)) {
					$list = array_unique($list); // Remove any duplicate Languages
					$i = 1;
					foreach ($list as $language) {
						$languageEntry = $monographLanguageEntryDao->newDataObject();
						$languageEntry->setControlledVocabId($currentLanguages->getID());
						$languageEntry->setLanguage(urldecode($language), $locale);
						$languageEntry->setSequence($i);
						$i ++;
						$languageEntryId = $monographLanguageEntryDao->insertObject($languageEntry);
					}
				}
			}
		}
	}
}
?>