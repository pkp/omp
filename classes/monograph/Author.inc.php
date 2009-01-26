<?php

/**
 * @file classes/author/Author.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
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

define('PRIMARY_CONTACT',1);

define('AUTHOR',0);
define('VOLUME_EDITOR',1);

class Author extends PKPAuthor {

	/**
	 * Constructor.
	 */
	function Author() {
		parent::DataObject();
	}

	//
	// Get/set methods
	//
	function getContributionType() {
		return $this->getData('contribution_type');
	}
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
