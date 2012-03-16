<?php

/**
 * @file classes/press/FooterLinkDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FooterLinkDAO
 * @ingroup press
 * @see FooterLink
 *
 * @brief Operations for retrieving and modifying FooterLink objects.
 */


import ('classes.press.FooterLink');

class FooterLinkDAO extends DAO {
	/**
	 * Constructor
	 */
	function FooterLinkDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a footer link by ID.
	 * @param $footerLinkId int
	 * @param $pressId int optional
	 * @return FooterLink
	 */
	function &getById($footerLinkId, $pressId = null) {
		$params = array((int) $footerLinkId);
		if ($pressId) $params[] = (int) $pressId;

		$result =& $this->retrieve(
			'SELECT	*
			FROM	footerlinks
			WHERE	footerlink_id = ?
			' . ($pressId?' AND press_id = ?':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return FooterLink
	 */
	function newDataObject() {
		return new FooterLink();
	}

	/**
	 * Internal function to return an FooterLink object from a row.
	 * @param $row array
	 * @return FooterLink
	 */
	function _fromRow(&$row) {
		$footerLink = $this->newDataObject();

		$footerLink->setId($row['footerlink_id']);
		$footerLink->setPressId($row['press_id']);
		$footerLink->setUrl($row['url']);
		$footerLink->setCategoryId($row['footer_category_id']);

		$this->getDataObjectSettings('footerlink_settings', 'footerlink_id', $row['footerlink_id'], $footerLink);

		HookRegistry::call('FooterLinkDAO::_fromRow', array(&$footerLink, &$row));

		return $footerLink;
	}

	/**
	 * Get the list of fields for which data can be localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title');
	}

	/**
	 * Update the localized fields for this table
	 * @param $footerLink object
	 */
	function updateLocaleFields(&$footerLink) {
		$this->updateDataObjectSettings(
			'footerlink_settings', $footerLink,
			array(
				'footerlink_id' => $footerLink->getId()
			)
		);
	}

	/**
	 * Insert a new footer link.
	 * @param $footerLink FooterLink
	 * @return int ID of the inserted link.
	 */
	function insertObject(&$footerLink) {
		$this->update(
			'INSERT INTO footerlinks
				(press_id, footer_category_id, url)
				VALUES
				(?, ?, ?)',
			array(
				(int) $footerLink->getPressId(),
				(int) $footerLink->getCategoryId(),
				$footerLink->getUrl()
			)
		);

		$footerLink->setId($this->getInsertFooterLinkId());
		$this->updateLocaleFields($footerLink);
		return $footerLink->getId();
	}

	/**
	 * Update an existing link.
	 * @param $footerLink FooterLink
	 */
	function updateObject($footerLink) {
		$returner = $this->update(
			'UPDATE	footerlinks
			SET	press_id = ?,
				footer_category_id = ?,
				url = ?
			WHERE	footerlink_id = ?',
			array(
				(int) $footerLink->getPressId(),
				(int) $footerLink->getCategoryId(),
				$footerLink->getUrl(),
				(int) $footerLink->getId()
			)
		);
		$this->updateLocaleFields($footerLink);
		return $returner;
	}

	/**
	 * Delete a link.
	 * @param $footerLink FooterLink
	 */
	function deleteObject(&$footerLink) {
		return $this->deleteById(
			$footerLink->getId(),
			$footerLink->getPressId()
		);
	}

	/**
	 * Delete a footer link by ID.
	 * @param $footerLinkId int
	 * @param $pressId int optional
	 */
	function deleteById($footerLinkId, $pressId = null) {
		$params = array((int) $footerLinkId);
		if ($pressId) $params[] = (int) $pressId;

		$this->update(
			'DELETE FROM footerlinks
			WHERE footerlink_id = ?
				' . ($pressId?' AND press_id = ?':''),
			$params
		);

		// If the link was deleted (this validates press_id,
		// if specified), delete any associated settings as well.
		if ($this->getAffectedRows()) {
			return $this->update(
				'DELETE FROM footerlink_settings WHERE footerlink_id = ?',
				array((int) $footerLinkId)
			);
		}
	}

	/**
	 * Delete footer link by press ID.
	 * NOTE: This does not delete dependent entries. It is intended
	 * to be called only when deleting a press.
	 * @param $pressId int
	 */
	function deleteByPressId($pressId) {
		$footerlinks =& $this->getByPressId($pressId);
		while ($footerLink =& $footerlinks->next()) {
			$this->deleteObject($footerLink, $pressId);
			unset($footerLink);
		}
	}

	/**
	 * Retrieve all footerlinks for a footer category.
	 * @return DAOResultFactory containing FooterLink objects
	 */
	function &getByCategoryId($categoryId, $pressId = null, $rangeInfo = null) {
		$params = array((int) $categoryId);
		if ($pressId) $params[] = (int) $pressId;

		$result =& $this->retrieveRange(
			'SELECT	*
			FROM	footerlinks
			WHERE	footer_category_id = ?
			' . ($pressId?' AND press_id = ?':''),
			$params
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get the ID of the last inserted link.
	 * @return int
	 */
	function getInsertFooterLinkId() {
		return $this->getInsertId('footerlinks', 'footerlink_id');
	}
}
?>
