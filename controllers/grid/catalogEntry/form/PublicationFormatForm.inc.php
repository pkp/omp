<?php

/**
 * @file controllers/grid/catalogEntry/form/PublicationFormatForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatForm
 * @ingroup controllers_grid_catalogEntry_form
 *
 * @brief Form for adding/editing a format
 */

import('lib.pkp.classes.form.Form');

class PublicationFormatForm extends Form {
	/** The monograph associated with the format being edited **/
	var $_monograph;

	/** PublicationFormat the format being edited **/
	var $_publicationFormat;

	/**
	 * Constructor.
	 */
	function PublicationFormatForm($monograph, $publicationFormat) {
		parent::Form('controllers/grid/catalogEntry/form/formatForm.tpl');
		$this->setMonograph($monograph);
		$this->setPublicationFormat($publicationFormat);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'title', 'required', 'grid.catalogEntry.titleRequired'));
		$this->addCheck(new FormValidator($this, 'entryKey', 'required', 'grid.catalogEntry.publicationFormatRequired'));
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
	* Set the publication format
	* @param @format PublicationFormat
	*/
	function setPublicationFormat($publicationFormat) {
		$this->_publicationFormat =& $publicationFormat;
	}

	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the MonographId
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
	}


	//
	// Overridden template methods
	//
	/**
	* Initialize form data from the associated publication format.
	*/
	function initData() {
		$format =& $this->getPublicationFormat();

		if ($format) {
			$this->_data = array(
				'entryKey' => $format->getEntryKey(),
				'title' => $format->getTitle()
			);
		}
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$format =& $this->getPublicationFormat();
		$press =& $request->getPress();


		$templateMgr =& TemplateManager::getManager();
		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');
		$templateMgr->assign('entryKeys', $onixCodelistItemDao->getCodes('List7')); // List7 is for object formats

		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());
		$publicationFormat =& $this->getPublicationFormat();
		if ($publicationFormat != null) {
			$templateMgr->assign('publicationFormatId', $publicationFormat->getId());
		}
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'title',
			'entryKey',
		));
	}

	/**
	 * Save the assigned format
	 * @see Form::execute()
	 */
	function execute() {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$monograph = $this->getMonograph();
		$publicationFormat =& $this->getPublicationFormat();
		if (!$publicationFormat) {
			// this is a new format to this published monograph
			$publicationFormat = $publicationFormatDao->newDataObject();
			$publicationFormat->setMonographId($monograph->getId());
			$existingFormat = false;
		} else {
			$existingFormat = true;
			if ($monograph->getId() !== $publicationFormat->getMonographId()) fatalError('Invalid format!');
		}

		$publicationFormat->setTitle($this->getData('title'));
		$publicationFormat->setEntryKey($this->getData('entryKey'));

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
