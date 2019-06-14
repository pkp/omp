<?php

/**
 * @file classes/monograph/AuthorDAO.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
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
import('classes.monograph.Submission');
import('lib.pkp.classes.submission.PKPAuthorDAO');

class AuthorDAO extends PKPAuthorDAO {
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
	function getAuthorsAlphabetizedByPress($pressId = null, $initial = null, $rangeInfo = null) {
		$params = array(
			'affiliation', AppLocale::getPrimaryLocale(),
			'affiliation', AppLocale::getLocale()
		);

		if (isset($pressId)) $params[] = $pressId;
		if (isset($initial)) {
			$params[] = PKPString::strtolower($initial) . '%';
			$initialSql = ' AND LOWER(a.last_name) LIKE LOWER(?)';
		} else {
			$initialSql = '';
		}

		$result = $this->retrieveRange(
			'SELECT DISTINCT
				CAST(\'\' AS CHAR) AS url,
				a.author_id AS author_id,
				a.submission_id AS submission_id,
				CAST(\'\' AS CHAR) AS email,
				0 AS primary_contact,
				0 AS seq,
				a.first_name AS first_name,
				a.middle_name AS middle_name,
				a.last_name AS last_name,
				asl.setting_value AS affiliation_l,
				asl.locale,
				aspl.setting_value AS affiliation_pl,
				aspl.locale AS primary_locale,
				a.suffix AS suffix,
				a.user_group_id AS user_group_id,
				a.include_in_browse AS include_in_browse,
				0 AS show_title,
				a.country,
				a.submission_version,
				a.prev_ver_id,
				a.is_current_submission_version = ?
			FROM	authors a
				LEFT JOIN author_settings aspl ON (a.author_id = aspl.author_id AND aspl.setting_name = ? AND aspl.locale = ?)
				LEFT JOIN author_settings asl ON (a.author_id = asl.author_id AND asl.setting_name = ? AND asl.locale = ?)
				JOIN submissions s ON (a.submission_id = s.submission_id)
			WHERE a.is_current_submission_version = 1 AND	s.status = ' . STATUS_PUBLISHED . ' ' .
				(isset($pressId)?'AND s.context_id = ? ':'') . '
				AND (a.last_name IS NOT NULL AND a.last_name <> \'\')' .
				$initialSql . '
			ORDER BY a.last_name, a.first_name',
			$params,
			$rangeInfo
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Get a new data object
	 * @return DataObject
	 */
	function newDataObject() {
		return new Author();
	}

	/**
	 * @copydoc DAO::getAdditionalFieldNames()
	 */
	function getAdditionalFieldNames() {
		return array_merge(parent::getAdditionalFieldNames(), array(
			'isVolumeEditor',
		));
	}
}


