<?php

/**
 * @file controllers/grid/catalogEntry/form/IdentificationCodeForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IdentificationCodeForm
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing an identification code
 */

import('lib.pkp.classes.form.Form');

class IdentificationCodeForm extends Form {
	/** The monograph associated with the format being edited **/
	var $_monograph;

	/** Identification Code the code being edited **/
	var $_identificationCode;

	/**
	 * Constructor.
	 */
	function __construct($monograph, $identificationCode) {
		parent::__construct('controllers/grid/catalogEntry/form/codeForm.tpl');
		$this->setMonograph($monograph);
		$this->setIdentificationCode($identificationCode);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'code', 'required', 'grid.catalogEntry.codeRequired'));
		$this->addCheck(new FormValidator($this, 'value', 'required', 'grid.catalogEntry.valueRequired'));
		$this->addCheck(new FormValidator($this, 'representationId', 'required', 'grid.catalogEntry.publicationFormatRequired'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the code
	 * @return IdentificationCode
	 */
	function &getIdentificationCode() {
		return $this->_identificationCode;
	}

	/**
	 * Set the code
	 * @param @identificationCode IdentificationCode
	 */
	function setIdentificationCode($identificationCode) {
		$this->_identificationCode = $identificationCode;
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
	 * Initialize form data from the identification code.
	 */
	function initData() {
		$code = $this->getIdentificationCode();

		if ($code) {
			$this->_data = array(
				'identificationCodeId' => $code->getId(),
				'code' => $code->getCode(),
				'value' => $code->getValue(),
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
		$identificationCode = $this->getIdentificationCode();

		if ($identificationCode) {
			$templateMgr->assign('identificationCodeId', $identificationCode->getId());
			$templateMgr->assign('code', $identificationCode->getCode());
			$templateMgr->assign('value', $identificationCode->getValue());
			$representationId = $identificationCode->getPublicationFormatId();
		} else { // loading a blank form
			$representationId = (int) $request->getUserVar('representationId');
		}

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($representationId, $monograph->getId());

		if ($publicationFormat) { // the format exists for this monograph
			$templateMgr->assign('representationId', $representationId);
			$identificationCodes = $publicationFormat->getIdentificationCodes();
			$assignedCodes = array_keys($identificationCodes->toAssociativeArray('code')); // currently assigned codes
			if ($identificationCode) $assignedCodes = array_diff($assignedCodes, array($identificationCode->getCode())); // allow existing codes to keep their value
			$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');

			// since the pubId DOI plugin may be enabled, we give that precedence and remove DOI from here if that is the case.
			$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
			foreach ($pubIdPlugins as $plugin) {
				if ($plugin->getEnabled() && $plugin->getPubIdType() == 'doi') {
					$assignedCodes[] = '06'; // 06 is DOI in ONIX-speak.
				}
			}
			$codes = $onixCodelistItemDao->getCodes('List5', $assignedCodes); // ONIX list for these
			$templateMgr->assign('identificationCodes', $codes);
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
			'identificationCodeId',
			'representationId',
			'code',
			'value',
		));
	}

	/**
	 * Save the code
	 * @see Form::execute()
	 */
	function execute() {
		$identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO');
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');

		$monograph = $this->getMonograph();
		$identificationCode = $this->getIdentificationCode();
		$publicationFormat = $publicationFormatDao->getById($this->getData('representationId', $monograph->getId()));

		if (!$identificationCode) {
			// this is a new code to this published monograph
			$identificationCode = $identificationCodeDao->newDataObject();
			$existingFormat = false;
			if ($publicationFormat != null) { // ensure this format is in this monograph
				$identificationCode->setPublicationFormatId($publicationFormat->getId());
			} else {
				fatalError('This format not in authorized monograph context!');
			}
		} else {
			$existingFormat = true;
			if ($publicationFormat->getId() !== $identificationCode->getPublicationFormatId()) fatalError('Invalid format!');
		}

		$identificationCode->setCode($this->getData('code'));
		$identificationCode->setValue($this->getData('value'));

		if ($existingFormat) {
			$identificationCodeDao->updateObject($identificationCode);
			$identificationCodeId = $identificationCode->getId();
		} else {
			$identificationCodeId = $identificationCodeDao->insertObject($identificationCode);
		}

		return $identificationCodeId;
	}
}


