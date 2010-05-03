<?php

/**
 * @file classes/monograph/ArtworkFile.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArtworkFile
 * @ingroup monograph
 * @see ArtworkFileDAO
 *
 * @brief Artwork file class.
 */

// $Id$

define('MONOGRAPH_ARTWORK_TYPE_OTHER',		1);
define('MONOGRAPH_ARTWORK_TYPE_TABLE',		2);
define('MONOGRAPH_ARTWORK_TYPE_FIGURE',		3);

define('MONOGRAPH_ARTWORK_PLACEMENT_OTHER',		1);
define('MONOGRAPH_ARTWORK_PLACEMENT_BY_CHAPTER',	2);

import('classes.monograph.MonographFile');

class ArtworkFile extends MonographFile {

	/** @var array */
	var $_imageInfo;

	/** @var MonographFile */
	var $_monographFile;

	/** @var PermissionFile */
	var $_permissionFile;

	function ArtworkFile() {
		parent::MonographFile();

		$this->_imageInfo = null;
		$this->_monographFile = null;
		$this->_permissionFile = null;
	}

	//
	// Get/set methods
	//

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
	function getArtworkType() {
		return $this->getData('artworkType');
	}

	/**
	 * Set the artwork type.
	 * @param $typeId int
	 */
	function setArtworkType($typeId) {
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
	 * Get the width of the image in pixels.
	 */
	function getWidth() {
		if (!$this->_imageInfo) {
			$monographFile =& $this->getFile();
			$this->_imageInfo = getimagesize($monographFile->getFilePath());
		}
		return $this->_imageInfo[0];
	}

	/**
	 * Get the height of the image in pixels.
	 */
	function getHeight() {
		if (!$this->_imageInfo) {
			$monographFile =& $this->getFile();
			$this->_imageInfo = getimagesize($monographFile->getFilePath());
		}
		return $this->_imageInfo[1];
	}

	/**
	 * Get the artwork file object.
	 * @return MonographFile
	 */
	function &getFile() {
		if (!$this->_monographFile) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFile =& $monographFileDao->getMonographFile($this->getData('fileId'), $this->getData('revision'));
			$this->_monographFile =& $monographFile;
		}
		return $this->_monographFile;
	}

	/**
	 * Get the file object.
	 * @return MonographFile
	 */
	function &getPermissionFile() {
		if (!$this->_permissionFile) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFile =& $monographFileDao->getMonographFile($this->getData('permissionFileId'));
			$this->_permissionFile =& $monographFile;
		}
		return $this->_permissionFile;
	}
}

?>
