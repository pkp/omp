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
define('MONOGRAPH_FILE_ARTWORK', 0x000003);
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
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($this->getMonographId());
		$pressId = $monograph->getPressId();

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($this->getMonographId());

		return Config::getVar('files', 'files_dir') . '/presses/' . $pressId .
		'/monographs/' . $this->getMonographId() . '/' . $monographFileManager->typeToPath($this->getType()) . '/' . $this->getFileName();
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
	 * Get object that is associated with this file.
	 * @return object
	 */
	function getAssocObject() {
		return $this->getData('assocObject');
	}

	/**
	 * Set object that is associated with this file.
	 * @param $assocObject object
	 */
	function setAssocObject($assocObject) {
		return $this->setData('assocObject', $assocObject);
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
	 * Get review type (internal or external review).
	 * @return int
	 */
	function getReviewType() {
		return $this->getData('reviewType');
	}

	/**
	 * Set review type.
	 * @param $reviewType int
	 */
	function setReviewType($reviewType) {
		return $this->SetData('reviewType', $reviewType);
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
}

?>
