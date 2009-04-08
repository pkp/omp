<?php

/**
 * @file classes/monograph/MonographFile.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFile
 * @ingroup monograph
 * @see MonographFileDAO
 *
 * @brief Monograph file class.
 */

// $Id$

import('monograph.MonographFile');

class MonographArtworkFile extends MonographFile {

	//
	// Get/set methods
	//

	/**
	 * Get permission check.
	 * @return int
	 */
	function getPermission() {
		return $this->getData('permission');
	}

	/**
	 * Set permission check.
	 * @param $permission int
	 */
	function setPermission($permission) {
		return $this->setData('permission', $permission);
	}

	/**
	 * Get permissions file id.
	 * @return int
	 */
	function getPermissionFileId() {
		return $this->getData('permissionFileId');
	}

	/**
	 * Set permissions file id.
	 * @param $permissionFileId int
	 */
	function setPermissionFileId($permissionFileId) {
		return $this->setData('permissionFileId', $permissionFileId);
	}

	/**
	 * Get the component Id.
	 * @return int
	 */
	function getMonographComponentId() {
		return $this->getData('componentId');
	}

	/**
	 * Set the component Id.
	 * @param $componentId int
	 */
	function setMonographComponentId($componentId) {
		return $this->setData('componentId', $componentId);
	}

	/**
	 * Get the component Id.
	 * @return int
	 */
	function getMonographComponentTitle() {
		return $this->getData('componentTitle');
	}

	/**
	 * Set the component Id.
	 * @param $componentId int
	 */
	function setMonographComponentTitle($componentTitle) {
		return $this->setData('componentTitle', $componentTitle);
	}

	/**
	 * Get artwork placement identifier.
	 * @return int
	 */
	function getIdentifier() {
		return $this->getData('identifier');
	}

	/**
	 * Set artwork placement identifier.
	 * @param $assocId int
	 */
	function setIdentifier($identifier) {
		return $this->setData('identifier', $identifier);
	}

	/**
	 * Get artwork sequencing info.
	 * @return int
	 */
	function getSeq() {
		return $this->getData('seq');
	}

	/**
	 * Set artwork sequencing info
	 * @param $seq int
	 */
	function setSeq($seq) {
		return $this->setData('seq', $seq);
	}

}

?>
