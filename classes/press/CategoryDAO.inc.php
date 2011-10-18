<?php

/**
 * @file classes/press/CategoryDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryDAO
 * @ingroup press
 * @see Category
 *
 * @brief Operations for retrieving and modifying Category objects.
 */



import ('classes.press.Category');

class CategoryDAO extends DAO {
	/**
	 * Retrieve an category by ID.
	 * @param $categoryId int
	 * @param $pressId int optional
	 * @param $parentId int optional
	 * @return Category
	 */
	function &getById($categoryId, $pressId = null, $parentId = null) {
		$sql = 'SELECT * FROM categories WHERE category_id = ?';
		$params = array((int) $categoryId);
		if ($pressId) $params[] = (int) $pressId;
		if ($parentId) $params[] = (int) $parentId;

		$result =& $this->retrieve(
			'SELECT	*
			FROM	categories
			WHERE	category_id = ?
			' . ($pressId?' AND press_id = ?':'') . '
			' . ($parentId?' AND parent_id = ?':''),
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
	 * Retrieve an category by title.
	 * @param $categoryTitle string
	 * @param $pressId int
	 * @param $locale string optional
	 * @return Category
	 */
	function &getByTitle($categoryTitle, $pressId, $locale = null) {
		$params = array('title', $categoryTitle, (int) $pressId);
		if ($locale) $params[] = $locale;

		$result =& $this->retrieve(
			'SELECT	a.*
			FROM	categories a,
				category_settings l
			WHERE	l.category_id = a.category_id AND
				l.setting_name = ? AND
				l.setting_value = ?
				AND a.press_id = ?
				' . ($locale?' AND locale = ?':''),
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
	 * @return Category
	 */
	function newDataObject() {
		return new Category();
	}

	/**
	 * Internal function to return an Category object from a row.
	 * @param $row array
	 * @return Category
	 */
	function _fromRow(&$row) {
		$category = $this->newDataObject();

		$category->setId($row['category_id']);
		$category->setPressId($row['press_id']);
		$category->setParentId($row['parent_id']);
		$category->setPath($row['path']);

		$this->getDataObjectSettings('category_settings', 'category_id', $row['category_id'], $category);

		HookRegistry::call('CategoryDAO::_fromRow', array(&$category, &$row));

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
			'category_settings', $category,
			array(
				'category_id' => $category->getId()
			)
		);
	}

	/**
	 * Insert a new category.
	 * @param $category Category
	 * @return int ID of the inserted category.
	 */
	function insertObject(&$category) {
		$this->update(
			'INSERT INTO categories
				(press_id, parent_id, path)
				VALUES
				(?, ?, ?)',
			array(
				(int) $category->getPressId(),
				(int) $category->getParentId(),
				$category->getPath()
			)
		);

		$category->setId($this->getInsertCategoryId());
		$this->updateLocaleFields($category);
		return $category->getId();
	}

	/**
	 * Update an existing category.
	 * @param $category Category
	 */
	function updateObject($category) {
		$returner = $this->update(
			'UPDATE	categories
			SET	press_id = ?,
				parent_id = ?,
				path = ?
			WHERE	category_id = ?',
			array(
				(int) $category->getPressId(),
				(int) $category->getParentId(),
				$category->getPath(),
				(int) $category->getId()
			)
		);
		$this->updateLocaleFields($category);
		return $returner;
	}

	/**
	 * Delete an category.
	 * @param $category Category
	 */
	function deleteObject(&$category) {
		return $this->deleteById(
			$category->getId(),
			$category->getPressId()
		);
	}

	/**
	 * Delete an category by ID.
	 * @param $categoryId int
	 * @param $pressId int optional
	 */
	function deleteById($categoryId, $pressId = null) {
		$params = array((int) $categoryId);
		if ($pressId) $params[] = (int) $pressId;

		$this->update(
			'DELETE FROM categories
			WHERE	category_id = ?
				' . ($pressId?' AND press_id = ?':''),
			$params
		);

		// If the category was deleted (this validates press_id,
		// if specified), delete any associated settings as well.
		if ($this->getAffectedRows()) {
			$this->update(
				'DELETE FROM category_settings WHERE category_id = ?',
				array((int) $categoryId)
			);
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
	 * @return DAOResultFactory containing Category ordered by sequence
	 */
	function &getByPressId($pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM categories WHERE press_id = ?',
			array((int) $pressId)
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve all categories for a parent category.
	 * @return DAOResultFactory containing Category ordered by sequence
	 */
	function &getByParentId($parentId, $pressId = null, $rangeInfo = null) {
		$params = array((int) $parentId);
		if ($pressId) $params[] = (int) $pressId;

		$result =& $this->retrieveRange(
			'SELECT	*
			FROM	categories
			WHERE	parent_id = ?
			' . ($pressId?' AND press_id = ?':''),
			$params
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get the ID of the last inserted category.
	 * @return int
	 */
	function getInsertCategoryId() {
		return $this->getInsertId('categories', 'category_id');
	}
}

?>
