<?php

/**
 * @file classes/monograph/MonographFileDAODelegate.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileDAODelegate
 * @ingroup monograph
 * @see MonographFile
 * @see SubmissionFileDAO
 *
 * @brief Operations for retrieving and modifying MonographFile objects.
 *
 * The SubmissionFileDAO will delegate to this class if it wishes
 * to access MonographFile classes.
 */


import('classes.monograph.MonographFile');
import('lib.pkp.classes.submission.SubmissionFileDAODelegate');

class MonographFileDAODelegate extends SubmissionFileDAODelegate {
	/**
	 * Constructor
	 */
	function MonographFileDAODelegate() {
		parent::SubmissionFileDAODelegate();
	}


	//
	// Public methods
	//
	/**
	 * @see SubmissionFileDAODelegate::getSubmissionEntityName()
	 */
	function getSubmissionEntityName() {
		return 'monograph';
	}

	/**
	 * @see SubmissionFileDAODelegate::insert()
	 * @param $monographFile MonographFile
	 * @return MonographFile
	 */
	function &insertObject(&$monographFile, $sourceFile, $isUpload = false) {
		$fileId = $monographFile->getFileId();

		if (!is_numeric($monographFile->getRevision())) {
			// Set the initial revision.
			$monographFile->setRevision(1);
		}

		if (!is_bool($monographFile->getViewable())) {
			// Set the viewable default.
			$monographFile->setViewable(false);
		}

		$params = array(
			(int)$monographFile->getRevision(),
			(int)$monographFile->getMonographId(),
			is_null($monographFile->getSourceFileId()) ? null : (int)$monographFile->getSourceFileId(),
			is_null($monographFile->getSourceRevision()) ? null : (int)$monographFile->getSourceRevision(),
			$monographFile->getFileType(),
			(int)$monographFile->getFileSize(),
			$monographFile->getOriginalFileName(),
			(int)$monographFile->getFileStage(),
			(boolean)$monographFile->getViewable(),
			is_null($monographFile->getUploaderUserId()) ? null : (int)$monographFile->getUploaderUserId(),
			is_null($monographFile->getUserGroupId()) ? null : (int)$monographFile->getUserGroupId(),
			is_null($monographFile->getAssocType()) ? null : (int)$monographFile->getAssocType(),
			is_null($monographFile->getAssocId()) ? null : (int)$monographFile->getAssocId(),
			is_null($monographFile->getGenreId()) ? null : (int)$monographFile->getGenreId()
		);

		if ($fileId) {
			array_unshift($params, $fileId);
		}

		$this->update(
			sprintf('INSERT INTO monograph_files
				(' . ($fileId ? 'file_id, ' : '') . 'revision, monograph_id, source_file_id, source_revision, file_type, file_size, original_file_name, file_stage, date_uploaded, date_modified, viewable, uploader_user_id, user_group_id, assoc_type, assoc_id, genre_id)
				VALUES
				(' . ($fileId ? '?, ' : '') . '?, ?, ?, ?, ?, ?, ?, ?, %s, %s, ?, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($monographFile->getDateUploaded()), $this->datetimeToDB($monographFile->getDateModified())),
			$params
		);

		if (!$fileId) {
			$monographFile->setFileId($this->getInsertId('monograph_files', 'monograph_id'));
		}

		$this->updateLocaleFields($monographFile);

		// Determine the final destination of the file (requires
		// the file id we just generated).
		$targetFilePath = $monographFile->getFilePath();

		// Only copy the file if it is not yet in the target position.
		if ($isUpload || $sourceFile != $targetFilePath) {
			// Copy the file from its current location to the target destination.
			import('lib.pkp.classes.file.FileManager');
			if ($isUpload) {
				$success = FileManager::uploadFile($sourceFile, $targetFilePath);
			} else {
				assert(is_readable($sourceFile));
				$success = FileManager::copyFile($sourceFile, $targetFilePath);
			}
			if (!$success) {
				// If the copy/upload operation fails then remove
				// the already inserted meta-data.
				$this->deleteObject($monographFile);
				$nullVar = null;
				return $nullVar;
			}
		}
		assert(is_readable($targetFilePath));

		return $monographFile;
	}

	/**
	 * @see SubmissionFileDAODelegate::update()
	 * @param $monographFile MonographFile
	 * @param $previousFile MonographFile
	 */
	function updateObject(&$monographFile, &$previousFile) {
		// Update the file in the database.
		$this->update(
			sprintf('UPDATE monograph_files
				SET
					file_id = ?,
					revision = ?,
					monograph_id = ?,
					source_file_id = ?,
					source_revision = ?,
					file_type = ?,
					file_size = ?,
					original_file_name = ?,
					file_stage = ?,
					date_uploaded = %s,
					date_modified = %s,
					viewable = ?,
					uploader_user_id = ?,
					user_group_id = ?,
					assoc_type = ?,
					assoc_id = ?,
					genre_id = ?
				WHERE file_id = ? AND revision = ?',
				$this->datetimeToDB($monographFile->getDateUploaded()), $this->datetimeToDB($monographFile->getDateModified())),
			array(
				(int)$monographFile->getFileId(),
				(int)$monographFile->getRevision(),
				(int)$monographFile->getMonographId(),
				is_null($monographFile->getSourceFileId()) ? null : (int)$monographFile->getSourceFileId(),
				is_null($monographFile->getSourceRevision()) ? null : (int)$monographFile->getSourceRevision(),
				$monographFile->getFileType(),
				$monographFile->getFileSize(),
				$monographFile->getOriginalFileName(),
				$monographFile->getFileStage(),
				is_null($monographFile->getViewable()) ? null : (boolean)$monographFile->getViewable(),
				is_null($monographFile->getUploaderUserId()) ? null : (int)$monographFile->getUploaderUserId(),
				is_null($monographFile->getUserGroupId()) ? null : (int)$monographFile->getUserGroupId(),
				is_null($monographFile->getAssocType()) ? null : (int)$monographFile->getAssocType(),
				is_null($monographFile->getAssocId()) ? null : (int)$monographFile->getAssocId(),
				is_null($monographFile->getGenreId()) ? null : (int)$monographFile->getGenreId(),
				(int)$previousFile->getFileId(),
				(int)$previousFile->getRevision()
			)
		);

		$this->updateLocaleFields($monographFile);

		// Update all dependent objects.
		$this->_updateDependentObjects($monographFile, $previousFile);

		// Copy the file from its current location to the target destination
		// if necessary.
		$previousFilePath = $previousFile->getFilePath();
		$targetFilePath = $monographFile->getFilePath();
		if ($previousFilePath != $targetFilePath && is_file($previousFilePath)) {
			// The file location changed so let's move the file on
			// the file system, too.
			assert(is_readable($previousFilePath));
			import('lib.pkp.classes.file.FileManager');
			if (!FileManager::copyFile($previousFilePath, $targetFilePath)) return false;
			if (!FileManager::deleteFile($previousFilePath)) return false;
		}

		return file_exists($targetFilePath);
	}

	/**
	 * @see SubmissionFileDAODelegate::deleteObject()
	 */
	function deleteObject(&$submissionFile) {
		if (!$this->update(
			'DELETE FROM monograph_files
			 WHERE file_id = ? AND revision = ?',
			array(
				(int)$submissionFile->getFileId(),
				(int)$submissionFile->getRevision()
			))) return false;

		// Delete all dependent objects.
		$this->_deleteDependentObjects($submissionFile);

		// Delete the file on the file system, too.
		$filePath = $submissionFile->getFilePath();
		if(!(is_file($filePath) && is_readable($filePath))) return false;
		assert(is_writable(dirname($filePath)));

		import('lib.pkp.classes.file.FileManager');
		FileManager::deleteFile($filePath);

		return !file_exists($filePath);
	}

	/**
	 * @see SubmissionFileDAODelegate::fromRow()
	 * @return MonographFile
	 */
	function &fromRow(&$row) {
		$monographFile = $this->newDataObject();
		$monographFile->setFileId((int)$row['monograph_file_id']);
		$monographFile->setRevision((int)$row['monograph_revision']);
		$monographFile->setAssocType(is_null($row['assoc_type']) ? null : (int)$row['assoc_type']);
		$monographFile->setAssocId(is_null($row['assoc_id']) ? null : (int)$row['assoc_id']);
		$monographFile->setSourceFileId(is_null($row['source_file_id']) ? null : (int)$row['source_file_id']);
		$monographFile->setSourceRevision(is_null($row['source_revision']) ? null : (int)$row['source_revision']);
		$monographFile->setMonographId((int)$row['monograph_id']);
		$monographFile->setFileStage((int)$row['file_stage']);
		$monographFile->setOriginalFileName($row['original_file_name']);
		$monographFile->setFileType($row['file_type']);
		$monographFile->setGenreId(is_null($row['genre_id']) ? null : (int)$row['genre_id']);
		$monographFile->setFileSize((int)$row['file_size']);
		$monographFile->setUploaderUserId(is_null($row['uploader_user_id']) ? null : (int)$row['uploader_user_id']);
		$monographFile->setUserGroupId(is_null($row['user_group_id']) ? null : (int)$row['user_group_id']);
		$monographFile->setViewable((boolean)$row['viewable']);

		$monographFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));
		$monographFile->setDateModified($this->datetimeFromDB($row['date_modified']));

