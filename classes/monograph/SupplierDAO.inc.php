<?php

/**
 * @file classes/monograph/SupplierDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SupplierDAO
 * @ingroup monograph
 * @see Supplier
 *
 * @brief Operations for retrieving and modifying Supplier objects.
 */

import('classes.monograph.Supplier');

class SupplierDAO extends DAO {
	/**
	 * Constructor
	 */
	function SupplierDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a supplier entry by id.
	 * @param $supplierId int
	 * @param $monographId optional int
	 * @return Supplier
	 */
	function &getById($supplierId, $monographId = null){
		$sqlParams = array((int) $supplierId);
		if ($monographId) {
			$sqlParams[] = (int) $monographId;
		}

		$result =& $this->retrieve(
			'SELECT s.*
				FROM suppliers s
			JOIN published_monographs pm ON (s.monograph_id = pm.monograph_id)
			WHERE s.supplier_id = ?
				' . ($monographId?' AND pm.monograph_id = ?':''),
			$sqlParams);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve all suppliers for a monograph.
	 * @param $monographId int
	 * @return DAOResultFactory containing matching suppliers.
	 */
	function &getSuppliersByMonographId($monographId) {
		$result =& $this->retrieveRange(
			'SELECT * FROM suppliers WHERE monograph_id = ?', array((int) $monographId));

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return Supplier
	 */
	function newDataObject() {
		return new Supplier();
	}

	/**
	 * Internal function to return a Supplier object from a row.
	 * @param $row array
	 * @param $callHooks boolean
	 * @return Supplier
	 */
	function &_fromRow(&$row, $callHooks = true) {
		$supplier = $this->newDataObject();
		$supplier->setId($row['supplier_id']);
		$supplier->setRole($row['role']);
		$supplier->setSupplierIdType($row['supplier_id_type']);
		$supplier->setSupplierIdValue($row['supplier_id_value']);
		$supplier->setName($row['name']);
		$supplier->setPhone($row['phone']);
		$supplier->setFax($row['fax']);
		$supplier->setEmail($row['email']);
		$supplier->setUrl($row['url']);

		$supplier->setMonographId($row['monograph_id']);

		if ($callHooks) HookRegistry::call('SupplierDAO::_fromRow', array(&$supplier, &$row));

		return $supplier;
	}

	/**
	 * Insert a new supplier entry.
	 * @param $supplier Supplier
	 */
	function insertObject(&$supplier) {
		$this->update(
			'INSERT INTO suppliers
				(monograph_id, role, supplier_id_type, supplier_id_value, name, phone, fax, email, url)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				(int) $supplier->getMonographId(),
				$supplier->getRole(),
				$supplier->getSupplierIdType(),
				$supplier->getSupplierIdValue(),
				$supplier->getName(),
				$supplier->getPhone(),
				$supplier->getFax(),
				$supplier->getEmail(),
				$supplier->getUrl()
			)
		);

		$supplier->setId($this->getInsertSupplierId());
		return $supplier->getId();
	}

	/**
	 * Update an existing supplier entry.
	 * @param $supplier Supplier
	 */
	function updateObject(&$supplier) {
		$this->update(
			'UPDATE suppliers
				SET role = ?,
				supplier_id_type = ?,
				supplier_id_value = ?,
				name = ?,
				phone = ?,
				fax = ?,
				email = ?,
				url = ?
			WHERE supplier_id = ?',
			array(
				$supplier->getRole(),
				$supplier->getSupplierIdType(),
				$supplier->getSupplierIdValue(),
				$supplier->getName(),
				$supplier->getPhone(),
				$supplier->getFax(),
				$supplier->getEmail(),
				$supplier->getUrl(),
				(int) $supplier->getId()
			)
		);
	}

	/**
	 * Delete a supplier entry by object.
	 * @param $supplier Supplier
	 */
	function deleteObject($supplier) {
		return $this->deleteById($supplier->getId());
	}

	/**
	 * delete a supplier entry by id.
	 * @param $entryId int
	 */
	function deleteById($entryId) {
		return $this->update(
			'DELETE FROM suppliers WHERE supplier_id = ?', array((int) $entryId)
		);
	}

	/**
	 * Get the ID of the last inserted supplier entry.
	 * @return int
	 */
	function getInsertSupplierId() {
		return $this->getInsertId('suppliers', 'supplier_id');
	}
}

?>
