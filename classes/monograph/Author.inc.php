<?php

/**
 * @file classes/monograph/Author.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
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
	 * Get whether or not the author should be listed as a volume editor
	 *
	 * @return boolean
	 */
	public function getIsVolumeEditor() {
		return $this->getData('isVolumeEditor');
	}

	/**
	 * Set whether or not the author should be listed as a volume editor
	 *
	 * @param boolean $isVolumeEditor
	 */
	public function setIsVolumeEditor($isVolumeEditor) {
		$this->setData('isVolumeEditor', $isVolumeEditor);
	}
}

?>
