<?php

/**
 * @file classes/search/MonographSearchIndex.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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
	public function indexObjectKeywords($objectId, $text, &$position) {
		$searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
		$keywords = $this->filterKeywords($text);
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
	public function updateTextIndex($monographId, $type, $text, $assocId = null) {
		$searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
		$objectId = $searchDao->insertObject($monographId, $type, $assocId);
		$position = 0;
		$this->indexObjectKeywords($objectId, $text, $position);
	}

	/**
	 * Add a file to the search index.
	 * @param $monographId int
	 * @param $type int
	 * @param $submissionFileId int
	 */
	public function updateFileIndex($monographId, $type, $submissionFileId) {
		$submisssionFile = Services::get('submissionFile')->get($submissionFileId);

		if (isset($submisssionFile)) {
			$parser = SearchFileParser::fromFile($submisssionFile);
		}

		if (isset($parser)) {
			if ($parser->open()) {
				$searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
				$objectId = $searchDao->insertObject($monographId, $type, $submissionFileId);

				$position = 0;
				while(($text = $parser->read()) !== false) {
					$this->indexObjectKeywords($objectId, $text, $position);
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
	public function deleteTextIndex($monographId, $type = null, $assocId = null) {
		// Check whether a search plugin jumps in.
		$hookResult = HookRegistry::call(
			'MonographSearchIndex::submissionDeleted',
			[$monographId]
		);

		if (!empty($hookResult)) {
			return;
		}

		$searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
		return $searchDao->deleteSubmissionKeywords($monographId, $type, $assocId);
	}

	/**
	 * Index monograph metadata.
	 * @param Submission $submission
	 */
	public function submissionMetadataChanged($submission) {
		// Check whether a search plug-in jumps in.
		$hookResult = HookRegistry::call(
			'MonographSearchIndex::submissionMetadataChanged',
			[$submission]
		);

		if (!empty($hookResult)) {
			return;
		}

		$publication = $submission->getCurrentPublication();

		// Build author keywords
		$authorText = [];
		foreach ($publication->getData('authors') as $author) {
			$authorText = array_merge(
				$authorText,
				array_values((array) $author->getData('givenName')),
				array_values((array) $author->getData('familyName')),
				array_values((array) $author->getData('preferredPublicName')),
				array_values(array_map('strip_tags', (array) $author->getData('affiliation'))),
				array_values(array_map('strip_tags', (array) $author->getData('biography')))
			);
		}

		// Update search index
		import('classes.search.MonographSearch');
		$submissionId = $submission->getId();
		$this->updateTextIndex($submissionId, SUBMISSION_SEARCH_AUTHOR, $authorText);
		$this->updateTextIndex($submissionId, SUBMISSION_SEARCH_TITLE, $publication->getData('title'));
		$this->updateTextIndex($submissionId, SUBMISSION_SEARCH_ABSTRACT, $publication->getData('abstract'));

		$this->updateTextIndex($submissionId, SUBMISSION_SEARCH_SUBJECT, (array) $this->_flattenLocalizedArray($publication->getData('subjects')));
		$this->updateTextIndex($submissionId, SUBMISSION_SEARCH_KEYWORD, (array) $this->_flattenLocalizedArray($publication->getData('keywords')));
		$this->updateTextIndex($submissionId, SUBMISSION_SEARCH_DISCIPLINE, (array) $this->_flattenLocalizedArray($publication->getData('disciplines')));
		$this->updateTextIndex($submissionId, SUBMISSION_SEARCH_TYPE, (array) $publication->getData('type'));
		$this->updateTextIndex($submissionId, SUBMISSION_SEARCH_COVERAGE, (array) $publication->getData('coverage'));
		// FIXME Index sponsors too?
	}

	/**
	 * Index all monograph files (galley files).
	 * @param $monograph Monograph
	 */
	public function submissionFilesChanged($monograph) {
		// Check whether a search plugin jumps in.
		$hookResult = HookRegistry::call(
			'MonographSearchIndex::submissionFilesChanged',
			[$monograph]
		);

		if (!empty($hookResult)) {
			return;
		}

		// Index galley files
		import('lib.pkp.classes.submission.SubmissionFile'); // Constants
		import('classes.search.MonographSearch'); // Constants
		$submissionFiles = Services::get('submissionFile')->getMany([
			'submissionIds' => [$monograph->getId()],
			'fileStages' => [SUBMISSION_FILE_PROOF],
		]);

		foreach ($submissionFiles as $submissionFile) {
			$this->updateFileIndex($monograph->getId(), SUBMISSION_SEARCH_GALLEY_FILE, $submissionFile->getId());
		}
	}

	/**
	 * @copydoc SubmissionSearchIndex::clearSubmissionFiles()
	 */
	public function clearSubmissionFiles($submission) {
		$searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
		$searchDao->deleteSubmissionKeywords($submission->getId(), SUBMISSION_SEARCH_GALLEY_FILE);
	}

	/**
	 * @copydoc SubmissionSearchIndex::submissionChangesFinished()
	 */
	public function submissionChangesFinished() {
		// Trigger a hook to let the indexing back-end know that
		// the index may be updated.
		HookRegistry::call(
			'MonographSearchIndex::monographChangesFinished'
		);

		// The default indexing back-end works completely synchronously
		// and will therefore not do anything here.
	}

	/**
	 * @copydoc SubmissionSearchIndex::submissionChangesFinished()
	 */
	public function monographChangesFinished() {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated call to monographChangesFinished. Use submissionChangesFinished instead.');
		$this->submissionChangesFinished();
	}


	/**
	 * Rebuild the search index for all presses.
	 * @param $log boolean Whether or not to log progress to the console.
	 */
	public function rebuildIndex($log = false) {
		// Check whether a search plugin jumps in.
		$hookResult = HookRegistry::call(
			'MonographSearchIndex::rebuildIndex',
			[$log]
		);
		if (!empty($hookResult)) {
			return;
		}

		// Clear index
		if ($log) echo 'Clearing index ... ';
		$searchDao = DAORegistry::getDAO('MonographSearchDAO'); /* @var $searchDao MonographSearchDAO */
		// FIXME Abstract into MonographSearchDAO?
		$searchDao->update('DELETE FROM submission_search_object_keywords');
		$searchDao->update('DELETE FROM submission_search_objects');
		$searchDao->update('DELETE FROM submission_search_keyword_list');
		$searchDao->setCacheDir(Config::getVar('files', 'files_dir') . '/_db');
		if ($log) echo "done\n";

		// Build index
		$pressDao = DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$submissionDao = DAORegistry::getDAO('SubmissionDAO'); /* @var $submissionDao SubmissionDAO */

		$presses = $pressDao->getAll();
		while ($press = $presses->next()) {
			$numIndexed = 0;

			if ($log) echo "Indexing \"", $press->getLocalizedName(), "\" ... ";

			$monographs = $submissionDao->getByContextId($press->getId());
			while (!$monographs->eof()) {
				$monograph = $monographs->next();
				if ($monograph->getDatePublished()) {
					$this->submissionMetadataChanged($monograph);
					$this->submissionFilesChanged($monograph);
					$numIndexed++;
				}
			}
			$this->submissionChangesFinished();

			if ($log) echo $numIndexed, " monographs indexed\n";
		}
	}

	/**
	 * Flattens array of localized fields to a single, non-associative array of items
	 *
	 * @param $arrayWithLocales array Array of localized fields
	 * @return array
	 */
	protected function _flattenLocalizedArray($arrayWithLocales) {
		$flattenedArray = array();
		foreach ($arrayWithLocales as $localeArray) {
			$flattenedArray = array_merge(
				$flattenedArray,
				$localeArray
			);
		}
		return $flattenedArray;
	}
}

