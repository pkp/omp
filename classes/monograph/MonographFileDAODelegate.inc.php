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
	 * @param SubmissionFileDAO
	 */
	function MonographFileDAODelegate(&$submissionFileDao) {
		parent::SubmissionFileDAODelegate($submissionFileDao);
	}


	//
	// Public methods
	//
	/**
	 * @see SubmissionFileDAODelegate::insert()
	 * @param $monographFile MonographFile
	 * @return MonographFile
	 */
	function &insertObject(&$monographFile) {
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
			$monographFile->getFileName(),
			$monographFile->getFileType(),
			(int)$monographFile->getFileSize(),
			$monographFile->getOriginalFileName(),
			(int)$monographFile->getFileStage(),
			(boolean)$monographFile->getViewable(),
			is_null($monographFile->getUserGroupId()) ? null : (int)$monographFile->getUserGroupId(),
			is_null($monographFile->getAssocType()) ? null : (int)$monographFile->getAssocType(),
			is_null($monographFile->getAssocId()) ? null : (int)$monographFile->getAssocId(),
			is_null($monographFile->getGenreId()) ? null : (int)$monographFile->getGenreId()
		);

		if ($fileId) {
			array_unshift($params, $fileId);
		}

		$submissionFileDao =& $this->getSubmissionFileDAO();
		$submissionFileDao->update(
			sprintf('INSERT INTO monograph_files
				(' . ($fileId ? 'file_id, ' : '') . 'revision, monograph_id, source_file_id, source_revision, file_name, file_type, file_size, original_file_name, file_stage, date_uploaded, date_modified, viewable, user_group_id, assoc_type, assoc_id, genre_id)
				VALUES
				(' . ($fileId ? '?, ' : '') . '?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s, ?, ?, ?, ?, ?)',
				$submissionFileDao->datetimeToDB($monographFile->getDateUploaded()), $submissionFileDao->datetimeToDB($monographFile->getDateModified())),
			$params
		);

		if (!$fileId) {
			$monographFile->setFileId($submissionFileDao->getInsertSubmissionFileId());
		}

		$this->updateLocaleFields($monographFile);

		return $monographFile;
	}

	/**
	 * @see SubmissionFileDAODelegate::update()
	 * @param $monographFile MonographFile
	 */
	function updateObject(&$monographFile) {
		$submissionFileDao =& $this->getSubmissionFileDAO();
		$submissionFileDao->update(
			sprintf('UPDATE monograph_files
				SET
					monograph_id = ?,
					source_file_id = ?,
					source_revision = ?,
					file_name = ?,
					file_type = ?,
					file_size = ?,
					original_file_name = ?,
					file_stage = ?,
					date_uploaded = %s,
					date_modified = %s,
					viewable = ?,
					user_group_id = ?,
					assoc_type = ?,
					assoc_id = ?,
					genre_id = ?
				WHERE file_id = ? AND revision = ?',
				$submissionFileDao->datetimeToDB($monographFile->getDateUploaded()), $submissionFileDao->datetimeToDB($monographFile->getDateModified())),
			array(
				$monographFile->getMonographId(),
				$monographFile->getSourceFileId(),
				$monographFile->getSourceRevision(),
				$monographFile->getFileName(),
				$monographFile->getFileType(),
				$monographFile->getFileSize(),
				$monographFile->getOriginalFileName(),
				$monographFile->getFileStage(),
				$monographFile->getViewable(),
				$monographFile->getUserGroupId(),
				$monographFile->getAssocType(),
				$monographFile->getAssocId(),
				$monographFile->getGenreId(),
				$monographFile->getFileId(),
				$monographFile->getRevision()
			)
		);

		$this->updateLocaleFields($monographFile);
		return true;
	}

	/**
	 * @see SubmissionFileDAODelegate::deleteObject()
	 */
	function deleteObject(&$submissionFile) {
		$submissionFileDao =& $this->getSubmissionFileDAO();
		return $submissionFileDao->update(
			'DELETE FROM monograph_files
			 WHERE file_id = ? AND revision = ?',
			array(
				(int)$submissionFile->getFileId(),
				(int)$submissionFile->getRevision()
			));
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
		$monographFile->setFileName($row['file_name']);
		$monographFile->setOriginalFileName($row['original_file_name']);
		$monographFile->setFileType($row['file_type']);
		$monographFile->setGenreId(is_null($row['genre_id']) ? null : (int)$row['genre_id']);
		$monographFile->setFileSize((int)$row['file_size']);
		$monographFile->setUserGroupId(is_null($row['user_group_id']) ? null : (int)$row['user_group_id']);
		$monographFile->setViewable((boolean)$row['viewable']);

		$submissionFileDao =& $this->getSubmissionFileDAO();
		$monographFile->setDateUploaded($submissionFileDao->datetimeFromDB($row['date_uploaded']));
		$monographFile->setDateModified($submissionFileDao->datetimeFromDB($row['date_modified']));

		$submissionFileDao->getDataObjectSettings('monograph_file_settings', 'file_id', $row['monograph_file_id'], $monographFile);

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
}

?>