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
	 * Get source file ID of this file.
	 * @return int
	 */
	function getPermission() {
		return $this->getData('permission');
	}

	/**
	 * Set source file ID of this file.
	 * @param $sourceFileId int
	 */
	function setPermission($permission) {
		return $this->setData('permission', $permission);
	}

	/**
	 * Get source revision of this file.
	 * @return int
	 */
	function getPermissionFileId() {
		return $this->getData('permissionFileId');
	}

	/**
	 * Set source revision of this file.
	 * @param $sourceRevision int
	 */
	function setPermissionFileId($permissionFileId) {
		return $this->setData('permissionFileId', $permissionFileId);
	}

	/**
	 * Get associated ID of file. (Used, e.g., for email log attachments.)
	 * @return int
	 */
	function getMonographComponentId() {
		return $this->getData('componentId');
	}

	/**
	 * Set associated ID of file. (Used, e.g., for email log attachments.)
	 * @param $assocId int
	 */
	function setMonographComponentId($componentId) {
		return $this->setData('componentId', $componentId);
	}

	/**
	 * Get associated ID of file. (Used, e.g., for email log attachments.)
	 * @return int
	 */
	function getIdentifier() {
		return $this->getData('identifier');
	}

	/**
	 * Set associated ID of file. (Used, e.g., for email log attachments.)
	 * @param $assocId int
	 */
	function setIdentifier($identifier) {
		return $this->setData('identifier', $identifier);
	}

	/**
	 * Get associated ID of file. (Used, e.g., for email log attachments.)
	 * @return int
	 */
	function getSeq() {
		return $this->getData('seq');
	}

	/**
	 * Set associated ID of file. (Used, e.g., for email log attachments.)
	 * @param $assocId int
	 */
	function setSeq($seq) {
		return $this->setData('seq', $seq);
	}

}

?>
