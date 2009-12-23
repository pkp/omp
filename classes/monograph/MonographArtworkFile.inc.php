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

define('MONOGRAPH_ARTWORK_TYPE_OTHER',		1);
define('MONOGRAPH_ARTWORK_TYPE_TABLE',		2);
define('MONOGRAPH_ARTWORK_TYPE_FIGURE',		3);

define('MONOGRAPH_ARTWORK_PLACEMENT_OTHER',		1);
define('MONOGRAPH_ARTWORK_PLACEMENT_BY_CHAPTER',	2);

class MonographArtworkFile extends DataObject {

	//
	// Get/set methods
	//

	/**
	 * Get artwork file id.
	 * @return int
	 */
	function getFileId() {
		return $this->getData('fileId');
	}

	/**
	 * Set artwork file id.
	 * @param $fileId int
	 */
	function setFileId($fileId) {
		return $this->setData('fileId', $fileId);
	}

	/**
	 * Get the current monograph id.
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	 * Set the current monograph id.
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}

	/**
	 * Get artwork caption.
	 * @return string
	 */
	function getCaption() {
		return $this->getData('caption');
	}

	/**
	 * Set artwork caption.
	 * @param $caption string
	 */
	function setCaption($caption) {
		return $this->setData('caption', $caption);
	}

	/**
	 * Get the credit.
	 * @return string
	 */
	function getCredit() {
		return $this->getData('credit');
	}

	/**
	 * Set the credit.
	 * @param $credit string
	 */
	function setCredit($credit) {
		return $this->setData('credit', $credit);
	}

	/**
	 * Get the copyright owner.
	 * @return string
	 */
	function getCopyrightOwner() {
		return $this->getData('copyrightOwner');
	}

	/**
	 * Set the copyright owner.
	 * @param $owner string
	 */
	function setCopyrightOwner($owner) {
		return $this->setData('copyrightOwner', $owner);
	}

	/**
	 * Get contact details for the copyright owner.
	 * @return string
	 */
	function getCopyrightOwnerContactDetails() {
		return $this->getData('copyrightOwnerContact');
	}

	/**
	 * Set the contact details for the copyright owner.
	 * @param $contactDetails string
	 */
	function setCopyrightOwnerContactDetails($contactDetails) {
		return $this->setData('copyrightOwnerContact', $contactDetails);
	}

	/**
	 * Get the permission terms.
	 * @return string
	 */
	function getPermissionTerms() {
		return $this->getData('terms');
	}

	/**
	 * Set the permission terms.
	 * @param $terms string
	 */
	function setPermissionTerms($terms) {
		return $this->setData('terms', $terms);
	}

	/**
	 * Get the permission form file id.
	 * @return int
	 */
	function getPermissionFileId() {
		return $this->getData('permissionFileId');
	}

	/**
	 * Set the permission form file id.
	 * @param $fileId int
	 */
	function setPermissionFileId($fileId) {
		return $this->setData('permissionFileId', $fileId);
	}

	/**
	 * Get the placement note for the artwork.
	 * @return string
	 */
	function getPlacement() {
		return $this->getData('placement');
	}

	/**
	 * Set the placement note for the artwork.
	 * @param $placement string
	 */
	function setPlacement($placement) {
		return $this->setData('placement', $placement);
	}

	/**
	 * Get the monograph component id.
	 * @return int
	 */
	function getComponentId() {
		return $this->getData('componentId');
	}

	/**
	 * Set the monograph component id.
	 * @param $componentId int
	 */
	function setComponentId($componentId) {
		return $this->setData('componentId', $componentId);
	}

	/**
	 * Get the artwork type.
	 * @return int
	 */
	function getType() {
		return $this->getData('artworkType');
	}

	/**
	 * Set the artwork type.
	 * @param $typeId int
	 */
	function setType($typeId) {
		return $this->setData('artworkType', $typeId);
	}

	/**
	 * Get the descriptive name for the artwork type.
	 * @return string
	 */
	function getCustomType() {
		return $this->getData('customArtworkType');
	}

	/**
	 * Set a descriptive name for the artwork type.
	 * @param $type string
	 */
	function setCustomType($type) {
		return $this->setData('customArtworkType', $type);
	}

	/**
	 * Get the contact author's id.
	 * @return int
	 */
	function getContactAuthor() {
		return $this->getData('contactAuthor');
	}

	/**
	 * Set the contact author's id.
	 * @param $authorId int
	 */
	function setContactAuthor($authorId) {
		return $this->setData('contactAuthor', $authorId);
	}

	/**
	 * Get the artwork file object.
	 * @return MonographFile
	 */
	function &getFile() {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($this->getData('fileId'), $this->getData('revision'));
		return $monographFile;
	}

	/**
	 * Get the file object.
	 * @return MonographFile
	 */
	function &getPermissionFile() {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($this->getData('permissionFileId'));
		return $monographFile;
	}
}

?>
