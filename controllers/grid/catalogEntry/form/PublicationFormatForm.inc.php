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

	/** AssignedPublicationFormat the format being edited **/
	var $_assignedPublicationFormat;

	/**
	 * Constructor.
	 */
	function PublicationFormatForm($monograph, $assignedPublicationFormat) {
		parent::Form('controllers/grid/catalogEntry/form/formatForm.tpl');
		$this->setMonograph($monograph);
		$this->setAssignedPublicationFormat($assignedPublicationFormat);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'title', 'required', 'grid.catalogEntry.titleRequired'));
		$this->addCheck(new FormValidator($this, 'publicationFormatId', 'required', 'grid.catalogEntry.publicationFormatRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	* Get the format
	* @return PublicationFormat
	*/
	function getAssignedPublicationFormat() {
		return $this->_assignedPublicationFormat;
	}

	/**
	* Set the publication format
	* @param @format PublicationFormat
	*/
	function setAssignedPublicationFormat($assignedPublicationFormat) {
		$this->_assignedPublicationFormat =& $assignedPublicationFormat;
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
		$format =& $this->getAssignedPublicationFormat();

		if ($format) {
			$this->_data = array(
				'assignedPublicationFormatId' => $format->getAssignedPublicationFormatId(),
				'title' => $format->getTitle()
			);
		}
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$format =& $this->getAssignedPublicationFormat();
		$press =& $request->getPress();


		$templateMgr =& TemplateManager::getManager();
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormats =& $publicationFormatDao->getEnabledByPressId($press->getId());
		$formats = array();
		while ($format =& $publicationFormats->next()) {
			$formats[ $format->getId() ] = $format->getLocalizedName();
		}
		$templateMgr->assign_by_ref('publicationFormats', $formats);

		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());
		$assignedPublicationFormat =& $this->getAssignedPublicationFormat();
		if ($assignedPublicationFormat != null) {
			$templateMgr->assign('publicationFormatId', $assignedPublicationFormat->getId()); // parent PublicationFormat getId
			$templateMgr->assign('assignedPublicationFormatId', $assignedPublicationFormat->getAssignedPublicationFormatId());
		}
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'assignedPublicationFormatId',
			'title',
			'publicationFormatId',
		));
	}

	/**
	 * Save the assigned format
	 * @see Form::execute()
	 */
	function execute() {
		$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
		$monograph = $this->getMonograph();
		$assignedPublicationFormat =& $this->getAssignedPublicationFormat();
		if (!$assignedPublicationFormat) {
			// this is a new assigned format to this published monograph
			$assignedPublicationFormat = $assignedPublicationFormatDao->newDataObject();
			$assignedPublicationFormat->setMonographId($monograph->getId());
			$existingFormat = false;
		} else {
			$existingFormat = true;
			if ($monograph->getId() !== $assignedPublicationFormat->getMonographId()) fatalError('Invalid format!');
		}

		$assignedPublicationFormat->setTitle($this->getData('title'));
		$assignedPublicationFormat->setId($this->getData('publicationFormatId'));

		if ($existingFormat) {
			$assignedPublicationFormatDao->updateObject($assignedPublicationFormat);
			$assignedPublicationFormatId = $assignedPublicationFormat->getAssignedPublicationFormatId();
		} else {
			$assignedPublicationFormatId = $assignedPublicationFormatDao->insertObject($assignedPublicationFormat);
		}

		return $assignedPublicationFormatId;
	}
}

?>
