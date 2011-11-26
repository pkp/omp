<?php

/**
 * @file classes/submission/SubmissionMetadataFormImplementation.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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

			foreach ($formData as $key => $data) {
				$this->_parentForm->setData($key, $data);
			}

			// load the persisted metadata controlled vocabularies
			$monographKeywordDao =& DAORegistry::getDAO('MonographKeywordDAO');
			$this->_parentForm->setData('keywords', $monographKeywordDao->getKeywords($monograph->getId()));

			$monographDisciplineDao =& DAORegistry::getDAO('MonographDisciplineDAO');
			$this->_parentForm->setData('disciplines', $monographDisciplineDao->getDisciplines($monograph->getId()));

			$monographAgencyDao =& DAORegistry::getDAO('MonographAgencyDAO');
			$this->_parentForm->setData('agencies', $monographAgencyDao->getAgencies($monograph->getId()));
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->_parentForm->readUserVars(
			array(
				'title',
				'abstract',
				'disciplinesKeywords',
				'keywordKeywords',
				'agenciesKeywords',
			)
		);
	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'abstract');
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

		// Save the monograph
		$monographDao->updateMonograph($monograph);

		// persist the metadata/keyword fields.
		$monographKeywordDao =& DAORegistry::getDAO('MonographKeywordDAO');
		$monographKeywordDao->insertKeywords($this->_parentForm->getData('keywordKeywords'), $monograph->getId());

		$monographDisciplineDao =& DAORegistry::getDAO('MonographDisciplineDAO');
		$monographDisciplineDao->insertDisciplines($this->_parentForm->getData('disciplinesKeywords'), $monograph->getId());

		$monographAgencyDao =& DAORegistry::getDAO('MonographAgencyDAO');
		$monographAgencyDao->insertAgencies($this->_parentForm->getData('agenciesKeywords'), $monograph->getId());

		// Resequence the authors (this ensures a primary contact).
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$authorDao->resequenceAuthors($monograph->getId());
	}
}

?>
