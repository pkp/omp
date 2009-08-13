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

	var $formComponent;

	/**
	 * Constructor.
	 */
	function AuthorSubmitStep2Form($monograph) {
		parent::AuthorSubmitForm($monograph);

		// Validation checks for this form
		$this->addCheck(new FormValidatorCustom($this, 'contributors', 'required', 'author.submit.form.authorRequired', create_function('$contributors', 'return count($contributors) > 0;')));
		$this->addCheck(new FormValidatorArray($this, 'contributors', 'required', 'author.submit.form.authorRequiredFields', array('firstName', 'lastName', 'email')));
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired'));
		$this->formComponent = new MonographComponentsInsert($this->monograph);
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {

		if (isset($this->monograph)) {

			$this->_data = array_merge($this->_data, $this->formComponent->initData($this));
			$monograph =& $this->monograph;
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

		$userVars =& array_merge($this->formComponent->listUserVars(),$userVars);

		$this->readUserVars($userVars);
	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		$fields = array('title', 'abstract', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor');
		$fields = array_merge($fields, $this->formComponent->getLocaleFieldNames());

		return $fields;
	}

	/**
	 * Display the form.
	 */
	function display() {
		$this->formComponent->display($this);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('workType', $this->monograph->getWorkType());

		parent::display();
	}
	function getHelpTopicId() {
		return 'submission.indexingAndMetadata';
	}
	function getTemplateFile() {
		return 'author/submit/step2.tpl';
	}
	function processEvents() {
		return $this->formComponent->processEvents($this);
	}
	/**
	 * Save changes to monograph.
	 * @return int the monograph ID
	 */
	function execute() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$authorDao =& DAORegistry::getDAO('AuthorDAO');

		// Update monograph
		$monograph =& $this->monograph;
		$monograph->setTitle($this->getData('title'), null); // Localized
		$monograph->setAbstract($this->getData('abstract'), null); // Localized
		$monograph->setDiscipline($this->getData('discipline'), null); // Localized
		$monograph->setSubject($this->getData('subject'), null); // Localized
		$monograph->setSubjectClass($this->getData('subjectClass'), null); // Localized
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

		$this->formComponent->execute($this, $monograph);

		$monographDao->updateMonograph($monograph);

		return $monograph->getMonographId();
	}
}

?>
