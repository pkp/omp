<?php

/**
 * @file controllers/grid/catalogEntry/form/RepresentativeForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RepresentativeForm
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing a representative entry
 */

import('lib.pkp.classes.form.Form');

class RepresentativeForm extends Form {
	/** The monograph associated with the format being edited **/
	var $_monograph;

	/** Representative the entry being edited **/
	var $_representative;

	/**
	 * Constructor.
	 */
	function RepresentativeForm($monograph, $representative) {
		parent::Form('controllers/grid/catalogEntry/form/representativeForm.tpl');
		$this->setMonograph($monograph);
		$this->setRepresentative($representative);

		// Validation checks for this form
		$this->addCheck(new FormValidatorCustom(
				$this, 'isSupplier', 'required', 'grid.catalogEntry.roleRequired',
				create_function(
						'$isSupplier, $form, $onixDao, $agentRole, $supplierRole',
						'return (!$isSupplier && $onixDao->codeExistsInList($agentRole, \'List69\')) || ($isSupplier && $onixDao->codeExistsInList($supplierRole, \'List93\'));'
				), array(&$this, DAORegistry::getDAO('ONIXCodelistItemDAO'), Request::getUserVar('agentRole'), Request::getUserVar('supplierRole'))
		));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the representative
	 * @return Representative
	 */
	function &getRepresentative() {
		return $this->_representative;
	}

	/**
	 * Set the representative
	 * @param @representative Representative
	 */
	function setRepresentative($representative) {
		$this->_representative = $representative;
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
		$this->_monograph = $monograph;
	}


	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the representative entry.
	 */
	function initData() {
		$representative = $this->getRepresentative();

		if ($representative) {
			$this->_data = array(
				'representativeId' => $representative->getId(),
				'role' => $representative->getRole(),
				'representativeIdType' => $representative->getRepresentativeIdType(),
				'representativeIdValue' => $representative->getRepresentativeIdValue(),
				'name' => $representative->getName(),
				'phone' => $representative->getPhone(),
				'fax' => $representative->getFax(),
				'email' => $representative->getEmail(),
				'url' =>$representative->getUrl(),
				'isSupplier' => $representative->getIsSupplier(),
			);
		}
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch($request) {

		$templateMgr = TemplateManager::getManager($request);

		$monograph = $this->getMonograph();
		$templateMgr->assign('submissionId', $monograph->getId());
		$representative = $this->getRepresentative();
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$templateMgr->assign('idTypeCodes', $onixCodelistItemDao->getCodes('List92')); // GLN, etc
		$templateMgr->assign('agentRoleCodes', $onixCodelistItemDao->getCodes('List69')); // Sales Agent, etc
		$templateMgr->assign('supplierRoleCodes', $onixCodelistItemDao->getCodes('List93')); // wholesaler, publisher to retailer, etc
		$templateMgr->assign('isSupplier', true); // default to 'supplier' on the form.

		if ($representative) {
			$templateMgr->assign('representativeId', $representative->getId());
			$templateMgr->assign('role', $representative->getRole());
			$templateMgr->assign('representativeIdType', $representative->getRepresentativeIdType());
			$templateMgr->assign('representativeIdValue', $representative->getRepresentativeIdValue());
			$templateMgr->assign('name', $representative->getName());
			$templateMgr->assign('phone', $representative->getPhone());
			$templateMgr->assign('fax', $representative->getFax());
			$templateMgr->assign('email', $representative->getEmail());
			$templateMgr->assign('url', $representative->getUrl());
			$templateMgr->assign('isSupplier', $representative->getIsSupplier() ? true : false);
		} else { // loading a blank form
			$templateMgr->assign('representativeIdType', '06'); // pre-populate new forms with GLN as it is recommended
		}

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'representativeId',
			'agentRole',
			'supplierRole',
			'representativeIdType',
			'representativeIdValue',
			'name',
			'phone',
			'fax',
			'email',
			'url',
			'isSupplier',
		));
	}

	/**
	 * Save the representative
	 * @see Form::execute()
	 */
	function execute() {
		$representativeDao = DAORegistry::getDAO('RepresentativeDAO');
		$monograph = $this->getMonograph();
		$representative = $this->getRepresentative();

		if (!$representative) {
			// this is a new representative for this monograph
			$representative = $representativeDao->newDataObject();
			$representative->setMonographId($monograph->getId());
			$existingRepresentative = false;

		} else {
			$existingRepresentative = true;
			// verify that this representative is in this monograph's context
			if ($representativeDao->getById($representative->getId(), $monograph->getId()) == null) fatalError('Invalid representative!');
		}

		if ($this->getData('isSupplier')) { // supplier
			$representative->setRole($this->getData('supplierRole'));
		} else {
			$representative->setRole($this->getData('agentRole'));
		}

		$representative->setRepresentativeIdType($this->getData('representativeIdType'));
		$representative->setRepresentativeIdValue($this->getData('representativeIdValue'));
		$representative->setName($this->getData('name'));
		$representative->setPhone($this->getData('phone'));
		$representative->setFax($this->getData('fax'));
		$representative->setEmail($this->getData('email'));
		$representative->setUrl($this->getData('url'));
		$representative->setIsSupplier($this->getData('isSupplier') ? true : false);

		if ($existingRepresentative) {
			$representativeDao->updateObject($representative);
			$representativeId = $representative->getId();
		} else {
			$representativeId = $representativeDao->insertObject($representative);
		}

		return $representativeId;
	}
}
?>
