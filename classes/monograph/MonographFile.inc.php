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
	// Public helper methods
	//
	/**
	 * Identify the workflow stage associated
	 * with a file stage code.
	 * @param $fileStage
	 */
	function fileStageToWorkflowStage($fileStage) {
		// FIXME: We need to join the two review workflow stages into one, see #6244.
		static $fileStageToWorkflowStage = array(
				MONOGRAPH_FILE_PUBLIC => null,
				MONOGRAPH_FILE_SUBMISSION => WORKFLOW_STAGE_ID_SUBMISSION,
				MONOGRAPH_FILE_NOTE => null,
				MONOGRAPH_FILE_REVIEW => WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
				MONOGRAPH_FILE_ATTACHMENT => WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
				MONOGRAPH_FILE_FINAL => WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
				MONOGRAPH_FILE_FAIR_COPY => WORKFLOW_STAGE_ID_EDITING,
				MONOGRAPH_FILE_EDITOR => WORKFLOW_STAGE_ID_EDITING,
				MONOGRAPH_FILE_COPYEDIT => WORKFLOW_STAGE_ID_EDITING,
				MONOGRAPH_FILE_PRODUCTION => WORKFLOW_STAGE_ID_PRODUCTION,
				MONOGRAPH_FILE_GALLEY => WORKFLOW_STAGE_ID_PRODUCTION,
				MONOGRAPH_FILE_LAYOUT => WORKFLOW_STAGE_ID_PRODUCTION);

		assert(isset($fileStageToWorkflowStage[$fileStage]));
		return $fileStageToWorkflowStage[$fileStage];
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
				MONOGRAPH_FILE_FINAL => 'submission/final',
				MONOGRAPH_FILE_FAIR_COPY => 'submission/fairCopy',
				MONOGRAPH_FILE_EDITOR => 'submission/editor',
				MONOGRAPH_FILE_COPYEDIT => 'submission/copyedit',
				MONOGRAPH_FILE_PRODUCTION => 'submission/production',
				MONOGRAPH_FILE_GALLEY => 'submission/galleys',
				MONOGRAPH_FILE_LAYOUT => 'submission/layout',
				MONOGRAPH_FILE_ATTACHMENT => 'attachment');

		assert(isset($fileStageToPath[$fileStage]));
		return $fileStageToPath[$fileStage];
	}
}

?>
