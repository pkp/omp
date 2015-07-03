<?php

/**
 * @file classes/monograph/SubmissionFileDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	 * @copydoc PKPSubmissionFileDAO::getDelegateClassNames()
	 */
	function getDelegateClassNames() {
		return array_replace(
			parent::getDelegateClassNames(),
			array(
				'monographartworkfile' => 'classes.monograph.ArtworkFileDAODelegate',
				'monographfile' => 'classes.monograph.MonographFileDAODelegate', // Override parent
			)
		);
	}

	/**
	 * @copydoc PKPSubmissionFileDAO::getGenreCategoryMapping()
	 */
	function getGenreCategoryMapping() {
		return array_replace(
			parent::getGenreCategoryMapping(),
			array(
				GENRE_CATEGORY_ARTWORK => 'monographartworkfile', // Override parent
				GENRE_CATEGORY_DOCUMENT => 'monographfile', // Override parent
			)
		);
	}


	//
	// Protected helper methods
	//
	/**
	 * @copydoc PKPSubmissionFileDAO::fromRow()
	 */
	function fromRow($row) {
		if (isset($row['artwork_file_id']) && is_numeric($row['artwork_file_id'])) {
			return parent::fromRow($row, 'MonographArtworkFile');
		} elseif (isset($row['supplementary_file_id']) && is_numeric($row['supplementary_file_id'])) {
			return parent::fromRow($row, 'SupplementaryFile');
		} else {
			return parent::fromRow($row, 'MonographFile');
		}
	}
}

?>
