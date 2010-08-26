<?php

/**
 * @file classes/author/form/submit/SubmissionSubmitStep3Form.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep3Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 3 of author monograph submission.
 */

// $Id$


import('classes.submission.form.submit.SubmissionSubmitForm');

class SubmissionSubmitStep3Form extends SubmissionSubmitForm {

	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep3Form($monograph) {
		parent::SubmissionSubmitForm($monograph, 3);

		$press =& Request::getPress();

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'submission.submit.form.titleRequired'));
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');

		if (isset($this->monograph)) {
			$monograph =& $this->monograph;
			$this->_data = array(
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
				'series' => $seriesDao->getById($monograph->getSeriesId()),
				'citations' => $monograph->getCitations()
			);

		}
		return parent::initData();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array(
				'title',
				'abstract',
				'disciplinesKeywords',
				'keywordKeywords',
				'agenciesKeywords',
			)
		);

		// Load the series. This is used in the step 3 form to
		// determine whether or not to display indexing options.
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$this->_data['series'] =& $seriesDao->getById($this->monograph->getSeriesId(), $this->monograph->getPressId());
	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'abstract');
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();

		$templateMgr->assign_by_ref('countries', $countries);
		$templateMgr->assign('monographId', $this->monographId);
		$templateMgr->assign('isEditedVolume', $this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME ? true : false);

		if (Request::getUserVar('addAuthor') || Request::getUserVar('delAuthor')  || Request::getUserVar('moveAuthor')) {
			$templateMgr->assign('scrollToAuthor', true);
		}

		parent::display();
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

		$monograph->setSupportingAgencies(implode(", ", $this->getData('agenciesKeywords')), null); // Localized
		$monograph->setDiscipline(implode(", ", $this->getData('disciplinesKeywords')), null); // Localized
		$monograph->setSubject(implode(", ",$this->getData('keywordKeywords')), null); // Localized

		if ($monograph->getSubmissionProgress() <= $this->step) {
			$monograph->setDateSubmitted(Core::getCurrentDate());
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress(0);
		}

		// Save the monograph
		$monographDao->updateMonograph($monograph);

		return $this->monographId;
	}
}

?>
