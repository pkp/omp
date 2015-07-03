<?php

/**
 * @file classes/monograph/ChapterAuthor.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterAuthor
 * @ingroup monograph
 * @see ChapterAuthorDAO
 * @see AuthorDAO
 *
 * @brief Adds chapterId to an Author
 */

import('classes.monograph.Author');

class ChapterAuthor extends Author {

	/**
	 * Constructor.
	 */
	function ChapterAuthor() {
		parent::Author();
	}

	//
	// Get/set methods
	//

	/**
	 * Get Chapter ID of this author
	 * @return int
	 */
	function getChapterId() {
		return $this->getData('chapterId');
	}

	/**
	 * Set ID of chapter.
	 * @param $chapterId int
	 */
	function setChapterId($chapterId) {
		return $this->setData('chapterId', $chapterId);
	}
}

?>
