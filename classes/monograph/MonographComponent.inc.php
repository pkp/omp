<?php

/**
 * @file classes/monograph/MonographComponent.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographComponent
 * @ingroup monograph
 * @see MonographComponentDAO
 *
 * @brief The monograph component class represents a division in a monograph (ie: intro, chapter, preface, etc)
 */

// $Id$

class MonographComponent extends DataObject {

	function MonographComponent() {
		parent::DataObject();
	}
	function &getMonographComponentAuthors() {
		$returner = $this->getData('authors');
		if (isset($returner))
			return $returner;
		$returner = array();
		return $returner;
	}
	function addMonographComponentAuthor($author) {
		$authors = $this->getData('authors');
		if ($authors == null)
			$authors = array();
		array_push($authors, $author);
		$this->setData('authors', $authors);
	}
	/**
	 * Set this monograph component's author list to an array with elements of type Author.
	 *
	 * @param array $authors
	 */
	function setMonographComponentAuthors($authors) {
		$this->setData('authors',$authors);
	}

	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}
	function setMonographComponentTitle($title, $locale) {
		return $this->setData('title', $title, $locale);
	}
	function setTitle($title) {
		$this->setData('title', $title);
	}
	function getTitle($locale) {
		return $this->getData('title', $locale);
	}
	function getMonographComponentId() {
		return $this->getData('component_id');
	}
	function setMonographComponentId($id) {
		$this->setData('component_id', $id);
	}
	function setMonographId($id) {
		$this->setData('monograph_id', $id);
	}
	function getMonographId() {
		return $this->getData('monograph_id');
	}
	function setPrimaryContact($contact) {
		$this->setData('primary_contact', $contact);
	}
	function getPrimaryContact() {
		return $this->getData('primary_contact');
	}
	function getSequence() {
		return $this->getData('seq');
	}
	function setSequence($seq) {
		$this->setData('seq', $seq);
	}

}

?>