		$this->getDataObjectSettings('monograph_file_settings', 'file_id', $row['monograph_file_id'], $monographFile);

		return $monographFile;
	}

	/**
	 * @see SubmissionFileDAODelegate::newDataObject()
	 * @return MonographFile
	 */
	function newDataObject() {
		return new MonographFile();
	}


	//
	// Protected helper methods
	//
	/**
	 * @see SubmissionFileDAODelegate::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		$localeFieldNames = parent::getLocaleFieldNames();
		$localeFieldNames[] = 'name';
		return $localeFieldNames;
	}


	//
	// Private helper methods
	//
	/**
	 * Update all objects that depend on the given file.
	 * @param $monographFile MonographFile
	 * @param $previousFile MonographFile
	 */
	function _updateDependentObjects(&$monographFile, &$previousFile) {
		// If the file ids didn't change then we do not have to
		// do anything.
		if (
			$previousFile->getFileId() == $monographFile->getFileId() ||
			$previousFile->getRevision() == $monographFile->getRevision()
		) return;

		// Update signoffs that refer to this file.
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoffFactory =& $signoffDao->getByFileRevision(
			$previousFile->getFileId(), $previousFile->getRevision()
		);
		while ($signoff =& $signoffFactory->next()) { /* @var $signoff Signoff */
			$signoff->setFileId($monographFile->getFileId());
			$signoff->setFileRevision($monographFile->getRevision());
			$signoffDao->updateObject($signoff);
			unset($signoff);
		}

		// Update file views that refer to this file.
		$viewsDao =& DAORegistry::getDAO('ViewsDAO'); /* @var $viewsDao ViewsDAO */
		$viewsDao->moveViews(
			ASSOC_TYPE_MONOGRAPH_FILE,
			$previousFile->getFileIdAndRevision(), $monographFile->getFileIdAndRevision()
		);
	}

	/**
	 * Delete all objects that depend on the given file.
	 * @param $monographFile MonographFile
	 */
	function _deleteDependentObjects(&$monographFile) {
		// Delete signoffs that refer to this file.
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoffFactory =& $signoffDao->getByFileRevision(
			$monographFile->getFileId(), $monographFile->getRevision()
		);
		while ($signoff =& $signoffFactory->next()) { /* @var $signoff Signoff */
			$signoffDao->deleteObject($signoff);
			unset($signoff);
		}

		// Delete file views that refer to this file.
		$viewsDao =& DAORegistry::getDAO('ViewsDAO'); /* @var $viewsDao ViewsDAO */
		$viewsDao->deleteViews(
			ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileIdAndRevision()
		);
	}
}

?>
