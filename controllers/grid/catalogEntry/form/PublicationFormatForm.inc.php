<?php

/**
 * @file controllers/grid/catalogEntry/form/PublicationFormatForm.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
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
		$this->addCheck(new FormValidator($this, 'name', 'required', 'grid.catalogEntry.nameRequired'));
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
		$this->_publicationFormat = $publicationFormat;
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
		$this->_monograph = $monograph;
	}


	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the associated publication format.
	 */
	function initData() {
		$format = $this->getPublicationFormat();

		if ($format) {
			$this->_data = array(
				'entryKey' => $format->getEntryKey(),
				'name' => $format->getName(null),
				'isPhysicalFormat' => $format->getPhysicalFormat()?true:false,
			);
		} else {
			$this->setData('entryKey', 'DA');
		}
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$templateMgr->assign('entryKeys', $onixCodelistItemDao->getCodes('List7')); // List7 is for object formats

		$monograph = $this->getMonograph();
		$templateMgr->assign('submissionId', $monograph->getId());
		$publicationFormat = $this->getPublicationFormat();
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
			'name',
			'entryKey',
			'isPhysicalFormat',
		));
	}

	/**
	 * Save the assigned format
	 * @param PKPRequest request
	 * @see Form::execute()
	 */
	function execute($request) {
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$monograph = $this->getMonograph();
		$publicationFormat = $this->getPublicationFormat();
		if (!$publicationFormat) {
			// this is a new format to this published monograph
			$publicationFormat = $publicationFormatDao->newDataObject();
			$publicationFormat->setMonographId($monograph->getId());
			$existingFormat = false;
		} else {
			$existingFormat = true;
			if ($monograph->getId() !== $publicationFormat->getMonographId()) fatalError('Invalid format!');
		}

		$publicationFormat->setName($this->getData('name'));
		$publicationFormat->setEntryKey($this->getData('entryKey'));
		$publicationFormat->setPhysicalFormat($this->getData('isPhysicalFormat')?true:false);

		if ($existingFormat) {
			$publicationFormatDao->updateObject($publicationFormat);
			$publicationFormatId = $publicationFormat->getId();
		} else {
			$publicationFormatId = $publicationFormatDao->insertObject($publicationFormat);
			// log the creation of the format.
			import('lib.pkp.classes.log.SubmissionLog');
			import('classes.log.SubmissionEventLogEntry');
			SubmissionLog::logEvent($request, $monograph, SUBMISSION_LOG_PUBLICATION_FORMAT_CREATE, 'submission.event.publicationFormatCreated', array('formatName' => $publicationFormat->getLocalizedName()));
		}

		return $publicationFormatId;
	}
}

?>
