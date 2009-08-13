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

	var $authors;

	/**
	 * Constructor.
	 */
	function MonographComponent() {
		parent::DataObject();
		$this->authors = array();
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

	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}
	function setTitle($title) {
		$this->setData('title', $title);
	}
	function getTitle($locale) {
		return $this->getData('title', $locale);
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
