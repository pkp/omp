<?php

/**
 * @file classes/author/form/submit/AuthorSubmitStep2Form.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep2Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 2 of author monograph submission.
 */

// $Id$


import('author.form.submit.AuthorSubmitForm');
import('inserts.monographComponents.MonographComponentsInsert');

class AuthorSubmitStep2Form extends AuthorSubmitForm {
	/**
	 * Constructor.
	 */
	var $formComponents;
	function AuthorSubmitStep2Form() {
		parent::AuthorSubmitForm();

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired'));

		//$this->formComponents = array(new ChapterEntryFormComponent($this, ($this->sequence->monograph->getWorkType()==EDITED_VOLUME)?0:AUTHORS_ONLY));		

	}

	function initializeInserts() {
		$this->formComponents = array(new MonographComponentsInsert($this, ($this->sequence->monograph->getWorkType()==EDITED_VOLUME)?0:AUTHORS_ONLY));
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {

		foreach ($this->formComponents as $formComponent) {
			$formComponent->initData();
		}
		if (isset($this->sequence->monograph)) {
			$monograph =& $this->sequence->monograph;
			$this->_data = array_merge($this->_data,
				array(
					'title' => $monograph->getTitle(null), // Localized
					'abstract' => $monograph->getAbstract(null), // Localized
					'discipline' => $monograph->getDiscipline(null), // Localized
					'subjectClass' => $monograph->getSubjectClass(null), // Localized
					'subject' => $monograph->getSubject(null), // Localized
					'coverageGeo' => $monograph->getCoverageGeo(null), // Localized
					'coverageChron' => $monograph->getCoverageChron(null), // Localized
					'coverageSample' => $monograph->getCoverageSample(null), // Localized
					'type' => $monograph->getType(null), // Localized
					'language' => $monograph->getLanguage(),
					'sponsor' => $monograph->getSponsor(null), // Localized
					'section' => 1//$sectionDao->getSection($monograph->getSectionId())
				)
			);			

		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$userVars = array('title',
				'abstract',
				'discipline',
				'subjectClass',
				'subject',
				'coverageGeo',
				'coverageChron',
				'coverageSample',
				'type',
				'language',
				'sponsor',
				'isEditedVolume'
				);

		foreach ($this->formComponents as $formComponent) {
			$userVars = array_merge($formComponent->listUserVars(),$userVars);
		}
		$this->readUserVars($userVars);

		// Load the section. This is used in the step 2 form to
		// determine whether or not to display indexing options.
//		$sectionDao =& DAORegistry::getDAO('SectionDAO');
//		$this->_data['section'] =& $sectionDao->getSection($this->monograph->getSectionId());

	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		$fields = array('title', 'abstract', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor');
		foreach ($this->formComponents as $formComponent) {
			array_merge($fields,$formComponent->getLocaleFieldNames());
		}
	}

	/**
	 * Display the form.
	 */
	function display() {
		foreach ($this->formComponents as $formComponent) {
			$formComponent->display();
		}
		$templateMgr =& TemplateManager::getManager();

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign_by_ref('countries', $countries);

		parent::display();
	}
	function getHelpTopicId() {
		return 'submission.indexingAndMetadata';
	}
	function getTemplateFile() {
		return 'author/submit/step2.tpl';
	}
	function processEvents() {
		$returner = false;
		foreach ($this->formComponents as $formComponent) {
			$processed = $formComponent->processEvents();
			if ($processed == true) $returner = true;
		}
		return $returner;
	}
	/**
	 * Save changes to monograph.
	 * @return int the monograph ID
	 */
	function execute() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$authorDao =& DAORegistry::getDAO('AuthorDAO');

		// Update monograph
		$monograph =& $this->sequence->monograph;
		$monograph->setTitle($this->getData('title'), null); // Localized
		$monograph->setAbstract($this->getData('abstract'), null); // Localized
		$monograph->setDiscipline($this->getData('discipline'), null); // Localized
		$monograph->setSubjectClass($this->getData('subjectClass'), null); // Localized
		$monograph->setSubject($this->getData('subject'), null); // Localized
		$monograph->setCoverageGeo($this->getData('coverageGeo'), null); // Localized
		$monograph->setCoverageChron($this->getData('coverageChron'), null); // Localized
		$monograph->setCoverageSample($this->getData('coverageSample'), null); // Localized
		$monograph->setType($this->getData('type'), null); // Localized
		$monograph->setLanguage($this->getData('language'));
		$monograph->setSponsor($this->getData('sponsor'), null); // Localized
 		if ($monograph->getSubmissionProgress() <= $this->sequence->currentStep) {
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress($this->sequence->currentStep + 1);
		}

		foreach ($this->formComponents as $formComponent) {
			$formComponent->execute();
		}

		$monographDao->updateMonograph($monograph);

		return $monograph->getMonographId();
	}
}

?>
