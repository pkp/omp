<?php

/**
 * @file controllers/grid/settings/formats/form/PublicationFormatForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatForm
 * @ingroup controllers_grid_settings_formats_form
 *
 * @brief Form for adding/editing a publication format
 */

import('lib.pkp.classes.form.Form');

class PublicationFormatForm extends Form {

	/** the Press **/
	var $_press;

	/** Publication Format the format being edited **/
	var $_publicationFormat;

	/**
	 * Constructor.
	 */
	function PublicationFormatForm($press, $publicationFormat) {
		parent::Form('controllers/grid/settings/formats/formatForm.tpl');

		$this->setPress($press);
		$this->setPublicationFormat($publicationFormat);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'entryKey', 'required', 'manager.setup.publicationFormat.codeRequired'));
		$this->addCheck(new FormValidator($this, 'name', 'required', 'manager.setup.publicationFormat.nameRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	* Get the format
	* @return PublicationFormat
	*/
	function getPublicationFormat() {
		return $this->_publicationFormat;
	}

	/**
	* Set the format
	* @param @publicationFormat PublicationFormat
	*/
	function setPublicationFormat($publicationFormat) {
		$this->_publicationFormat =& $publicationFormat;
	}

	/**
	 * Set the press context
	 * @param Press $press
	 */
	function setPress($press) {
		$this->_press =& $press;
	}

	/**
	 * Get the press context
	 * @return Press
	 */
	function getPress() {
		return $this->_press;
	}

	//
	// Overridden template methods
	//
	/**
	* Initialize form data from the publication format.
	*/
	function initData() {
		$format =& $this->getPublicationFormat();

		if ($format) {
			$this->_data = array(
				'formatId' => $format->getId(),
				'entryKey' => $format->getEntryKey(),
				'name' => $format->getName(null), // localized
				'enabled' => $format->getEnabled(),
				'physicalFormat' => $format->getPhysicalFormat()
			);
		}
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {

		$router =& $request->getRouter();
		$context = $request->getContext();

		$templateMgr =& TemplateManager::getManager();
		$formatId = null;

		$publicationFormat =& $this->getPublicationFormat();

		if (isset($publicationFormat)) {

			$templateMgr->assign('formatId', $publicationFormat->getId());
			$templateMgr->assign('entryKey', $publicationFormat->getEntryKey());
			$templateMgr->assign('name', $publicationFormat->getName(null));
			$templateMgr->assign('enabled', $publicationFormat->getEnabled());
			$templateMgr->assign('physicalFormat', $publicationFormat->getPhysicalFormat());
		}

		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codes =& $onixCodelistItemDao->getCodes('List7'); // ONIX list for these
		$templateMgr->assign_by_ref('formatCodes', $codes);

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'formatId',
			'entryKey',
			'name',
			'enabled',
			'physicalFormat'
		));
	}

	/**
	 * Save the format
	 * @see Form::execute()
	 */
	function execute() {

		$press =& $this->getPress();
		$publicationFormat =& $this->getPublicationFormat();
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');

		if (!isset($publicationFormat)) {
			// this is a new format for this press
			$existingFormat = false;
			$publicationFormat =& $publicationFormatDao->newDataObject();
			$publicationFormat->setPressId($press->getId());
		} else {
			if ($publicationFormatDao->getById($publicationFormat->getId(), $press->getId()) != null) { // context validation
				$existingFormat = true;
			} else {
				fatalError('publication format not in press context');
			}
		}

		$publicationFormat->setEntryKey($this->getData('entryKey'));
		$publicationFormat->setName($this->getData('name'), null); // localized
		$publicationFormat->setEnabled($this->getData('enabled') ? true : false);
		$publicationFormat->setPhysicalFormat($this->getData('physicalFormat') ? true : false);

		if ($existingFormat) {
			$publicationFormatDao->updateObject($publicationFormat);
			$publicationFormatId = $publicationFormat->getId();
		} else {
			$publicationFormatId = $publicationFormatDao->insertObject($publicationFormat);
		}

		return $publicationFormatId;
	}
}

?>
