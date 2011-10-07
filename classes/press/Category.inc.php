<?php

/**
 * @file classes/press/Category.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Category
 * @ingroup press
 * @see CategoryDAO
 *
 * @brief Describes basic Category properties.
 */


class Category extends DataObject {

	/**
	 * Constructor.
	 */
	function Category() {
		parent::DataObject();
	}

	/**
	 * Get ID of press.
	 * @return int
	 */
	function getPressId() {
		return $this->getData('pressId');
	}

	/**
	 * Set ID of press.
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		return $this->setData('pressId', $pressId);
	}

	/**
	 * Get ID of parent category.
	 * @return int
	 */
	function getParentId() {
		return $this->getData('parentId');
	}

	/**
	 * Set ID of parent category.
	 * @param $parentId int
	 */
	function setParentId($parentId) {
		return $this->setData('parentId', $parentId);
	}

	/**
	 * Get localized title of the series.
	 * @return string
	 */
	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * Get title of series.
	 * @param $locale string
	 * @return string
	 */
	function getTitle($locale) {
		return $this->getData('title', $locale);
	}

	/**
	 * Set title of series.
	 * @param $title string
	 * @param $locale string
	 */
	function setTitle($title, $locale) {
		return $this->setData('title', $title, $locale);
	}
}

?>
