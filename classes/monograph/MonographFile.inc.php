<?php

/**
 * @file classes/monograph/MonographFile.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFile
 * @ingroup monograph
 * @see MonographFileDAO
 *
 * @brief Monograph file class.
 */



import('lib.pkp.classes.submission.SubmissionFile');

// File Type IDs
define('MONOGRAPH_FILE_PUBLIC', 0x000001);
define('MONOGRAPH_FILE_SUBMISSION', 0x000002);
define('MONOGRAPH_FILE_NOTE', 0x000004);
define('MONOGRAPH_FILE_REVIEW', 0x000005);
define('MONOGRAPH_FILE_FINAL', 0x000006);
define('MONOGRAPH_FILE_FAIR_COPY', 0x000007);
define('MONOGRAPH_FILE_EDITOR', 0x000008);
define('MONOGRAPH_FILE_COPYEDIT', 0x000009);
define('MONOGRAPH_FILE_PRODUCTION', 0x000010);
define('MONOGRAPH_FILE_GALLEY', 0x000011);
define('MONOGRAPH_FILE_LAYOUT', 0x000012);
define('MONOGRAPH_FILE_ATTACHMENT', 0x000013);
define('MONOGRAPH_FILE_COPYEDIT_RESPONSE', 0x000014);

class MonographFile extends SubmissionFile {

	/**
	 * Constructor.
	 */
	function MonographFile() {
		parent::SubmissionFile();
	}

	/**
	 * Return absolute path to the file on the host filesystem.
	 * @return string
	 */
	function getFilePath() {
		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
		$monograph =& $monographDao->getMonograph($this->getMonographId());
		import('classes.file.MonographFileManager');
		return $monograph->getFilePath() . MonographFileManager::typeToPath($this->getType()) . '/' . $this->getFileName();
	}


	//
	// Get/set methods
	//
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
		$fileManager = new FileManager();
		return strtoupper($fileManager->getExtension($this->getData('fileName')));
	}

	/**
	 * Get the file's document type (enumerated types)
	 * @return string
	 */
	function getDocumentType() {
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();
		return $fileManager->getDocumentType($this->getFileType());
	}

	/**
	 * Check if the file may be displayed inline.
	 * @return boolean
	 */
	function isInlineable() {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		return $monographFileDao->isInlineable($this);
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
}

?>
