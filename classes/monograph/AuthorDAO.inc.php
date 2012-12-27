<?php

/**
 * @file classes/monograph/AuthorDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorDAO
 * @ingroup monograph
 * @see Author
 * @see ChapterDAO (uses AuthorDAO)
 *
 * @brief Operations for retrieving and modifying Author objects.
 */



import('classes.monograph.Author');
import('classes.monograph.Monograph');
import('lib.pkp.classes.submission.PKPAuthorDAO');

class AuthorDAO extends PKPAuthorDAO {
	/**
	 * Constructor
	 */
	function AuthorDAO() {
		parent::PKPAuthorDAO();
	}

	/**
	 * Retrieve all published authors for a press in an associative array by
	 * the first letter of the last name, for example:
	 * $returnedArray['S'] gives array($misterSmithObject, $misterSmytheObject, ...)
	 * Keys will appear in sorted order. Note that if pressId is null,
	 * alphabetized authors for all presses are returned.
	 * @param $pressId int
	 * @param $initial An initial the last names must begin with
	 * @return array Authors ordered by sequence
	 */
	function &getAuthorsAlphabetizedByPress($pressId = null, $initial = null, $rangeInfo = null) {
		$authors = array();
		$params = array(
			'affiliation', AppLocale::getPrimaryLocale(),
			'affiliation', AppLocale::getLocale()
		);

		if (isset($pressId)) $params[] = $pressId;
		if (isset($initial)) {
			$params[] = String::strtolower($initial) . '%';
			$initialSql = ' AND LOWER(ma.last_name) LIKE LOWER(?)';
		} else {
			$initialSql = '';
		}

		$result =& $this->retrieveRange(
			'SELECT DISTINCT
				CAST(\'\' AS CHAR) AS url,
				ma.author_id AS author_id,
				ma.submission_id AS submission_id,
				CAST(\'\' AS CHAR) AS email,
				0 AS primary_contact,
				0 AS seq,
				ma.first_name AS first_name,
				ma.middle_name AS middle_name,
				ma.last_name AS last_name,
				asl.setting_value AS affiliation_l,
				asl.locale,
				aspl.setting_value AS affiliation_pl,
				aspl.locale AS primary_locale,
				ma.suffix AS suffix,
				ma.user_group_id AS user_group_id,
				ma.country
			FROM	authors ma
				LEFT JOIN author_settings aspl ON (ma.author_id = aspl.author_id AND aspl.setting_name = ? AND aspl.locale = ?)
				LEFT JOIN author_settings asl ON (ma.author_id = asl.author_id AND asl.setting_name = ? AND asl.locale = ?)
				JOIN monographs a ON (ma.submission_id = a.monograph_id)
			WHERE	a.status = ' . STATUS_PUBLISHED . ' ' .
				(isset($pressId)?'AND a.press_id = ? ':'') . '
				AND (ma.last_name IS NOT NULL AND ma.last_name <> \'\')' .
				$initialSql . '
			ORDER BY ma.last_name, ma.first_name',
			$params,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnAuthorFromRow');
		return $returner;
	}

	/**
	 * Get a new data object
	 * @return DataObject
	 */
	function newDataObject() {
		return new Author();
	}
}

?>
