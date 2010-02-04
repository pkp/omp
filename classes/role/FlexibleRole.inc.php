<?php

/**
 * @defgroup role
 */

/**
 * @file classes/role/FlexibleRole.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FlexibleRole
 * @ingroup role
 * @see FlexibleRoleDAO
 *
 * @brief Basic class describing a flexible role.
 */

define('FLEXIBLE_ROLE_SERIES_SUBMISSION',		1);
define('FLEXIBLE_ROLE_SERIES_INTERNAL_REVIEW',	2);
define('FLEXIBLE_ROLE_SERIES_EXTERNAL_REVIEW',	3);
define('FLEXIBLE_ROLE_SERIES_EDITORIAL',		4);
define('FLEXIBLE_ROLE_SERIES_PRODUCTION',		5);

define('FLEXIBLE_ROLE_CLASS_AUTHOR',	1);
define('FLEXIBLE_ROLE_CLASS_PRESS',	2);
define('FLEXIBLE_ROLE_CLASS_MANAGERIAL', 3);

class FlexibleRole extends DataObject {

	var $series;

	/**
	 * Constructor.
	 */
	function FlexibleRole() {
		parent::DataObject();
		$this->series = array();
	}

	/**
	 * Set the name of the role
	 * @param $name string
	 * @param $locale string
	 */
	function setName($name, $locale) {
		$this->setData('name', $name, $locale);
	}

	/**
	 * Get the name of the role
	 * @param $locale string
	 * @return string
	 */
	function getName($locale) {
		return $this->getData('name', $locale);
	}

	/**
	 * Get the localized name of the role
	 * @return string
	 */
	function getLocalizedName() {
		return $this->getLocalizedData('name');
	}

	/**
	 * Set the role's designation
	 * @param $designation string
	 * @param $locale string
	 */
	function setDesignation($designation, $locale) {
		$this->setData('designation', $designation, $locale);
	}

	/**
	 * Get the role's designation
	 * @param $locale string
	 * @return string
	 */
	function getDesignation($locale) {
		return $this->getData('designation', $locale);
	}

	/**
	 * Get the role's localized designation
	 * @return string
	 */
	function getLocalizedDesignation() {
		return $this->getLocalizedData('designation');
	}

	/**
	 * Set the plural name of the role
	 * @param $name string
	 * @param $locale string
	 */
	function setPluralName($name, $locale) {
		$this->setData('pluralName', $name, $locale);
	}

	/**
	 * Get the plural name of the role
	 * @param $locale string
	 * @return string
	 */
	function getPluralName($locale) {
		return $this->getData('pluralName', $locale);
	}

	/**
	 * Get the localized plural name of the role
	 * @return string
	 */
	function getLocalizedPluralName() {
		$pluralName = $this->getLocalizedData('pluralName');
		if (!$pluralName || trim($pluralName) == '') {
			return $this->getLocalizedData('name');
		}
		return $pluralName;
	}

	/**
	 * Get the role's path
	 * @return string
	 */
	function getPath() {
		return $this->getData('customRole') ? 'role' : $this->getData('path');
	}

	/**
	 * Set the role's path
	 * @param $path string
	 */
	function setPath($path) {
		return $this->setData('path', $path);
	}

	/**
	 * Get the role's constant identifier
	 * @return string
	 */
	function getRoleId() {
		return $this->getData('roleId');
	}

	/**
	 * Set the role's constant identifier
	 * @param $roleId int
	 */
	function setRoleId($roleId) {
		return $this->setData('roleId', $roleId);
	}

	/**
	 * Get enabled flag of the role
	 * @return int
	 */
	function getEnabled() {
		return $this->getData('enabled');
	}

	/**
	 * Set enabled flag of the role
	 * @param $enabled int
	 */
	function setEnabled($enabled) {
		return $this->setData('enabled',$enabled);
	}

	/**
	 * Get the role type
	 * @return int
	 */
	function getType() {
		return $this->getData('type');
	}

	/**
	 * Set the role type
	 * @param $roleType int
	 */
	function setType($roleType) {
		$this->setData('type', $roleType);
	}

	/**
	 * Return the press id.
	 * @return string
	 */
	function getPressId() {
		return $this->getData('pressId');
	}

	/**
	 * Set the press id.
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		return $this->setData('pressId', $pressId);
	}

	/**
	 * Determine whether or not this is a custom role	.
	 * @return bool
	 */
	function isCustomRole() {
		return $this->getData('customRole') ? true : false;
	}

	/**
	 * Set the 'is custom role' the value.
	 * @param $customRole bool
	 */
	function setCustomRole($customRole) {
		return $this->setData('customRole', $customRole);
	}

	/**
	 * Return the associated workflow points for this role.
	 * @return array workflow ids
	 */
	function getAssociatedSeries() {
		return $this->series;
	}

	/**
	 * Reset the associated series array.
	 */
	function clearAssociatedSeries() {
		$this->series = array();
	}

	/**
	 * Associate a workflow point with this role.
	 * @param $seriesId int
	 */
	function addAssociatedSeries($seriesId) {
		if (!in_array($seriesId, $this->series)) {
			array_push($this->series, $seriesId);
		}
	}

	/**
	 * Remove a workflow point from this role.
	 * @param $seriesId int
	 */
	function removeAssociatedSeries($seriesId) {
		$key = array_search($seriesId, $this->series);

		if (isset($key)) {
			unset($this->series[$key]);
		}
	}
}

?>