<?php

/**
 * @file classes/submission/form/MetadataForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MetadataForm
 * @ingroup submission_form
 *
 * @brief Form to change metadata information for a submission.
 */

// $Id$


import('form.Form');
import('inserts.monographComponents.MonographComponentsInsert');

class MetadataForm extends Form {
	/** @var Monograph current monograph */
	var $monograph;

	/** @var boolean can edit metadata */
	var $canEdit;

	/** @var boolean can view authors */
	var $canViewAuthors;
	var $formComponents;
	/**
	 * Constructor.
	 */
	function MetadataForm(&$monograph) {
		$this->formComponents = new MonographComponentsInsert($monograph);
		$roleDao = &DAORegistry::getDAO('RoleDAO');
		$signoffDao = &DAORegistry::getDAO('SignoffDAO');

		$user = &Request::getUser();
		$roleId = $roleDao->getRoleIdFromPath(Request::getRequestedPage());

		// If the user is an editor of this monograph, make the form editable.
		$this->canEdit = false;
		$this->isEditor = false;
		if ($roleId != null && ($roleId == ROLE_ID_EDITOR || $roleId == ROLE_ID_ACQUISITIONS_EDITOR)) {
			$this->canEdit = true;
			$this->isEditor = true;
		}

		$copyeditInitialSignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $monograph->getMonographId());
		// If the user is an author and the monograph hasn't passed the Copyediting stage, make the form editable.
		if ($roleId == ROLE_ID_AUTHOR) {
			if ($monograph->getStatus() != STATUS_PUBLISHED && ($copyeditInitialSignoff == null || $copyeditInitialSignoff->getDateCompleted() == null)) {
				$this->canEdit = true;
			}
		}

		// Copy editors are also allowed to edit metadata, but only if they have
		// a current assignment to the monograph.
		if ($roleId != null && ($roleId == ROLE_ID_COPYEDITOR)) {
			$copyeditFinalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $monograph->getMonographId());			
			if ($copyeditFinalSignoff != null && $monograph->getStatus() != STATUS_PUBLISHED) {
				if ($copyeditInitialSignoff->getDateNotified() != null && $copyeditFinalSignoff->getDateCompleted() == null) {
					$this->canEdit = true;
				}
			}
		}

		if ($this->canEdit) {
			parent::Form('submission/metadata/metadataEdit.tpl');
			$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired'));
		} else {
			parent::Form('submission/metadata/metadataView.tpl');
		}

		// If the user is a reviewer of this monograph, do not show authors.
		$this->canViewAuthors = true;
		if ($roleId != null && $roleId == ROLE_ID_REVIEWER) {
			$this->canViewAuthors = false;
		}

		$this->monograph = $monograph;

		$this->addCheck(new FormValidatorPost($this));

	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		$this->_data = array();
		$this->_data = array_merge($this->_data, $this->formComponents->initData($this));
		if (isset($this->monograph)) {
			$monograph =& $this->monograph;
			if (!is_array($this->_data))
				$this->_data = array($this->_data);
			$this->_data = array_merge($this->_data,array(

				'title' => $monograph->getTitle(null), // Localized
				'abstract' => $monograph->getAbstract(null), // Localized
				'language' => $monograph->getLanguage(),
				'sponsor' => $monograph->getSponsor(null), // Localized
				'hideAuthor' => $monograph->getHideAuthor()

			));

		}

	}

	/**
	 * Get the field names for which data can be localized
	 * @return array
	 */
	function getLocaleFieldNames() {

		$fields = array(
			'title', 'abstract', 'coverPageAltText', 'showCoverPage', 'hideCoverPageToc', 'hideCoverPageAbstract', 'originalFileName', 'fileName', 'width', 'height',
			'discipline', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor'
		);
		return array_merge($fields, $this->formComponents->getLocaleFieldNames());
	}

	/**
	 * Display the form.
	 */
	function display() {
		$this->formComponents->display($this);
		$press =& Request::getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', isset($this->monograph)?$this->monograph->getMonographId():null);
		$templateMgr->assign('pressSettings', $settingsDao->getPressSettings($press->getId()));
		$templateMgr->assign('rolePath', Request::getRequestedPage());
		$templateMgr->assign('canViewAuthors', $this->canViewAuthors);

		parent::display();
	}


	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$userVars = array (
				'monographId',
				'title',
				'abstract',
				'language',
				'sponsor',
				'hideAuthor'
			);

		$this->readUserVars(array_merge($userVars, $this->formComponents->listUserVars()));
	}

	/**
	 * Save changes to monograph.
	 * @return int the monograph ID
	 */
	function execute() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		// Update monograph
		$monograph =& $this->monograph;
		$monograph->setTitle($this->getData('title'), null); // Localized

		$this->formComponents->execute($this, $monograph);

		$monograph->setAbstract($this->getData('abstract'), null); // Localized

		$monograph->setLanguage($this->getData('language')); // Localized
		$monograph->setSponsor($this->getData('sponsor'), null); // Localized
		$monograph->setHideAuthor($this->getData('hideAuthor') ? $this->getData('hideAuthor') : 0);


		// Save the monograph
		$monographDao->updateMonograph($monograph);

		return $monograph->getMonographId();
	}

	/**
	 * Determine whether or not the current user is allowed to edit metadata.
	 * @return boolean
	 */
	function getCanEdit() {
		return $this->canEdit;
	}
	function processEvents() {
		return $this->formComponents->processEvents($this);
	}
}

?>