<?php

/**
 * @file classes/author/form/submit/AuthorSubmitStep3Form.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep3Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 3 of author monograph submission.
 */

// $Id$


import('author.form.submit.AuthorSubmitForm');
import('inserts.monographComponents.MonographComponentsInsert');

class AuthorSubmitStep3Form extends AuthorSubmitForm {

	/**
	 * Constructor.
	 */
	function AuthorSubmitStep3Form($monograph) {
		parent::AuthorSubmitForm($monograph);
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {

		if (isset($this->monograph)) {

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
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('validateId', 'submit');
		
		parent::display();
	}

	function getHelpTopicId() {
		return 'submission.indexingAndMetadata';
	}

	function getTemplateFile() {
		return 'author/submit/step3.tpl';
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

		$monographDao->updateMonograph($monograph);

		return $monograph->getMonographId();
	}
}

?>
