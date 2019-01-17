<?php
/**
 * @file classes/monograph/Representative.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Representative
 * @ingroup monograph
 * @see RepresentativeDAO
 *
 * @brief Basic class describing a representative composite type (used on the ONIX templates for publication formats).
 * This type is used for both Agents and Suppliers.
 */

class Representative extends DataObject {
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
	 * Set the ONIX code for this representative role (List93 for Suppliers, List69 for Agents)
	 * @param $type string
	 */
	function setRole($role) {
		$this->setData('role', $role);
	}

	/**
	 * Get the ONIX code for this representative role.
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
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		if ($this->getIsSupplier()) {
			$listName = 'List93'; // List93 -> Publisher to retailers, Wholesaler, etc
		} else {
			$listName = 'List69'; // List93 -> Local Publisher, Sales Agent, etc
		}
		$codes =& $onixCodelistItemDao->getCodes($listName);
		return $codes[$this->getRole()];
	}

	/**
	 * Set the ONIX code for this representative's ID type (List92) (GLN, SAN, etc).  GLN is the recommended one.
	 * @param $representativeIdType string
	 */
	function setRepresentativeIdType($representativeIdType) {
		$this->setData('representativeIdType', $representativeIdType);
	}

	/**
	 * Get the representative ID type (ONIX Code).
	 * @return string
	 */
	function getRepresentativeIdType() {
		return $this->getData('representativeIdType');
	}

	/**
	 * Set this representative's ID value.
	 * @param $representativeIdValue string
	 */
	function setRepresentativeIdValue($representativeIdValue) {
		$this->setData('representativeIdValue', $representativeIdValue);
	}

	/**
	 * Get the representative ID value.
	 * @return string
	 */
	function getRepresentativeIdValue() {
		return $this->getData('representativeIdValue');
	}

	/**
	 * Get the representative name.
	 * @return string
	 */
	function getName() {
		return $this->getData('name');
	}

	/**
	 * Set the representative name.
	 * @param string $name
	 */
	function setName($name) {
		$this->setData('name', $name);
	}

	/**
	 * Get the representative phone.
	 * @return string
	 */
	function getPhone() {
		return $this->getData('phone');
	}

	/**
	 * Set the representative phone.
	 * @param string $phone
	 */
	function setPhone($phone) {
		$this->setData('phone', $phone);
	}

	/**
	 * Get the representative email address.
	 * @return string
	 */
	function getEmail() {
		return $this->getData('email');
	}

	/**
	 * Set the representative email address.
	 * @param string $email
	 */
	function setEmail($email) {
		$this->setData('email', $email);
	}

	/**
	 * Get the representative's url.
	 * @return string
	 */
	function getUrl() {
		return $this->getData('url');
	}

	/**
	 * Set the representative url.
	 * @param string $url
	 */
	function setUrl($url) {
		$this->setData('url', $url);
	}

	/**
	 * Get the representative's is_supplier setting.
	 * @return int
	 */
	function getIsSupplier() {
		return $this->getData('isSupplier');
	}

	/**
	 * Set the representative's is_supplier setting.
	 * @param int $isSupplier
	 */
	function setIsSupplier($isSupplier) {
		$this->setData('isSupplier', $isSupplier);
	}
}

