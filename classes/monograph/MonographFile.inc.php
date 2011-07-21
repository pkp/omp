<?php

/**
 * @file classes/monograph/MonographFile.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFile
 * @ingroup monograph
 * @see SubmissionFileDAO
 *
 * @brief Monograph file class.
 */



import('lib.pkp.classes.submission.SubmissionFile');

// Define the file stage identifiers.
define('MONOGRAPH_FILE_PUBLIC', 1);
define('MONOGRAPH_FILE_SUBMISSION', 2);
define('MONOGRAPH_FILE_NOTE', 3);
define('MONOGRAPH_FILE_REVIEW', 4);
define('MONOGRAPH_FILE_REVIEW_ATTACHMENT', 5);
define('MONOGRAPH_FILE_FINAL', 6);
define('MONOGRAPH_FILE_FAIR_COPY', 7);
define('MONOGRAPH_FILE_EDITOR', 8);
define('MONOGRAPH_FILE_COPYEDIT', 9);
define('MONOGRAPH_FILE_PRODUCTION', 10);
define('MONOGRAPH_FILE_GALLEY', 11);
define('MONOGRAPH_FILE_LAYOUT', 12);
define('MONOGRAPH_FILE_ATTACHMENT', 13);
define('MONOGRAPH_FILE_COPYEDIT_RESPONSE', 14);


class MonographFile extends SubmissionFile {

	/**
	 * Constructor.
	 */
	function MonographFile() {
		parent::SubmissionFile();
	}


	//
	// Get/set methods
	//
	/**
	 * Set the uploader's user id.
	 * @param $uploaderUserId integer
	 */
	function setUploaderUserId($uploaderUserId) {
		$this->setData('uploaderUserId', $uploaderUserId);
	}

	/**
	 * Get the uploader's user id.
	 * @return integer
	 */
	function getUploaderUserId() {
		return $this->getData('uploaderUserId');
	}

	/**
	 * Set the uploader's user group id
	 * @param $userGroupId int
	 */
	function setUserGroupId($userGroupId) {
		$this->setData('userGroupId', $userGroupId);
	}

	/**
	 * Get the uploader's user group id
	 * @return int
	 */
	function getUserGroupId() {
		return $this->getData('userGroupId');
	}

	/**
	 * Get type that is associated with this file.
	 * @return int
	 */
	function getAssocType() {
		return $this->getData('assocType');
	}

	/**
	 * Set type that is associated with this file.
	 * @param $assocType int
	 */
	function setAssocType($assocType) {
		return $this->setData('assocType', $assocType);
	}

	/**
	 * Get id that is associated with this file.
	 * @return int
	 */
	function getAssocId() {
		return $this->getData('assocId');
	}

	/**
	 * Set id that is associated with this file.
	 * @param $assocId int
	 */
	function setAssocId($assocId) {
		return $this->setData('assocId', $assocId);
	}

	/**
	 * Get ID of monograph.
	 * @return int
	 */
	function getMonographId() {
		return $this->getSubmissionId();
	}

	/**
	 * Set ID of monograph.
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setSubmissionId($monographId);
	}

	/**
	 * Set the name of the file
	 * @param $name string
	 * @param $locale string
	 */
	function setName($name, $locale) {
		$this->setData('name', $name, $locale);
	}

	/**
	 * Get the name of the file
	 * @param $locale string
	 * @return string
	 */
	function getName($locale) {
		return $this->getData('name', $locale);
	}

	/**
	 * Get the localized name of the file
	 * @return string
	 */
	function getLocalizedName() {
		return $this->getLocalizedData('name');
	}

	/**
	 * Get the file's extension.
	 * @return string
	 */
	function getExtension() {
		import('lib.pkp.classes.file.FileManager');
		return strtoupper(FileManager::parseFileExtension($this->getOriginalFileName()));
	}

	/**
	 * Get the file's document type (enumerated types)
	 * @return string
	 */
	function getDocumentType() {
		import('lib.pkp.classes.file.FileManager');
		return FileManager::getDocumentType($this->getFileType());
	}

	/**
	 * Set the genre id of this file (i.e. referring to Manuscript, Index, etc)
	 * Foreign key into genres table
	 * @param $genreId int
	 */
	function setGenreId($genreId) {
		$this->setData('genreId', $genreId);
	}

	/**
	 * Get the genre id of this file (i.e. referring to Manuscript, Index, etc)
	 * Foreign key into genres table
	 * @return int
	 */
	function getGenreId() {
		return $this->getData('genreId');
	}

