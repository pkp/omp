<?php

/**
 * @file controllers/grid/catalogEntry/form/RepresentativeForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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
	function __construct($monograph, $representative) {
		parent::__construct('controllers/grid/catalogEntry/form/representativeForm.tpl');
		$this->setMonograph($monograph);
		$this->setRepresentative($representative);

		// Validation checks for this form
		$form = $this;
		$this->addCheck(new FormValidatorCustom(
			$this, 'isSupplier', 'required', 'grid.catalogEntry.roleRequired',
			function($isSupplier) use ($form) {
				$request = Application::get()->getRequest();
				$agentRole = $request->getUserVar('agentRole');
				$supplierRole = $request->getUserVar('supplierRole');
				$onixDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
				return (!$isSupplier && $onixDao->codeExistsInList($agentRole, 'List69')) || ($isSupplier && $onixDao->codeExistsInList($supplierRole, 'List93'));
			}
		));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
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
				'email' => $representative->getEmail(),
				'url' =>$representative->getUrl(),
				'isSupplier' => $representative->getIsSupplier(),
			);
		}
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);

		$monograph = $this->getMonograph();
		$templateMgr->assign('submissionId', $monograph->getId());
		$representative = $this->getRepresentative();
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$templateMgr->assign(array(
			'idTypeCodes' => $onixCodelistItemDao->getCodes('List92'), // GLN, etc
			'agentRoleCodes' => $onixCodelistItemDao->getCodes('List69'), // Sales Agent, etc
			'supplierRoleCodes' => $onixCodelistItemDao->getCodes('List93'), // wholesaler, publisher to retailer, etc
			'isSupplier' => true,
		)); // default to 'supplier' on the form.

		if ($representative) $templateMgr->assign(array(
			'representativeId' => $representative->getId(),
			'role' => $representative->getRole(),
			'representativeIdType' => $representative->getRepresentativeIdType(),
			'representativeIdValue' => $representative->getRepresentativeIdValue(),
			'name' => $representative->getName(),
			'phone' => $representative->getPhone(),
			'email' => $representative->getEmail(),
			'url' => $representative->getUrl(),
			'isSupplier' => $representative->getIsSupplier() ? true : false,
		));
		else $templateMgr->assign('representativeIdType', '06'); // pre-populate new forms with GLN as it is recommended

		return parent::fetch($request, $template, $display);
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

