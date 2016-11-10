<?php

/**
 * @file classes/search/MonographSearchIndex.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographSearchIndex
 * @ingroup search
 *
 * @brief Class to add content to the monograph search index.
 */

import('lib.pkp.classes.search.SubmissionSearchIndex');

class MonographSearchIndex extends SubmissionSearchIndex {

	/**
	 * Index a block of text for an object.
	 * @param $objectId int
	 * @param $text string
	 * @param $position int
	 */
	function indexObjectKeywords($objectId, $text, &$position) {
		$searchDao = DAORegistry::getDAO('MonographSearchDAO');
		$keywords = self::filterKeywords($text);
		for ($i = 0, $count = count($keywords); $i < $count; $i++) {
			if ($searchDao->insertObjectKeyword($objectId, $keywords[$i], $position) !== null) {
				$position += 1;
			}
		}
	}

	/**
	 * Add a block of text to the search index.
	 * @param $monographId int
	 * @param $type int
	 * @param $text string
	 * @param $assocId int optional
	 */
	function updateTextIndex($monographId, $type, $text, $assocId = null) {
		$searchDao = DAORegistry::getDAO('MonographSearchDAO');
		$objectId = $searchDao->insertObject($monographId, $type, $assocId);
		$position = 0;
		self::indexObjectKeywords($objectId, $text, $position);
	}

	/**
	 * Add a file to the search index.
	 * @param $monographId int
	 * @param $type int
	 * @param $fileId int
	 */
	function updateFileIndex($monographId, $type, $fileId) {
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$file = $submissionFileDao->getLatestRevision($fileId);

		if (isset($file)) {
			$parser = SearchFileParser::fromFile($file);
		}

		if (isset($parser)) {
			if ($parser->open()) {
				$searchDao = DAORegistry::getDAO('MonographSearchDAO');
				$objectId = $searchDao->insertObject($monographId, $type, $fileId);

				$position = 0;
				while(($text = $parser->read()) !== false) {
					self::indexObjectKeywords($objectId, $text, $position);
				}
				$parser->close();
			} else {
				// cannot open parser; unsupported format?
			}
		}
	}

	/**
	 * Delete keywords from the search index.
	 * @param $monographId int
	 * @param $type int optional
	 * @param $assocId int optional
	 */
	function deleteTextIndex($monographId, $type = null, $assocId = null) {
		$searchDao = DAORegistry::getDAO('MonographSearchDAO');
		return $searchDao->deleteSubmissionKeywords($monographId, $type, $assocId);
	}

	/**
	 * Index monograph metadata.
	 * @param $monograph Monograph
	 */
	function indexMonographMetadata(&$monograph) {
		// Build author keywords
		$authorText = array();
		$authorDao = DAORegistry::getDAO('AuthorDAO');
		$authors = $authorDao->getBySubmissionId($monograph->getId());
		foreach ($authors as $author) {
			array_push($authorText, $author->getFirstName());
			array_push($authorText, $author->getMiddleName());
			array_push($authorText, $author->getLastName());
			$affiliations = $author->getAffiliation(null);
			if (is_array($affiliations)) foreach ($affiliations as $affiliation) { // Localized
				array_push($authorText, strip_tags($affiliation));
			}
			$bios = $author->getBiography(null);
			if (is_array($bios)) foreach ($bios as $bio) { // Localized
				array_push($authorText, strip_tags($bio));
			}
		}

		// Update search index
		import('classes.search.MonographSearch');
		$monographId = $monograph->getId();
		self::updateTextIndex($monographId, SUBMISSION_SEARCH_AUTHOR, $authorText);
		self::updateTextIndex($monographId, SUBMISSION_SEARCH_TITLE, $monograph->getTitle(null, false));
		self::updateTextIndex($monographId, SUBMISSION_SEARCH_ABSTRACT, $monograph->getAbstract(null));

		self::updateTextIndex($monographId, SUBMISSION_SEARCH_DISCIPLINE, (array) $monograph->getDiscipline(null));
		self::updateTextIndex($monographId, SUBMISSION_SEARCH_SUBJECT, (array) $monograph->getSubject(null));
		self::updateTextIndex($monographId, SUBMISSION_SEARCH_TYPE, $monograph->getType(null));
		self::updateTextIndex($monographId, SUBMISSION_SEARCH_COVERAGE, (array) $monograph->getCoverage(null));
		// FIXME Index sponsors too?
	}

	/**
	 * Index all monograph files (galley files).
	 * @param $monograph Monograph
	 */
	function indexMonographFiles(&$monograph) {
		// Index galley files
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		import('classes.monograph.MonographFile'); // Constants
		import('classes.search.MonographSearch'); // Constants
		$files = $submissionFileDao->getLatestRevisions($monograph->getId(), SUBMISSION_FILE_PROOF);

		foreach ($files as $file) {
			if ($file->getFileId() && $file->getViewable()) {
				self::updateFileIndex($monograph->getId(), SUBMISSION_SEARCH_GALLEY_FILE, $file->getFileId());
			}
		}
	}

	/**
	 * Remove indexed file contents for a monograph
	 * @param $monograph Monograph
	 */
	function clearMonographFiles($monograph) {
		$searchDao = DAORegistry::getDAO('MonographSearchDAO');
		$searchDao->deleteSubmissionKeywords($monograph->getId(), SUBMISSION_SEARCH_GALLEY_FILE);
	}

	/**
	 * Rebuild the search index for all presses.
	 */
	function rebuildIndex($log = false) {
		// Clear index
		if ($log) echo 'Clearing index ... ';
		$searchDao = DAORegistry::getDAO('MonographSearchDAO');
		// FIXME Abstract into MonographSearchDAO?
		$searchDao->update('DELETE FROM submission_search_object_keywords');
		$searchDao->update('DELETE FROM submission_search_objects');
		$searchDao->update('DELETE FROM submission_search_keyword_list');
		$searchDao->setCacheDir(Config::getVar('files', 'files_dir') . '/_db');
		$searchDao->_dataSource->CacheFlush();
		if ($log) echo "done\n";

		// Build index
		$pressDao = DAORegistry::getDAO('PressDAO');
		$monographDao = DAORegistry::getDAO('MonographDAO');

		$presses = $pressDao->getAll();
		while ($press = $presses->next()) {
			$numIndexed = 0;

			if ($log) echo "Indexing \"", $press->getLocalizedName(), "\" ... ";

			$monographs = $monographDao->getByPressId($press->getId());
			while (!$monographs->eof()) {
				$monograph = $monographs->next();
				if ($monograph->getDatePublished()) {
					self::indexMonographMetadata($monograph);
					self::indexMonographFiles($monograph);
					$numIndexed++;
				}
			}

			if ($log) echo $numIndexed, " monographs indexed\n";
		}
	}

}

?>
