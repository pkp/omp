<?php

/**
 * @file controllers/grid/catalogEntry/form/SalesRightsForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SalesRightsForm
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing a sales rights entry
 */

import('lib.pkp.classes.form.Form');

class SalesRightsForm extends Form {
	/** The monograph associated with the format being edited **/
	var $_monograph;

	/** Sales Rights the entry being edited **/
	var $_salesRights;

	/**
	 * Constructor.
	 */
	function __construct($monograph, $salesRights) {
		parent::__construct('controllers/grid/catalogEntry/form/salesRightsForm.tpl');
		$this->setMonograph($monograph);
		$this->setSalesRights($salesRights);

		// Validation checks for this form
		$form = $this;
		$this->addCheck(new FormValidator($this, 'type', 'required', 'grid.catalogEntry.typeRequired'));
		$this->addCheck(new FormValidator($this, 'representationId', 'required', 'grid.catalogEntry.publicationFormatRequired'));
		$this->addCheck(new FormValidatorCustom(
			$this, 'ROWSetting', 'optional', 'grid.catalogEntry.oneROWPerFormat',
			function($ROWSetting) use ($form, $salesRights) {
				$salesRightsDao = DAORegistry::getDAO('SalesRightsDAO');
				$pubFormatId = $form->getData('representationId');
				return $ROWSetting == '' || $salesRightsDao->getROWByPublicationFormatId($pubFormatId) == null ||
					($salesRights != null && $salesRightsDao->getROWByPublicationFormatId($pubFormatId)->getId() == $salesRights->getId());
			}
		));

		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the entry
	 * @return SalesRights
	 */
	function getSalesRights() {
		return $this->_salesRights;
	}

	/**
	 * Set the entry
	 * @param @salesRights SalesRights
	 */
	function setSalesRights($salesRights) {
		$this->_salesRights = $salesRights;
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
	 * Initialize form data from the sales rights entry.
	 */
	function initData() {
		$salesRights = $this->getSalesRights();

		if ($salesRights) {
			$this->_data = array(
				'salesRightsId' => $salesRights->getId(),
				'type' => $salesRights->getType(),
				'ROWSetting' => $salesRights->getROWSetting(),
				'countriesIncluded' => $salesRights->getCountriesIncluded(),
				'countriesExcluded' => $salesRights->getCountriesExcluded(),
				'regionsIncluded' => $salesRights->getRegionsIncluded(),
				'regionsExcluded' => $salesRights->getRegionsExcluded(),
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
		$salesRights = $this->getSalesRights();
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$templateMgr->assign('countryCodes', $onixCodelistItemDao->getCodes('List91')); // countries (CA, US, GB, etc)
		$templateMgr->assign('regionCodes', $onixCodelistItemDao->getCodes('List49')); // regions (British Columbia, England, etc)

		if ($salesRights) {
			$templateMgr->assign('salesRightsId', $salesRights->getId());
			$templateMgr->assign('type', $salesRights->getType());
			$templateMgr->assign('ROWSetting', $salesRights->getROWSetting());
			$templateMgr->assign('countriesIncluded', $salesRights->getCountriesIncluded());
			$templateMgr->assign('countriesExcluded', $salesRights->getCountriesExcluded());
			$templateMgr->assign('regionsIncluded', $salesRights->getRegionsIncluded());
			$templateMgr->assign('regionsExcluded', $salesRights->getRegionsExcluded());

			$representationId = $salesRights->getPublicationFormatId();
		} else { // loading a blank form
			$representationId = (int) $request->getUserVar('representationId');
		}

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($representationId, $monograph->getId());

		if ($publicationFormat) { // the format exists for this monograph
			$templateMgr->assign('representationId', $representationId);
			// SalesRightsType values are not normally used more than once per PublishingDetail block, so filter used ones out.
			$assignedSalesRights = $publicationFormat->getSalesRights();
			$assignedTypes = array_keys($assignedSalesRights->toAssociativeArray('type')); // currently assigned types

			if ($salesRights) $assignedTypes = array_diff($assignedTypes, array($salesRights->getType())); // allow existing codes to keep their value

			$types = $onixCodelistItemDao->getCodes('List46', $assignedTypes); // ONIX list for these
			$templateMgr->assign('salesRights', $types);
		} else {
			fatalError('Format not in authorized monograph');
		}

		return parent::fetch($request, $template, $display);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'salesRightsId',
			'representationId',
			'type',
			'ROWSetting',
			'countriesIncluded',
			'countriesExcluded',
			'regionsIncluded',
			'regionsExcluded',
		));
	}

	/**
	 * Save the entry
	 * @see Form::execute()
	 */
	function execute() {
		$salesRightsDao = DAORegistry::getDAO('SalesRightsDAO');
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');

		$monograph = $this->getMonograph();
		$salesRights = $this->getSalesRights();
		$publicationFormat = $publicationFormatDao->getById($this->getData('representationId'), $monograph->getId());

		if (!$salesRights) {
			// this is a new assigned format to this published monograph
			$salesRights = $salesRightsDao->newDataObject();
			$existingFormat = false;
			if ($publicationFormat != null) { // ensure this assigned format is in this monograph
				$salesRights->setPublicationFormatId($publicationFormat->getId());
			} else {
				fatalError('This assigned format not in authorized monograph context!');
			}
		} else {
			$existingFormat = true;
			if ($publicationFormat->getId() !== $salesRights->getPublicationFormatId()) fatalError('Invalid format!');
		}

		$salesRights->setType($this->getData('type'));
		$salesRights->setROWSetting($this->getData('ROWSetting')?true:false);
		$salesRights->setCountriesIncluded($this->getData('countriesIncluded') ? $this->getData('countriesIncluded') : array());
		$salesRights->setCountriesExcluded($this->getData('countriesExcluded') ? $this->getData('countriesExcluded') : array());
		$salesRights->setRegionsIncluded($this->getData('regionsIncluded') ? $this->getData('regionsIncluded') : array());
		$salesRights->setRegionsExcluded($this->getData('regionsExcluded') ? $this->getData('regionsExcluded') : array());

		if ($existingFormat) {
			$salesRightsDao->updateObject($salesRights);
			$salesRightsId = $salesRights->getId();
		} else {
			$salesRightsId = $salesRightsDao->insertObject($salesRights);
		}

		return $salesRightsId;
	}
}

