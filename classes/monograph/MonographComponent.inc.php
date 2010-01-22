<?php

/**
 * @file classes/monograph/MonographComponent.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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

	var $authors;
	var $assocObjects;

	/**
	 * Constructor.
	 */
	function MonographComponent() {
		parent::DataObject();
		$this->authors = array();
		$this->assocObjects = array();
	}

	/**
	 * Add a component author.
	 * @param $author Author
	 */
	function addAuthor($author) {
		if ($author->getSequence() == null) {
			$author->setSequence(count($this->authors) + 1);
		}
		array_push($this->authors, $author);
	}

	/**
	 * Get a specific author of this component.
	 * @param $authorId int
	 * @return array Authors
	 */
	function &getAuthor($authorId) {
		$author = null;

		if ($authorId != 0) {
			for ($i=0, $count=count($this->authors); $i < $count && $author == null; $i++) {
				if ($this->authors[$i]->getId() == $authorId) {
					$author =& $this->authors[$i];
				}
			}
		}
		return $author;
 	}

	/**
	 * Set authors of this submission.
	 * @param $authors array Authors
	 */
	function setAuthors($authors) {
		return $this->authors = $authors;
 	}

 	/**
	 * Get all authors of this submission.
	 * @return array Authors
 	 */
	function &getAuthors() {
		return $this->authors;
 	}

	/**
	 * Get the localized component title.
	 * @return string
	 */
	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * Set the monograph component title.
	 * @param $title string
	 * @param $locale string
	 */
	function setTitle($title, $locale = null) {
		$this->setData('title', $title, $locale);
	}

	/**
	 * Get the component title.
	 * @return string
	 */
	function getTitle($locale) {
		return $this->getData('title', $locale);
	}

	/**
	 * Set the monograph id.
	 * @param $id int
	 */
	function setMonographId($id) {
		$this->setData('monograph_id', $id);
	}

	/**
	 * Get the monograph id.
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monograph_id');
	}

	/**
	 * Set the primary contact.
	 * @param $contact int
	 */
	function setPrimaryContact($contact) {
		$this->setData('primary_contact', $contact);
	}

	/**
	 * Get primary contact.
	 * @return int
	 */
	function getPrimaryContact() {
		return $this->getData('primary_contact');
	}

	/**
	 * Get placement value.
	 * @return int
	 */
	function getSequence() {
		return $this->getData('seq');
	}

	/**
	 * Set placement value.
	 * @param $seq int
	 */
	function setSequence($seq) {
		$this->setData('seq', $seq);
	}

	/**
	 * Set associated objects.
	 * @param $assocObjects array Object
	 */
	function setAssocObjects($assocObjects) {
		return $this->assocObjects = $assocObjects;
	}

	/**
	 * Get all associated objects.
	 * @return array Object
	 */
	function &getAssocObjects() {
		return $this->assocObjects;
	}

	/**
	 * Add an associated object.
	 * @param $object Object
	 */
	function addAssocObject($object) {
		array_push($this->assocObjects, $object);
	}
}

?>