	/**
	 * Build a file name label.
	 * @return string
	 */
	function getFileLabel($locale = null) {
		// Retrieve the localized file name as basis for the label.
		if ($locale) {
			$fileLabel = $this->getName($locale);
		} else {
			$fileLabel = $this->getLocalizedName();
		}

		// If we have no file name then use a default name.
		if (empty($fileLabel)) $fileLabel = Locale::translate('common.untitled');

		// Add the revision number to the label if we have more than one revision.
		if ($this->getRevision() > 1) $fileLabel .= ' (' . $this->getRevision() . ')';

		return $fileLabel;
	}


	//
	// Overridden public methods from PKPFile
	//
	/**
	 * @see PKPFile::getFileName()
	 * Generate the file name from identification data rather than
	 * retrieving it from the database.
	 */
	function getFileName() {
		// FIXME: Need to move this to PKPFile or remove PKPFile's getFileName()
		// implementation and place it into app-specific classes, see #6446.
		return $this->_generateFileName();
	}

	/**
	 * @see PKPFile::setFileName()
	 * Do not allow setting the file name of a monograph file
	 * directly because it is generated from identification data.
	 */
	function setFileName($fileName) {
		// FIXME: Remove this setter from PKPFile, too? See #6446.
		assert(false);
	}


	//
	// Implementation of template methods from SubmissionFile
	//
	/**
	 * @see SubmissionFile::getFilePath()
	 */
	function getFilePath() {
		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
		$monograph =& $monographDao->getMonograph($this->getMonographId());
		return $monograph->getFilePath() . $this->_fileStageToPath($this->getFileStage()) . '/' . $this->getFileName();
	}


	//
	// Private helper methods
	//
	/**
	 * Return path associated with a file stage code.
	 * @param $fileStage string
	 * @return string
	 */
	function _fileStageToPath($fileStage) {
		static $fileStageToPath = array(
				MONOGRAPH_FILE_PUBLIC => 'public',
				MONOGRAPH_FILE_SUBMISSION => 'submission',
				MONOGRAPH_FILE_NOTE => 'note',
				MONOGRAPH_FILE_REVIEW => 'submission/review',
				MONOGRAPH_FILE_REVIEW_ATTACHMENT => 'submission/review/attachment',
				MONOGRAPH_FILE_FINAL => 'submission/final',
				MONOGRAPH_FILE_FAIR_COPY => 'submission/fairCopy',
				MONOGRAPH_FILE_EDITOR => 'submission/editor',
				MONOGRAPH_FILE_COPYEDIT => 'submission/copyedit',
				MONOGRAPH_FILE_COPYEDIT_RESPONSE => 'submission/copyeditResponse',
				MONOGRAPH_FILE_PRODUCTION => 'submission/production',
				MONOGRAPH_FILE_GALLEY => 'submission/galleys',
				MONOGRAPH_FILE_LAYOUT => 'submission/layout',
				MONOGRAPH_FILE_ATTACHMENT => 'attachment');

		assert(isset($fileStageToPath[$fileStage]));
		return $fileStageToPath[$fileStage];
	}

	/**
	 * Generate the unique filename for this monograph file.
	 * @return string
	 */
	function _generateFileName() {
		// Remember the ID information we generated the file name
		// on so that we only have to re-generate the name if the
		// relevant information changed.
		static $lastIds = array();
		static $fileName = null;

		// Retrieve the current id information.
		$currentIds = array(
			'genreId' => $this->getGenreId(),
			'dateUploaded' => $this->getDateUploaded(),
			'monographId' => $this->getMonographId(),
			'fileId' => $this->getFileId(),
			'revision' => $this->getRevision(),
			'fileStage' => $this->getFileStage(),
			'extension' => strtolower($this->getExtension())
		);

		// Check whether we need a refresh.
		$refreshRequired = false;
		foreach($currentIds as $key => $currentId) {
			if (!isset($lastIds[$key]) || $lastIds[$key] !== $currentId) {
				$refreshRequired = true;
				$lastIds = $currentIds;
				break;
			}
		}

		// Refresh the file name if required.
		if ($refreshRequired) {
			// If the file has a file genre set then include
			// human readable genre information.
			$genreName = '';
			if ($currentIds['genreId']) {
				$primaryLocale = Locale::getPrimaryLocale();
				$genreDao =& DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */
				$genre =& $genreDao->getById($currentIds['genreId']);
				assert(is_a($genre, 'Genre'));
				$genreName = $genre->getDesignation($primaryLocale).'_'.$genre->getName($primaryLocale).'-';
			}

			// Generate a human readable time stamp.
			$timestamp = date('Ymd', strtotime($currentIds['dateUploaded']));

			// Make the file name unique across all files and file revisions.
			// Also make sure that files can be ordered sensibly by file name.
			$fileName = $currentIds['monographId'].'-'.$genreName.$currentIds['fileId'].'-'.$currentIds['revision'].'-'.$currentIds['fileStage'].'-'.$timestamp.'.'.$currentIds['extension'];
		}

		return $fileName;
	}
}

?>
