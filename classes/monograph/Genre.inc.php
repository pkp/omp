<?php

/**
 * @file classes/monograph/Genre.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Genre
 * @ingroup monograph
 * @see GenreDAO
 *
 * @brief Basic class describing a genre.
 */


define('GENRE_CATEGORY_DOCUMENT', 1);
define('GENRE_CATEGORY_ARTWORK', 2);

define('GENRE_SORTABLE_DESIGNATION', '##');

class Genre extends DataObject {

	/**
	 * Set the name of the genre
	 * @param $name string
	 * @param $locale string
	 */
	function setName($name, $locale) {
		$this->setData('name', $name, $locale);
	}

	/**
	 * Get the name of the genre
	 * @param $locale string
	 * @return string
	 */
	function getName($locale) {
		return $this->getData('name', $locale);
	}

	/**
	 * Get the localized name of the genre
	 * @return string
	 */
	function getLocalizedName() {
		return $this->getLocalizedData('name');
	}

	/**
	 * Set the designation of the genre
	 * @param $abbrev string
	 * @param $locale string
	 */
	function setDesignation($abbrev, $locale) {
		$this->setData('designation', $abbrev, $locale);
	}

	/**
	 * Get the designation of the genre
	 * @param $locale string
	 * @return string
	 */
	function getDesignation($locale) {
		return $this->getData('designation', $locale);
	}

	/**
	 * Get the localized designation of the genre
	 * @return string
	 */
	function getLocalizedDesignation() {
		return $this->getLocalizedData('designation');
	}

	/**
	 * Get sortable flag of the monograph type
	 * @return bool
	 */
	function getSortable() {
		return $this->getData('sortable');
	}

	/**
	 * Set sortable flag of the monograph type
	 * @param $sortable bool
	 */
	function setSortable($sortable) {
		return $this->setData('sortable', $sortable);
	}

	/**
	 * Get monograph file category (e.g. artwork or document)
	 * @return int
	 */
	function getCategory() {
		return $this->getData('category');
	}

	/**
	 * Set monograph file category (e.g. artwork or document)
	 * @param $category bool
	 */
	function setCategory($category) {
		return $this->setData('category', $category);
	}
}

?>