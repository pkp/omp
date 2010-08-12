<?php

/**
 * @file classes/submission/form/MetadataForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MetadataForm
 * @ingroup submission_form
 *
 * @brief Form to change metadata information for a submission.
 */

// $Id$


import('lib.pkp.classes.form.Form');

class MetadataForm extends Form {
	/** @var Monograph current monograph */
	var $monograph;

	/** @var boolean can edit metadata */
	var $canEdit;

	/** @var boolean can view authors */
	var $canViewAuthors;

	/** @var Insert monograph components insert */
	var $componentsInsert;

	/** @var Display only metadata content (e.g. for a modal) */
	var $contentOnly;

	/**
	 * Constructor.
	 */
	function MetadataForm(&$monograph, $contentOnly = false) {
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		$user =& Request::getUser();
		$roleId = $roleDao->getRoleIdFromPath(Request::getRequestedPage());

		// If the user is an editor of this monograph, make the form editable.
		$this->canEdit = false;
		$this->isEditor = false;
		if ($roleId != null && ($roleId == ROLE_ID_EDITOR || $roleId == ROLE_ID_SERIES_EDITOR)) {
			$this->canEdit = true;
			$this->isEditor = true;
		}

		$copyeditInitialSignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $monograph->getId());
		// If the user is an author and the monograph hasn't passed the Copyediting stage, make the form editable.
		if ($roleId == ROLE_ID_AUTHOR) {
			if ($monograph->getStatus() != STATUS_PUBLISHED && ($copyeditInitialSignoff == null || $copyeditInitialSignoff->getDateCompleted() == null)) {
				$this->canEdit = true;
			}
		}

		// Copy editors are also allowed to edit metadata, but only if they have
		// a current assignment to the monograph.
		if ($roleId != null && ($roleId == ROLE_ID_COPYEDITOR)) {
			$copyeditFinalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $monograph->getId());
			if ($copyeditFinalSignoff != null && $monograph->getStatus() != STATUS_PUBLISHED) {
				if ($copyeditInitialSignoff->getDateNotified() != null && $copyeditFinalSignoff->getDateCompleted() == null) {
					$this->canEdit = true;
				}
			}
		}

		if ($this->canEdit) {
			parent::Form('submission/metadata/metadataEdit.tpl');
			$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'submission.submit.form.titleRequired'));
		} else {
			parent::Form('submission/metadata/metadataView.tpl');
		}

		// If the user is a reviewer of this monograph, do not show authors.
		$this->canViewAuthors = true;
		if ($roleId != null && $roleId == ROLE_ID_REVIEWER) {
			$this->canViewAuthors = false;
		}

		$this->monograph = $monograph;
		$this->contentOnly = $contentOnly;
		$this->addCheck(new FormValidatorPost($this));

	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		$this->_data = array();
		if (isset($this->monograph)) {
			$monograph =& $this->monograph;

			$this->_data = array(
				'title' => $monograph->getTitle(null),
				'abstract' => $monograph->getAbstract(null),
				'language' => $monograph->getLanguage(),
				'sponsor' => $monograph->getSponsor(null),
				'discipline' => $monograph->getDiscipline(null),
				'subjectClass' => $monograph->getSubjectClass(null),
				'subject' => $monograph->getSubject(null),
				'coverageGeo' => $monograph->getCoverageGeo(null),
				'coverageChron' => $monograph->getCoverageChron(null),
				'coverageSample' => $monograph->getCoverageSample(null),
				'contentOnly' => $this->contentOnly
			);

		}

	}

	/**
	 * Get the field names for which data can be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array(
			'title', 'abstract', 'sponsor', 'discipline', 'subjectClass', 'type',
			'subject', 'coverageGeo', 'coverageChron', 'coverageSample'
		);
	}

	/**
	 * Display the form.
	 */
	function display(&$request, $fetch = true) {
		$press =& Request::getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR)); // editor.cover.xxx locale keys; FIXME?

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', isset($this->monograph) ? $this->monograph->getId():null);
		$templateMgr->assign('pressSettings', $settingsDao->getPressSettings($press->getId()));
		$templateMgr->assign('rolePath', Request::getRequestedPage());
		$templateMgr->assign('canViewAuthors', $this->canViewAuthors);
		$templateMgr->assign('contentOnly', $this->contentOnly);

		$templateMgr->assign('helpTopicId','submission.indexingAndMetadata');

		return parent::display($request, $fetch);
	}


	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars( array (
				'monographId',
				'title',
				'abstract',
				'language',
				'sponsor',
				'discipline',
				'subjectClass',
				'subject',
				'coverageGeo',
				'coverageChron',
				'coverageSample',
				'type'
			));
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
		$monograph->setAbstract($this->getData('abstract'), null); // Localized
		$monograph->setLanguage($this->getData('language'));
		$monograph->setSponsor($this->getData('sponsor'), null); // Localized
		$monograph->setDiscipline($this->getData('discipline'), null); // Localized
		$monograph->setSubjectClass($this->getData('subjectClass'), null); // Localized
		$monograph->setSubject($this->getData('subject'), null); // Localized
		$monograph->setCoverageGeo($this->getData('coverageGeo'), null); // Localized
		$monograph->setCoverageChron($this->getData('coverageChron'), null); // Localized
		$monograph->setCoverageSample($this->getData('coverageSample'), null); // Localized
		$monograph->setType($this->getData('type'), null); // Localized

		// Save the monograph
		$monographDao->updateMonograph($monograph);

		return $monograph->getId();
	}

	/**
	 * Determine whether or not the current user is allowed to edit metadata.
	 * @return boolean
	 */
	function getCanEdit() {
		return $this->canEdit;
	}
}

?>
