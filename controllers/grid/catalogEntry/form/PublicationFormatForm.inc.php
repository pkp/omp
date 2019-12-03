<?php

/**
 * @file controllers/grid/catalogEntry/form/PublicationFormatForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
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

	/** @var $_publication Publication */
	var $_publication = null;

	/**
	 * Constructor.
	 */
	function __construct($monograph, $publicationFormat, $publication) {
		parent::__construct('controllers/grid/catalogEntry/form/formatForm.tpl');
		$this->setMonograph($monograph);
		$this->setPublicationFormat($publicationFormat);
		$this->setPublication($publication);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'name', 'required', 'grid.catalogEntry.nameRequired'));
		$this->addCheck(new FormValidator($this, 'entryKey', 'required', 'grid.catalogEntry.publicationFormatRequired'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
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


	/**
	 * Get the Publication
	 * @return Publication
	 */
	function getPublication() {
		return $this->_publication;
	}

	/**
	 * Set the PublicationId
	 * @param Publication
	 */
	function setPublication($publication) {
		$this->_publication = $publication;
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
				'remoteURL' => $format->getRemoteURL(),
			);
		} else {
			$this->setData('entryKey', 'DA');
		}
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$templateMgr->assign('entryKeys', $onixCodelistItemDao->getCodes('List7')); // List7 is for object formats

		$monograph = $this->getMonograph();
		$templateMgr->assign('submissionId', $monograph->getId());
		$publicationFormat = $this->getPublicationFormat();
		if ($publicationFormat != null) {
			$templateMgr->assign('representationId', $publicationFormat->getId());
		}
		$templateMgr->assign('publicationId', $this->getPublication()->getId());
		return parent::fetch($request, $template, $display);
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
			'remoteURL',
		));
	}

	/**
	 * Save the assigned format
	 * @return int Publication format ID
	 * @see Form::execute()
	 */
	function execute(...$functionParams) {
		parent::execute(...$functionParams);

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $this->getPublicationFormat();
		if (!$publicationFormat) {
			// this is a new format to this published submission
			$publicationFormat = $publicationFormatDao->newDataObject();
			$publicationFormat->setData('publicationId', $this->getPublication()->getId());
			$existingFormat = false;
		} else {
			$existingFormat = true;
			if ($this->getPublication()->getId() != $publicationFormat->getData('publicationId')) {
				throw new Exception('Invalid publication format');
			}
		}

		$publicationFormat->setName($this->getData('name'));
		$publicationFormat->setEntryKey($this->getData('entryKey'));
		$publicationFormat->setPhysicalFormat($this->getData('isPhysicalFormat')?true:false);
		$publicationFormat->setRemoteURL($this->getData('remoteURL'));

		if ($existingFormat) {
			$publicationFormatDao->updateObject($publicationFormat);
			$representationId = $publicationFormat->getId();
		} else {
			$representationId = $publicationFormatDao->insertObject($publicationFormat);
			// log the creation of the format.
			import('lib.pkp.classes.log.SubmissionLog');
			import('classes.log.SubmissionEventLogEntry');
			SubmissionLog::logEvent(Application::get()->getRequest(), $this->getMonograph(), SUBMISSION_LOG_PUBLICATION_FORMAT_CREATE, 'submission.event.publicationFormatCreated', array('formatName' => $publicationFormat->getLocalizedName()));
		}

		return $representationId;
	}
}


