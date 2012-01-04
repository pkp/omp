<?php

/**
 * @file classes/submission/SubmissionMetadataFormImplementation.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionMetadataFormImplementation
 * @ingroup submission
 *
 * @brief This can be used by other forms that want to
 * implement submission metadata data and form operations.
 */

class SubmissionMetadataFormImplementation {

	/** @var Form Form that use this implementation */
	var $_parentForm;

	/**
	 * Constructor.
	 *
	 * @param $parentForm Form A form that can use this form.
	 */
	function SubmissionMetadataFormImplementation($parentForm = null) {

		if (is_a($parentForm, 'Form')) {
			$this->_parentForm = $parentForm;
		} else {
			assert(false);
		}
	}

	/**
	 * Add checks to form.
	 * @param $monograph Monograph
	 */
	function addChecks(&$monograph) {

		import('lib.pkp.classes.form.validation.FormValidatorLocale');
		import('lib.pkp.classes.form.validation.FormValidatorCustom');

		// Validation checks.
		$this->_parentForm->addCheck(new FormValidatorLocale($this->_parentForm, 'title', 'required', 'submission.submit.form.titleRequired'));
		// Validates that at least one author has been added (note that authors are in grid, so Form does not
		// directly see the authors value (there is no "authors" input. Hence the $ignore parameter.
		$this->_parentForm->addCheck(new FormValidatorCustom(
			$this->_parentForm, 'authors', 'required', 'submission.submit.form.authorRequired',
			// The first parameter is ignored. This
			create_function('$ignore, $monograph', 'return count($monograph->getAuthors()) > 0;'),
			array($monograph)
		));
	}

	/**
	 * Initialize form data from current monograph.
	 * @param $monograph Monograph
	 */
	function initData(&$monograph) {
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');

		if (isset($monograph)) {
			$formData = array(
				'title' => $monograph->getTitle(null), // Localized
				'abstract' => $monograph->getAbstract(null), // Localized
				'subjectClass' => $monograph->getSubjectClass(null), // Localized
				'coverageGeo' => $monograph->getCoverageGeo(null), // Localized
				'coverageChron' => $monograph->getCoverageChron(null), // Localized
				'coverageSample' => $monograph->getCoverageSample(null), // Localized
				'type' => $monograph->getType(null), // Localized
				'sponsor' => $monograph->getSponsor(null), // Localized
				'series' => $seriesDao->getById($monograph->getSeriesId()),
				'citations' => $monograph->getCitations()
			);

			foreach ($formData as $key => $data) {
				$this->_parentForm->setData($key, $data);
			}

			// get the supported locale keys
			$locales = array_keys($this->_parentForm->supportedLocales);

			// load the persisted metadata controlled vocabularies
			$monographKeywordDao =& DAORegistry::getDAO('MonographKeywordDAO');
			$monographSubjectDao =& DAORegistry::getDAO('MonographSubjectDAO');
			$monographDisciplineDao =& DAORegistry::getDAO('MonographDisciplineDAO');
			$monographAgencyDao =& DAORegistry::getDAO('MonographAgencyDAO');
			$monographLanguageDao =& DAORegistry::getDAO('MonographLanguageDAO');

			$this->_parentForm->setData('subjects', $monographSubjectDao->getSubjects($monograph->getId(), $locales));
	 		$this->_parentForm->setData('keywords', $monographKeywordDao->getKeywords($monograph->getId(), $locales));
			$this->_parentForm->setData('disciplines', $monographDisciplineDao->getDisciplines($monograph->getId(), $locales));
			$this->_parentForm->setData('agencies', $monographAgencyDao->getAgencies($monograph->getId(), $locales));
			$this->_parentForm->setData('languages', $monographLanguageDao->getLanguages($monograph->getId(), $locales));
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {

		$userVars = array('title', 'abstract', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'subjectClass');
		$keywordInputFields = array('disciplines', 'keyword', 'agencies', 'languages', 'subjects');

		// The controlled vocabularies are multilingual and have their field names prefixed with the locale code.
		foreach (array_keys($this->_parentForm->supportedLocales) as $localeKey) {
			foreach ($keywordInputFields as $field) {
				$userVars[] = $localeKey . '-' . $field . 'Keywords';
			}
		}

		$this->_parentForm->readUserVars($userVars);
	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'abstract', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'subjectClass');
	}

	/**
	 * Save changes to monograph.
	 * @param $monograph Monograph
	 * @return Monograph
	 */
	function execute(&$monograph) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		// Update monograph
		$monograph->setTitle($this->_parentForm->getData('title'), null); // Localized
		$monograph->setAbstract($this->_parentForm->getData('abstract'), null); // Localized
		$monograph->setCoverageGeo($this->_parentForm->getData('coverageGeo'), null); // Localized
		$monograph->setCoverageChron($this->_parentForm->getData('coverageChron'), null); // Localized
		$monograph->setCoverageSample($this->_parentForm->getData('coverageSample'), null); // Localized
		$monograph->setType($this->_parentForm->getData('type'), null); // Localized
		$monograph->setSubjectClass($this->_parentForm->getData('subjectClass'), null); // Localized

		// Save the monograph
		$monographDao->updateMonograph($monograph);

		// get the supported locale keys
		$locales = array_keys($this->_parentForm->supportedLocales);

		// persist the metadata/keyword fields.
		$monographKeywordDao =& DAORegistry::getDAO('MonographKeywordDAO');
		$monographDisciplineDao =& DAORegistry::getDAO('MonographDisciplineDAO');
		$monographAgencyDao =& DAORegistry::getDAO('MonographAgencyDAO');
		$monographSubjectDao =& DAORegistry::getDAO('MonographSubjectDAO');
		$monographLanguageDao =& DAORegistry::getDAO('MonographLanguageDAO');

		$keywords = array();
		$agencies = array();
		$disciplines = array();
		$languages = array();
		$subjects = array();

		foreach ($locales as $locale) {
			$keywords[$locale] = $this->_parentForm->getData($locale . '-keywordKeywords');
			$agencies[$locale] = $this->_parentForm->getData($locale . '-agenciesKeywords');
			$disciplines[$locale] = $this->_parentForm->getData($locale . '-disciplinesKeywords');
			$languages[$locale] = $this->_parentForm->getData($locale . '-languagesKeywords');
			$subjects[$locale] = $this->_parentForm->getData($locale . '-subjectsKeywords');
		}

		// persist the controlled vocabs
		$monographKeywordDao->insertKeywords($keywords, $monograph->getId());
		$monographAgencyDao->insertAgencies($agencies, $monograph->getId());
		$monographDisciplineDao->insertDisciplines($disciplines, $monograph->getId());
		$monographLanguageDao->insertLanguages($languages, $monograph->getId());
		$monographSubjectDao->insertSubjects($subjects, $monograph->getId());

		// Resequence the authors (this ensures a primary contact).
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$authorDao->resequenceAuthors($monograph->getId());
	}
}

?>
