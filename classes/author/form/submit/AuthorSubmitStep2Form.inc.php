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
import('inserts.contributors.ContributorInsert');
import('inserts.monographComponents.MonographComponentsInsert');

class AuthorSubmitStep2Form extends AuthorSubmitForm {
	/**
	 * Constructor.
	 */
	var $formComponents;
	function AuthorSubmitStep2Form() {

		parent::AuthorSubmitForm();
//		$insert =& new ContributorInsert($this->sequence->monograph, $this);
//		$this->formComponents = array($insert);

		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired'));
		// Validation checks for this form

	}

	function initializeInserts() {
		if ($this->sequence->monograph->getWorkType() == EDITED_VOLUME) { 
			$insert = new MonographComponentsInsert($this->sequence->monograph, $this);
		} else {
			$insert =& new ContributorInsert($this->sequence->monograph, $this);
		}
		$this->formComponents = array($insert);
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {

		if (isset($this->sequence->monograph)) {

			foreach ($this->formComponents as $formComponent) {
				$this->_data = array_merge($this->_data, $formComponent->initData($this));
			}

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
			$userVars =& array_merge($formComponent->listUserVars(),$userVars);
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
			$fields = array_merge($fields,$formComponent->getLocaleFieldNames());
		}
		return $fields;
	}

	/**
	 * Display the form.
	 */
	function display() {
		foreach ($this->formComponents as $formComponent) {
			$formComponent->display($this);
		}
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('workType', $this->sequence->monograph->getWorkType());

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
			$processed = $formComponent->processEvents($this);
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
			$formComponent->execute($this, $monograph);
		}

		$monographDao->updateMonograph($monograph);

		return $monograph->getMonographId();
	}
}

?>
