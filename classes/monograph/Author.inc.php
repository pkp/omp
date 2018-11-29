<?php

/**
 * @file classes/monograph/Author.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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
	 * Get whether or not this author should be displayed as a volume editor
	 * @return boolean
	 */
	public function getIsVolumeEditor() {
		return $this->getData('isVolumeEditor');
	}

	/**
	 * Set whether or not this author should be displayed as a volume editor
	 * @param $isVolumeEditor boolean
	 */
	public function setIsVolumeEditor($isVolumeEditor) {
		$this->setData('isVolumeEditor', $isVolumeEditor);
	}
}


