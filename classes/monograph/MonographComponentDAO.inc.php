<?php

/**
 * @file classes/monograph/MonographComponentDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographComponentDAO
 * @ingroup monograph
 * @see MonographComponent
 *
 * @brief Operations for retrieving and modifying MonographComponent objects.
 */

// $Id$


import('monograph.Author');
import('monograph.Monograph');

class MonographComponentDAO extends DAO {
	/**
	 * Retrieve an author by ID.
	 * @param $authorId int
	 * @return Author
	 */
	function &getMonographComponents($monographId) {
		$result =& $this->retrieve(
			'SELECT * 
			FROM monograph_components WHERE monograph_id = ?', $monographId
		);

		$returner = array();

		while (!$result->EOF) {
			$returner[] =& $this->_returnMonographComponentFromRow($result->GetRowAssoc(false));;
			$result->moveNext();
		}

		$result->Close();
		unset($result);
		
		return $returner;
	}

	/**
	 * Retrieve the IDs of all authors for an monograph.
	 * @param $monographId int
	 * @return array int ordered by sequence
	 */
	function &getMonographComponentIdsByMonographId($monographId) {
		$componentIds = array();

		$result =& $this->retrieve(
			'SELECT component_id FROM monograph_components WHERE monograph_id = ? ORDER BY seq',
			$monographId
		);

		while (!$result->EOF) {
			$componentIds[] = $result->fields[0];
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $componentIds;
	}

	/**
	 * Get field names for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title');
	}

	/**
	 * Update the localized data for this object
	 * @param $author object
	 */
	function updateLocaleFields(&$component) {
		$this->updateDataObjectSettings('monograph_component_settings', $component, array(
			'component_id' => $component->getMonographComponentId()
		));

	}

	/**
	 * Internal function to return a MonographComponent object from a row.
	 * @param $row array
	 * @return MonographComponent
	 */
	function &_returnMonographComponentFromRow(&$row) {
		import('monograph.MonographComponent');
		$component =& new MonographComponent();
		$component->setMonographComponentId($row['component_id']);
		$component->setMonographId($row['monograph_id']);
		$component->setSequence($row['seq']);
		$component->setPrimaryContact($row['contact_author']);

		$componentAuthors = $this->getAuthorsByMonographComponent($row['component_id']);
		
		$component->setMonographComponentAuthors($componentAuthors);
		$this->getDataObjectSettings('monograph_component_settings', 'component_id', $row['component_id'], $component);

		HookRegistry::call('MonographComponentDAO::_returnMonographComponentFromRow', array(&$component, &$row));

		return $component;
	}

	function updateMonographComponent($component) {
		$returner = $this->update(
			'UPDATE monograph_components
				SET
					monograph_id = ?,
					seq = ? 
					WHERE component_id = ?',
			array(
				$component->getMonographId(),
				$component->getSequence(),
				$component->getMonographComponentId()
			)
		);
		$this->updateLocaleFields($component);
		
		$this->update('DELETE FROM monograph_component_authors WHERE monograph_id = ? AND component_id = ?',
				array(
					$component->getMonographId(),
					$component->getMonographComponentId()
				)
			);
		
		$componentAuthors = $component->getMonographComponentAuthors();

		for ($i=0,$count=count($componentAuthors);$i < $count;$i++) {
			$this->_insertMonographComponentAuthor(
				$component->getMonographComponentId(), 
				$componentAuthors[$i]->getId(), 
				$component->getMonographId(),
				$i+1
			);
		}
		return $returner;
	}
	function _insertMonographComponentAuthor($componentId, $authorId, $monographId, $seq) {

		$this->update(
			'INSERT INTO monograph_component_authors
				(component_id, author_id, monograph_id, seq)
				VALUES
				(?, ?, ?, ?)',
				array(
					$componentId,
					$authorId,
					$monographId,
					$seq
				)
			);
	}
	function deleteMonographComponentById($componentId) {
		$returner = $this->update(
			'DELETE FROM monograph_components WHERE component_id = ?', $componentId
		);
	}
	function insertMonographComponent($component) {

		$this->update(
			'INSERT INTO monograph_components
				(monograph_id, contact_author, seq)
				VALUES
				(?, ?, ?)',
			array(
				$component->getMonographId(),
				$component->getPrimaryContact(),
				$component->getSequence()
			));
		
		$component->setMonographComponentId($this->getInsertMonographComponentId());
		$componentAuthors = $component->getMonographComponentAuthors();

		for ($i=0,$count=count($componentAuthors);$i<$count;$i++) {
			$this->_insertMonographComponentAuthor(
				$this->getInsertMonographComponentId(), 
				$componentAuthors[$i]->getId(),
				$component->getMonographId(), 
				$i+1
			);		
		}
		$this->updateLocaleFields($component);
	}
	/**
	 * Retrieve all authors for a component.
	 * @param $componentId int
	 * @return array Authors ordered by sequence
	 */
	function &getAuthorsByMonographComponent($componentId) {
		$authors = array();
		$result =& $this->retrieve(
			'SELECT mca.author_id FROM monograph_component_authors mca 
			WHERE mca.component_id = ? ORDER BY mca.seq',
			$componentId
		);
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		while (!$result->EOF) {
			$authors[] =& $authorDao->getAuthor($result->fields[0]);
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $authors;
	}
	/**
	 * Delete an author by ID.
	 * @param $authorId int
	 * @param $monographId int optional
	 */
	function deleteComponentAuthorById($authorId, $componentId, $monographId) {
		$params = array($authorId);
		if ($monographId) $params[] = $monographId;
		$returner = $this->update(
			'DELETE FROM monograph_component_authors WHERE author_id = ?' .
			($monographId?' AND monograph_id = ?':''),
			$params
		);
		if ($returner) $this->update('DELETE FROM monograph_author_settings WHERE author_id = ?', array($authorId));
	}

	/**
	 * Get the ID of the last inserted author.
	 * @return int
	 */
	function getInsertMonographComponentId() {
		return $this->getInsertId('monograph_components', 'component_id');
	}
}

?>
