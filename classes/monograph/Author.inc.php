<?php

/**
 * @file classes/monograph/Author.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Author
 * @ingroup monograph
 * @see AuthorDAO
 *
 * @brief Monograph author metadata class.
 */


import('lib.pkp.classes.submission.PKPAuthor');

class Author extends PKPAuthor {
	/**
	 * Constructor.
	 */
	function Author() {
		parent::PKPAuthor();
	}

	//
	// Get/set methods
	//
	/**
	 * Get ID of monograph.
	 * @return int
	 */
	function getMonographId() {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		return $this->getSubmissionId();
	}

	/**
	 * Set ID of monograph.
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		return $this->setSubmissionId($monographId);
	}

	/**
	 * Get a localized version of the User Group
	 * @return string
	 */
	function getLocalizedUserGroupName() {
		//FIXME: should this be queried when fetching Author from DB? - see #5231.
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroup =& $userGroupDao->getById($this->getUserGroupId());
		return $userGroup->getLocalizedName();
	}
}

?>
