<?php

/**
 * @file classes/monograph/Chapter.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Chapter
 * @ingroup monograph
 * @see ChapterDAO
 *
 * @brief Describes a monograph chapter (or section)
 */

class Chapter extends DataObject {
	/**
	 * Constructor
	 */
	function Chapter() {
		parent::DataObject();
	}

	//
	// Get/set methods
	//
	/**
	 * Get the monographId this chapter belongs to
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	 * Set the monographId this chapter belongs to
	 * @param int $monographId
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}

	/**
	 * Get localized title of a chapter.
	 */
	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}


	/**
	 * Get title of chapter (primary locale)
	 * @param $locale string
	 * @return string
	 */
	function getTitle($locale = null) {
		return $this->getData('title', $locale);
	}

	/**
	 * Set title of chapter
	 * @param $title string
	 * @param $locale string
	 */
	function setTitle($title, $locale = null) {
		return $this->setData('title', $title, $locale);
	}

	/**
	 * Get sequence of chapter.
	 * @return float
	 */
	function getSequence() {
		return $this->getData('sequence');
	}

	/**
	 * Set sequence of chapter.
	 * @param $sequence float
	 */
	function setSequence($sequence) {
		return $this->setData('sequence', $sequence);
	}

	/**
	 * Get all authors of this chapter.
	 * @return array Authors
	 */
	function &getAuthors() {
		$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO'); /* @var $chapterAuthorDao ChapterAuthorDAO */
		$returner =& $chapterAuthorDao->getAuthors($this->getMonographId(), $this->getId());
		return $returner;
	}
}

?>
