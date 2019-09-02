<?php

/**
 * @file classes/monograph/Chapter.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Chapter
 * @ingroup monograph
 * @see ChapterDAO
 *
 * @brief Describes a monograph chapter (or section)
 */

import('lib.pkp.classes.submission.SubmissionVersionedDataObject');

class Chapter extends SubmissionVersionedDataObject {
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
		return parent::getSubmissionId();
	}

	/**
	 * Set the monographId this chapter belongs to
	 * @param int $monographId
	 */
	function setMonographId($monographId) {
		return parent::setSubmissionId($monographId);
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
	 * @param $preferred boolean If the preferred public name should be used, if exist
	 * @return string
	 */
	function getAuthorNamesAsString($preferred = true) {
		$authorNames = array();
		$authors = $this->getAuthors();
		while ($author = $authors->next()) {
			$authorNames[] = $author->getFullName($preferred);
		}
		return join(', ', $authorNames);
	}

	/**
	 * Get stored public ID of the chapter.
	 * @param @literal $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>). @endliteral
	 * @return int
	 */
	function getStoredPubId($pubIdType) {
		return $this->getData('pub-id::'.$pubIdType);
	}

	/**
	 * Set the stored public ID of the chapter.
	 * @param $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 * @param $pubId string
	 */
	function setStoredPubId($pubIdType, $pubId) {
		$this->setData('pub-id::'.$pubIdType, $pubId);
	}

	/**
	 * @copydoc DataObject::getDAO()
	 */
	function getDAO() {
		return DAORegistry::getDAO('ChapterDAO');
	}

	/**
	 * Get abstract of chapter (primary locale)
	 * @param $locale string
	 * @return string
	 */
	function getAbstract($locale = null) {
		return $this->getData('abstract', $locale);
	}

	/**
	 * Set abstract of chapter
	 * @param $abstract string
	 * @param $locale string
	 */
	function setAbstract($abstract, $locale = null) {
		return $this->setData('abstract', $abstract, $locale);
	}
	/**
	 * Get localized abstract of a chapter.
	 */
	function getLocalizedAbstract() {
		return $this->getLocalizedData('abstract');
	}

	/**
	 * get date published
	 * @return date
	 */
	function getDatePublished() {
		return $this->getData('datePublished');
	}

	/**
	 * set date published
	 * @param $datePublished date
	 */
	function setDatePublished($datePublished) {
		return $this->setData('datePublished', $datePublished);
	}

	/**
	 * get pages
	 * @return string
	 */
	function getPages() {
		return $this->getData('pages');
	}

	/**
	 * set pages
	 * @param $pages string
	 */
	function setPages($pages) {
		$this->setData('pages', $pages);
	}
}


