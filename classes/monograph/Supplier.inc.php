<?php
/**
 * @file classes/monograph/Supplier.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Supplier
 * @ingroup monograph
 * @see SupplierDAO
 *
 * @brief Basic class describing a supplier composite type (used on the ONIX templates for publication formats).
 */

class Supplier extends DataObject {
	/**
	 * Constructor
	 */
	function Supplier() {
		parent::DataObject();
	}

	/**
	 * get monograph id.
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	 * set monograph id.
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}

	/**
	 * Set the ONIX code for this supplier role (List93).
	 * @param $type string
	 */
	function setRole($role) {
		$this->setData('role', $role);
	}

	/**
	 * Get the ONIX code for this supplier role.
	 * @return string
	 */
	function getRole() {
		return $this->getData('role');
	}

	/**
	 * Get the human readable name for this ONIX code
	 * @return string
	 */
	function getNameForONIXCode() {
		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codes =& $onixCodelistItemDao->getCodes('List93'); // List93 -> Publisher to retailers, Wholesaler, etc
		return $codes[$this->getRole()];
	}

	/**
	 * Set the ONIX code for this supplier's ID type (List92) (GLN, SAN, etc).  GLN is the recommended one.
	 * @param $supplierIdType string
	 */
	function setSupplierIdType($supplierIdType) {
		$this->setData('supplierIdType', $supplierIdType);
	}

	/**
	 * Get the supplier ID type (ONIX Code).
	 * @return string
	 */
	function getSupplierIdType() {
		return $this->getData('supplierIdType');
	}

	/**
	 * Set this supplier's ID value.
	 * @param $supplierIdValue string
	 */
	function setSupplierIdValue($supplierIdValue) {
		$this->setData('supplierIdValue', $supplierIdValue);
	}

	/**
	 * Get the supplier ID value.
	 * @return string
	 */
	function getSupplierIdValue() {
		return $this->getData('supplierIdValue');
	}

	/**
	 * Get the supplier name.
	 * @return string
	 */
	function getName() {
		return $this->getData('name');
	}

	/**
	 * Set the supplier name.
	 * @param string $name
	 */
	function setName($name) {
		$this->setData('name', $name);
	}

	/**
	 * Get the supplier phone.
	 * @return string
	 */
	function getPhone() {
		return $this->getData('phone');
	}

	/**
	 * Set the supplier phone.
	 * @param string $phone
	 */
	function setPhone($phone) {
		$this->setData('phone', $phone);
	}

	/**
	 * Get the supplier fax.
	 * @return string
	 */
	function getFax() {
		return $this->getData('fax');
	}

	/**
	 * Set the supplier fax.
	 * @param string $fax
	 */
	function setFax($fax) {
		$this->setData('fax', $fax);
	}

	/**
	 * Get the supplier email address.
	 * @return string
	 */
	function getEmail() {
		return $this->getData('email');
	}

	/**
	 * Set the supplier email address.
	 * @param string $email
	 */
	function setEmail($email) {
		$this->setData('email', $email);
	}

	/**
	 * Get the supplier's url.
	 * @return string
	 */
	function getUrl() {
		return $this->getData('url');
	}

	/**
	 * Set the supplier url.
	 * @param string $url
	 */
	function setUrl($url) {
		$this->setData('url', $url);
	}
}
?>
