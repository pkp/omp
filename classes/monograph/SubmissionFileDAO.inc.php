<?php

/**
 * @file classes/monograph/SubmissionFileDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFileDAO
 * @ingroup monograph
 * @see MonographFile
 * @see ArtworkFile
 * @see MonographFileDAODelegate
 * @see ArtworkFileDAODelegate
 *
 * @brief Operations for retrieving and modifying OMP-specific submission
 *  file implementations.
 */


import('lib.pkp.classes.submission.PKPSubmissionFileDAO');

class SubmissionFileDAO extends PKPSubmissionFileDAO {

	/**
	 * Constructor
	 */
	function SubmissionFileDAO() {
		return parent::PKPSubmissionFileDAO();
	}


	//
	// Implement protected template methods from PKPSubmissionFileDAO
	//
	/**
	 * @see PKPSubmissionFileDAO::getSubmissionEntityName()
	 */
	function getSubmissionEntityName() {
		return 'monograph';
	}

	/**
	 * @see PKPSubmissionFileDAO::getDelegateClassNames()
	 */
	function getDelegateClassNames() {
		static $delegateClasses = array(
			'artworkfile' => 'classes.monograph.ArtworkFileDAODelegate',
			'monographfile' => 'classes.monograph.MonographFileDAODelegate'
		);
		return $delegateClasses;
	}

	/**
	 * @see PKPSubmissionFileDAO::getGenreCategoryMapping()
	 */
	function getGenreCategoryMapping() {
		static $genreCategoryMapping = array(
			GENRE_CATEGORY_ARTWORK => 'artworkfile',
			GENRE_CATEGORY_DOCUMENT => 'monographfile'
		);
		return $genreCategoryMapping;
	}

	/**
	 * @see PKPSubmissionFileDAO::baseQueryForFileSelection()
	 */
	function baseQueryForFileSelection() {
		// Build the basic query that joins the class tables.
		return 'SELECT	sf.file_id AS monograph_file_id, sf.revision AS monograph_revision,
				af.file_id AS artwork_file_id, af.revision AS artwork_revision,
				sf.*, af.*
			FROM	monograph_files sf
				LEFT JOIN monograph_artwork_files af ON sf.file_id = af.file_id AND sf.revision = af.revision ';
	}


	//
	// Protected helper methods
	//
	/**
	 * @see PKPSubmissionFileDAO::fromRow()
	 */
	function &fromRow(&$row) {
		// Identify the appropriate file implementation for the
		// given row.
		if (isset($row['artwork_file_id']) && is_numeric($row['artwork_file_id'])) {
			$fileImplementation = 'ArtworkFile';
		} else {
			$fileImplementation = 'MonographFile';
		}

		// Let the superclass instantiate the file.
		return parent::fromRow($row, $fileImplementation);
	}
}

?>
