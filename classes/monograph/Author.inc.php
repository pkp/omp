<?php

/**
 * @file classes/author/Author.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Author
 * @ingroup monograph
 * @see AuthorDAO
 *
 * @brief Monograph author metadata class.
 */

// $Id$

import('submission.PKPAuthor');

define('PRIMARY_CONTACT',	1);

define('CONTRIBUTION_TYPE_AUTHOR',		0);
define('CONTRIBUTION_TYPE_VOLUME_EDITOR',	1);

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
	 * Get the author's contribution type.
	 * @return int
	 */
	function getContributionType() {
		return $this->getData('contribution_type');
	}

	/**
	 * Set the author's contribution type
	 * @param $type int
	 */
	function setContributionType($type) {
		$this->setData('contribution_type', $type);
	}

	/**
	 * Get ID of monograph.
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	 * Set ID of monograph.
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}
}

?>
