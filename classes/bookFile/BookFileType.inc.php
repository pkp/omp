<?php

/**
 * @defgroup bookFile
 */

/**
 * @file classes/bookFile/BookFileType.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookFileType
 * @ingroup bookFile
 * @see BookFileTypeDAO
 *
 * @brief Basic class describing a book file type.
 */


define('BOOK_FILE_CATEGORY_DOCUMENT', 1);
define('BOOK_FILE_CATEGORY_ARTWORK', 2);

define('BOOK_FILE_TYPE_SORTABLE_DESIGNATION', '##');

class BookFileType extends DataObject {

	/**
	 * Set the name of the book file type
	 * @param $name string
	 * @param $locale string
	 */
	function setName($name, $locale) {
		$this->setData('name', $name, $locale);
	}

	/**
	 * Get the name of the book file type
	 * @param $locale string
	 * @return string
	 */
	function getName($locale) {
		return $this->getData('name', $locale);
	}

	/**
	 * Get the localized name of the book file type
	 * @return string
	 */
	function getLocalizedName() {
		return $this->getLocalizedData('name');
	}

	/**
	 * Set the designation of the book file type
	 * @param $abbrev string
	 * @param $locale string
	 */
	function setDesignation($abbrev, $locale) {
		$this->setData('designation', $abbrev, $locale);
	}

	/**
	 * Get the designation of the book file type
	 * @param $locale string
	 * @return string
	 */
	function getDesignation($locale) {
		return $this->getData('designation', $locale);
	}

	/**
	 * Get the localized designation of the book file type
	 * @return string
	 */
	function getLocalizedDesignation() {
		return $this->getLocalizedData('designation');
	}

	/**
	 * Get sortable flag of the book type
	 * @return bool
	 */
	function getSortable() {
		return $this->getData('sortable');
	}

	/**
	 * Set sortable flag of the book type
	 * @param $sortable bool
	 */
	function setSortable($sortable) {
		return $this->setData('sortable', $sortable);
	}

	/**
	 * Get book file category (e.g. artwork)
	 * @return int
	 */
	function getCategory() {
		return $this->getData('category');
	}

	/**
	 * Set book file category
	 * @param $category bool
	 */
	function setCategory($category) {
		return $this->setData('category', $category);
	}
}

?>