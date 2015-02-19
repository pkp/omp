<?php

/**
 * @file classes/monograph/ArtworkFileDAODelegate.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArtworkFileDAODelegate
 * @ingroup monograph
 * @see ArtworkFile
 *
 * @brief Operations for retrieving and modifying ArtworkFile objects.
 *
 * The SubmissionFileDAO will delegate to this class if it wishes
 * to access ArtworkFile classes.
 */


import('classes.monograph.ArtworkFile');
import('lib.pkp.classes.submission.SubmissionArtworkFileDAODelegate');

class ArtworkFileDAODelegate extends SubmissionArtworkFileDAODelegate {
	/**
	 * Constructor
	 */
	function ArtworkFileDAODelegate() {
		parent::SubmissionArtworkFileDAODelegate();
	}

	/**
	 * @see SubmissionFileDAODelegate::newDataObject()
	 * @return MonographFile
	 */
	function newDataObject() {
		return new ArtworkFile();
	}

	/**
	 * @copydoc DAO::getAdditionalFieldNames()
	 */
	function getAdditionalFieldNames() {
		return array_merge(
			parent::getAdditionalFieldNames(),
			array('chapterId')
		);
	}
}

?>
