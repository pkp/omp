<?php

/**
 * @file classes/monograph/Chapter.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
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
	function __construct() {
		parent::__construct();
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
	 * Get the chapter full title (with title and subtitle).
	 * @return string
	 */
	function getLocalizedFullTitle() {

		$fullTitle = $this->getLocalizedTitle();

		if ($subtitle = $this->getLocalizedSubtitle()) {
			$fullTitle = PKPString::concatTitleFields(array($fullTitle, $subtitle));
		}

		return $fullTitle;
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
	 * Get localized sub title of a chapter.
	 */
	function getLocalizedSubtitle() {
		return $this->getLocalizedData('subtitle');
	}

	/**
	 * Get sub title of chapter (primary locale)
	 * @param $locale string
	 * @return string
	 */
	function getSubtitle($locale = null) {
		return $this->getData('subtitle', $locale);
	}

	/**
	 * Set sub title of chapter
	 * @param $subtitle string
	 * @param $locale string
	 */
	function setSubtitle($subtitle, $locale = null) {
		return $this->setData('subtitle', $subtitle, $locale);
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
	 * @return DAOResultFactory Iterator of authors
	 */
	function getAuthors() {
		$chapterAuthorDao = DAORegistry::getDAO('ChapterAuthorDAO'); /* @var $chapterAuthorDao ChapterAuthorDAO */
		return $chapterAuthorDao->getAuthors($this->getMonographId(), $this->getId());
	}

	/**
	 * Get the author names for this chapter and return them as a string.
	 * @return string
	 */
	function getAuthorNamesAsString() {
		$authorNames = array();
		$authors = $this->getAuthors();
		while ($author = $authors->next()) {
			$authorNames[] = $author->getFullName();
		}
		return join(', ', $authorNames);
	}
}

?>
