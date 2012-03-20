<?php

/**
 * @file classes/press/FooterCategoryDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FooterCategoryDAO
 * @ingroup press
 * @see FooterCategory
 *
 * @brief Operations for retrieving and modifying FooterCategory objects.
 */


import ('classes.press.FooterCategory');

class FooterCategoryDAO extends DAO {
	/**
	 * Constructor
	 */
	function FooterCategoryDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a category by ID.
	 * @param $categoryId int
	 * @param $pressId int optional
	 * @return FooterCategory
	 */
	function &getById($categoryId, $pressId = null) {
		$params = array((int) $categoryId);
		if ($pressId) $params[] = (int) $pressId;

		$result =& $this->retrieve(
			'SELECT *
			FROM footer_categories
			WHERE footer_category_id = ?
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
	 * Retrieve a category by path.
	 * @param $path string
	 * @param $pressId int
	 * @return FooterCategory
	 */
	function &getByPath($path, $pressId) {
		$returner = null;
		$result =& $this->retrieve(
			'SELECT * FROM footer_categories WHERE path = ? AND press_id = ?',
			array((string) $path, (int) $pressId)
		);

		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Check if a category exists with a specified path.
	 * @param $path the path for the category
	 * @return boolean
	 */
	function categoryExistsByPath($path) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM categories WHERE path = ?', $path
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return FooterCategory
	 */
	function newDataObject() {
		return new FooterCategory();
	}

	/**
	 * Internal function to return a FooterCategory object from a row.
	 * @param $row array
	 * @return FooterCategory
	 */
	function _fromRow(&$row) {
		$category = $this->newDataObject();

		$category->setId($row['footer_category_id']);
		$category->setPressId($row['press_id']);
		$category->setPath($row['path']);

		$this->getDataObjectSettings('footer_category_settings', 'footer_category_id', $row['footer_category_id'], $category);

		HookRegistry::call('FooterCategoryDAO::_fromRow', array(&$category, &$row));

		return $category;
	}

	/**
	 * Get the list of fields for which data can be localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'description');
	}

	/**
	 * Update the localized fields for this table
	 * @param $category object
	 */
	function updateLocaleFields(&$category) {
		$this->updateDataObjectSettings(
			'footer_category_settings', $category,
			array(
				'footer_category_id' => $category->getId()
			)
		);
	}

	/**
	 * Insert a new category.
	 * @param $category FooterCategory
	 * @return int ID of the inserted category.
	 */
	function insertObject(&$category) {
		$this->update(
			'INSERT INTO footer_categories
				(press_id, path)
				VALUES
				(?, ?)',
			array(
				(int) $category->getPressId(),
				$category->getPath()
			)
		);

		$category->setId($this->getInsertFooterCategoryId());
		$this->updateLocaleFields($category);
		return $category->getId();
	}

	/**
	 * Update an existing category.
	 * @param $category FooterCategory
	 */
	function updateObject($category) {
		$returner = $this->update(
			'UPDATE footer_categories
			SET	press_id = ?,
				path = ?
			WHERE footer_category_id = ?',
			array(
				(int) $category->getPressId(),
				$category->getPath(),
				(int) $category->getId()
			)
		);
		$this->updateLocaleFields($category);
		return $returner;
	}

	/**
	 * Delete a category.
	 * @param $category FooterCategory
	 */
	function deleteObject(&$category) {
		return $this->deleteById(
			$category->getId(),
			$category->getPressId()
		);
	}

	/**
	 * Delete a category by ID.
	 * @param $categoryId int
	 * @param $pressId int optional
	 */
	function deleteById($categoryId, $pressId = null) {
		$params = array((int) $categoryId);
		if ($pressId) $params[] = (int) $pressId;

		$this->update(
			'DELETE FROM footer_categories
			WHERE footer_category_id = ?
				' . ($pressId?' AND press_id = ?':''),
			$params
		);

		// If the category was deleted (this validates press_id,
		// if specified), delete any associated settings as well.
		if ($this->getAffectedRows()) {
			$this->update(
				'DELETE FROM footer_category_settings WHERE footer_category_id = ?',
				array((int) $categoryId)
			);

			return true;
		}
	}

	/**
	 * Delete category by press ID
	 * NOTE: This does not delete dependent entries. It is intended
	 * to be called only when deleting a press.
	 * @param $pressId int
	 */
	function deleteByPressId($pressId) {
		$categories =& $this->getByPressId($pressId);
		while ($category =& $categories->next()) {
			$this->deleteObject($category, $pressId);
			unset($category);
		}
	}

	/**
	 * Retrieve all categories for a press.
	 * @return DAOResultFactory containing FooterCategory ordered by sequence
	 */
	function &getByPressId($pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT *
			FROM footer_categories
			WHERE press_id = ?',
			array((int) $pressId)
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve the number of categories for a press.
	 * @return int
	 */
	function &getCountByPressId($pressId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*)
			FROM footer_categories
			WHERE press_id = ?',
			(int) $pressId
		);

		$returner = $result->fields[0];

		$result->Close();
		unset($result);
		return $returner;
	}

	/**
	 * Get the ID of the last inserted category.
	 * @return int
	 */
	function getInsertFooterCategoryId() {
		return $this->getInsertId('footer_categories', 'footer_category_id');
	}
}

?>
