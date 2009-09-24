<?php

/**
 * @defgroup role
 */
 
/**
 * @file classes/role/FlexibleRole.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FlexibleRole
 * @ingroup role
 * @see FlexibleRoleDAO
 *
 * @brief Basic class describing a flexible role.
 */

define('FLEXIBLE_ROLE_ARRANGEMENT_SUBMISSION',		1);
define('FLEXIBLE_ROLE_ARRANGEMENT_INTERNAL_REVIEW',	2);
define('FLEXIBLE_ROLE_ARRANGEMENT_EXTERNAL_REVIEW',	3);
define('FLEXIBLE_ROLE_ARRANGEMENT_EDITORIAL',		4);
define('FLEXIBLE_ROLE_ARRANGEMENT_PRODUCTION',		5);

define('FLEXIBLE_ROLE_CLASS_AUTHOR',	1);
define('FLEXIBLE_ROLE_CLASS_PRESS',	2);

class FlexibleRole extends DataObject {

	var $arrangements;

	/**
	 * Constructor.
	 */
	function FlexibleRole() {
		parent::DataObject();
		$this->arrangements = array();
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
		return $this->getLocalizedSetting('name');	
	}

	/**
	 * Set the abbreviation of the role
	 * @param $abbrev string
	 * @param $locale string
	 */
	function setAbbrev($abbrev, $locale) {
		$this->setData('abbrev', $abbrev, $locale);
	}
	
	/**
	 * Get the abbreviation of the role
	 * @param $locale string
	 * @return string
	 */
	function getAbbrev($locale) {
		return $this->getData('abbrev', $locale);	
	}

	/**
	 * Get the localized abbreviation of the role
	 * @return string
	 */
	function getLocalizedAbbrev() {
		return $this->getLocalizedSetting('abbrev');	
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
	 * Return the associated workflow points for this role.
	 * @return array workflow ids
	 */
	function getAssociatedArrangements() {
		return $this->arrangements;
	}

	/**
	 * Reset the associated arrangements array.
	 */
	function clearAssociatedArrangements() {
		$this->arrangements = array();
	}

	/**
	 * Associate a workflow point with this role.
	 * @param $pressId int
	 */
	function addAssociatedArrangement($arrangementId) {
		if (!in_array($arrangementId, $this->arrangements)) {
			array_push($this->arrangements, $arrangementId);
		}
	}
}

?>