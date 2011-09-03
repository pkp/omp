<?php

/**
 * @file classes/monograph/MonographKeywordDAO.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographKeywordDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying a monograph's assigned keywords
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabDAO');

define('CONTROLLED_VOCAB_MONOGRAPH_KEYWORD', 'monograph_keyword');

class MonographKeywordDAO extends ControlledVocabDAO {

	function build($monographId) {
		return parent::build(CONTROLLED_VOCAB_MONOGRAPH_KEYWORD, ASSOC_TYPE_MONOGRAPH, $monographId);
	}

	/**
	 * Get the list of non-localized additional fields to store.
	 * @return array
	 */
	function getAdditionalFieldNames() {
		return array('monograph_keyword');
	}

	function getKeywords($monographId) {
		$keywords = $this->build($monographId);
		$monographKeywordEntryDao =& DAORegistry::getDAO('MonographKeywordEntryDAO');
		$monographKeywords = $monographKeywordEntryDao->getByControlledVocabId($keywords->getId());

		$returner = array();
		while ($keyword =& $monographKeywords->next()) {
			$returner[] = $keyword->getKeyword();
			unset($keyword);
		}

		return $returner;
	}

	/**
	 * Get an array of all of the monograph's keywords
	 * @return array
	 */
	function getAllUniqueKeywords() {
		$keywords = array();

		$result =& $this->retrieve(
			'SELECT DISTINCT setting_value FROM controlled_vocab_entry_settings WHERE setting_name = ?', CONTROLLED_VOCAB_MONOGRAPH_KEYWORD
		);

		while (!$result->EOF) {
			$keywords[] = $result->fields[0];
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $keywords;
	}

	/**
	 * Get an array of monographIds that have a given keyword
	 * @param $content string
	 * @return array
	 */
	function getMonographIdsByKeyword($keyword) {
		$result =& $this->retrieve(
			'SELECT assoc_id
			 FROM controlled_vocabs cv
			 LEFT JOIN controlled_vocab_entries cve ON cv.controlled_vocab_id = cve.controlled_vocab_id
			 INNER JOIN controlled_vocab_entry_settings cves ON cve.controlled_vocab_entry_id = cves.controlled_vocab_entry_id
			 WHERE cves.setting_name = ? AND cves.setting_value = ?',
			array(CONTROLLED_VOCAB_MONOGRAPH_KEYWORD, $keyword)
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
	 * Add an array of keywords
	 * @param $keywords array
	 * @param $monographId int
	 * @param $deleteFirst boolean
	 * @return int
	 */
	function insertKeywords($keywords, $monographId, $deleteFirst = true) {
		$keywordDao =& DAORegistry::getDAO('MonographKeywordDAO');
		$monographKeywordEntryDao =& DAORegistry::getDAO('MonographKeywordEntryDAO');
		$currentKeywords = $this->build($monographId);

		if ($deleteFirst) {
			$existingEntries = $keywordDao->enumerate($currentKeywords->getId(), CONTROLLED_VOCAB_MONOGRAPH_KEYWORD);

			foreach ($existingEntries as $id => $entry) {
				$entry = trim($entry);
				$monographKeywordEntryDao->deleteObjectById($id);
			}
		}

		$keywords = array_unique($keywords); // Remove any duplicate keywords
		$i = 1;
		foreach ($keywords as $keyword) {
			$keywordEntry = $monographKeywordEntryDao->newDataObject();
			$keywordEntry->setControlledVocabId($currentKeywords->getId());
			$keywordEntry->setKeyword($keyword);
			$keywordEntry->setSequence($i);
			$i ++;
			$keywordEntryId = $monographKeywordEntryDao->insertObject($keywordEntry);
		}
	}
}
?>
