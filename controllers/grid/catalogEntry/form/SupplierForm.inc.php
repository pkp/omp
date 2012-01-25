<?php

/**
 * @file controllers/grid/catalogEntry/form/SupplierForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SupplierForm
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing a supplier entry
 */

import('lib.pkp.classes.form.Form');

class SupplierForm extends Form {
	/** The monograph associated with the format being edited **/
	var $_monograph;

	/** Supplier the entry being edited **/
	var $_supplier;

	/**
	 * Constructor.
	 */
	function SupplierForm($monograph, $supplier) {
		parent::Form('controllers/grid/catalogEntry/form/supplierForm.tpl');
		$this->setMonograph($monograph);
		$this->setSupplier($supplier);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'role', 'required', 'grid.catalogEntry.roleRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	* Get the supplier
	* @return Supplier
	*/
	function getSupplier() {
		return $this->_supplier;
	}

	/**
	* Set the supplier
	* @param @supplier Supplier
	*/
	function setSupplier($supplier) {
		$this->_supplier =& $supplier;
	}

	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the Monograph
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
	}


	//
	// Overridden template methods
	//
	/**
	* Initialize form data from the supplier entry.
	*/
	function initData() {
		$supplier =& $this->getSupplier();

		if ($supplier) {
			$this->_data = array(
				'supplierId' => $supplier->getId(),
				'role' => $supplier->getRole(),
				'supplierIdType' => $supplier->getSupplierIdType(),
				'supplierIdValue' => $supplier->getSupplierIdValue(),
				'name' => $supplier->getName(),
				'phone' => $supplier->getPhone(),
				'fax' => $supplier->getFax(),
				'email' => $supplier->getEmail(),
				'url' =>$supplier->getUrl()
			);
		}
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {

		$templateMgr =& TemplateManager::getManager();

		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());
		$supplier =& $this->getSupplier();
		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');
		$templateMgr->assign('roleCodes', $onixCodelistItemDao->getCodes('List93')); // wholesaler, publisher to retailer, etc
		$templateMgr->assign('idTypeCodes', $onixCodelistItemDao->getCodes('List92')); // GLN, etc

		if ($supplier) {
			$templateMgr->assign('supplierId', $supplier->getId());
			$templateMgr->assign('role', $supplier->getRole());
			$templateMgr->assign('supplierIdType', $supplier->getSupplierIdType());
			$templateMgr->assign('supplierIdValue', $supplier->getSupplierIdValue());
			$templateMgr->assign('name', $supplier->getName());
			$templateMgr->assign('phone', $supplier->getPhone());
			$templateMgr->assign('fax', $supplier->getFax());
			$templateMgr->assign('email', $supplier->getEmail());
			$templateMgr->assign('url', $supplier->getUrl());

		} else { // loading a blank form
			$templateMgr->assign('supplierIdType', '06'); // pre-populate new forms with GLN as it is recommended
		}

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'supplierId',
			'role',
			'supplierIdType',
			'supplierIdValue',
			'name',
			'phone',
			'fax',
			'email',
			'url'
		));
	}

	/**
	 * Save the supplier
	 * @see Form::execute()
	 */
	function execute() {
		$supplierDao =& DAORegistry::getDAO('SupplierDAO');
		$monograph = $this->getMonograph();
		$supplier =& $this->getSupplier();

		if (!$supplier) {
			// this is a new supplier for this monograph
			$supplier = $supplierDao->newDataObject();
			$supplier->setMonographId($monograph->getId());
			$existingSupplier = false;

		} else {
			$existingSupplier = true;
			// verify that this supplier is in this monograph's context
			if ($supplierDao->getById($supplier->getId(), $monograph->getId()) == null) fatalError('Invalid supplier!');
		}

		$supplier->setRole($this->getData('role'));
		$supplier->setSupplierIdType($this->getData('supplierIdType'));
		$supplier->setSupplierIdValue($this->getData('supplierIdValue'));
		$supplier->setName($this->getData('name'));
		$supplier->setPhone($this->getData('phone'));
		$supplier->setFax($this->getData('fax'));
		$supplier->setEmail($this->getData('email'));
		$supplier->setUrl($this->getData('url'));

		if ($existingSupplier) {
			$supplierDao->updateObject($supplier);
			$supplierId = $supplier->getId();
		} else {
			$supplierId = $supplierDao->insertObject($supplier);
		}

		return $supplierId;
	}
}

?>
