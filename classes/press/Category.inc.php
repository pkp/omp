<?php

/**
 * @file classes/press/Category.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	 * Get category path.
	 * @return string
	 */
	function getPath() {
		return $this->getData('path');
	}

	/**
	 * Set category path.
	 * @param $path string
	 */
	function setPath($path) {
		return $this->setData('path', $path);
	}

	/**
	 * Get localized title of the category.
	 * @return string
	 */
	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * Get title of category.
	 * @param $locale string
	 * @return string
	 */
	function getTitle($locale) {
		return $this->getData('title', $locale);
	}

	/**
	 * Set title of category.
	 * @param $title string
	 * @param $locale string
	 */
	function setTitle($title, $locale) {
		return $this->setData('title', $title, $locale);
	}

	/**
	 * Get localized description of the category.
	 * @return string
	 */
	function getLocalizedDescription() {
		return $this->getLocalizedData('description');
	}

	/**
	 * Get description of category.
	 * @param $locale string
	 * @return string
	 */
	function getDescription($locale) {
		return $this->getData('description', $locale);
	}

	/**
	 * Set description of category.
	 * @param $description string
	 * @param $locale string
	 */
	function setDescription($description, $locale) {
		return $this->setData('description', $description, $locale);
	}

	/**
	 * Get the image.
	 * @return array
	 */
	function getImage() {
		return $this->getData('image');
	}

	/**
	 * Set the image.
	 * @param $image array
	 */
	function setImage($image) {
		return $this->setData('image', $image);
	}
}

?>
