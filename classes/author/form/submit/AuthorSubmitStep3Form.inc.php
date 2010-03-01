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

class AuthorSubmitStep3Form extends AuthorSubmitForm {

	/**
	 * Constructor.
	 */
	function AuthorSubmitStep3Form($monograph) {
		parent::AuthorSubmitForm($monograph, 3);

		$press =& Request::getPress();

		// Validation checks for this form
		$this->addCheck(new FormValidatorCustom($this, 'authors', 'required', 'author.submit.form.authorRequired', create_function('$authors', 'return count($authors) > 0;')));
		$this->addCheck(new FormValidatorArray($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', array('firstName', 'lastName')));
		$this->addCheck(new FormValidatorArrayCustom($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', create_function('$email, $regExp', 'return String::regexp_match($regExp, $email);'), array(FormValidatorEmail::getRegexp()), false, array('email')));
		$this->addCheck(new FormValidatorArrayCustom($this, 'authors', 'required', 'user.profile.form.urlInvalid', create_function('$url, $regExp', 'return empty($url) ? true : String::regexp_match($regExp, $url);'), array(FormValidatorUrl::getRegexp()), false, array('url')));
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired'));
		
		// FIXME: No abstract word count implemented--Should there be?
//		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
//		$series = $seriesDao->getById($monograph->getSeriesId());
//		$abstractWordCount = $series->getAbstractWordCount();
//		if (isset($abstractWordCount) && $abstractWordCount > 0) {
//			$this->addCheck(new FormValidatorCustom($this, 'abstract', 'required', 'author.submit.form.wordCountAlert', create_function('$abstract, $wordCount', 'foreach ($abstract as $localizedAbstract) {return count(explode(" ",$localizedAbstract)) < $wordCount; }'), array($abstractWordCount)));
//		}

	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');

		if (isset($this->monograph)) {
			$monograph =& $this->monograph;
			$this->_data = array(
				'authors' => array(),
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

			$authors =& $monograph->getAuthors();
			for ($i=0, $count=count($authors); $i < $count; $i++) {
				array_push(
					$this->_data['authors'],
					array(
						'authorId' => $authors[$i]->getId(),
						'firstName' => $authors[$i]->getFirstName(),
						'middleName' => $authors[$i]->getMiddleName(),
						'lastName' => $authors[$i]->getLastName(),
						'affiliation' => $authors[$i]->getAffiliation(),
						'country' => $authors[$i]->getCountry(),
						'email' => $authors[$i]->getEmail(),
						'url' => $authors[$i]->getUrl(),
					//	FIXME: Authors don't have a competing interest function, should they?
					//	'competingInterests' => $authors[$i]->getCompetingInterests(null),
						'biography' => $authors[$i]->getBiography(null)
					)
				);
				if ($authors[$i]->getPrimaryContact()) {
					$this->setData('primaryContact', $i);
				}
			}
		}
		return parent::initData();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array(
				'authors',
				'deletedAuthors',
				'primaryContact',
				'title',
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
				'citations'
			)
		);

		// Load the series. This is used in the step 3 form to
		// determine whether or not to display indexing options.
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$this->_data['series'] =& $seriesDao->getSeries($this->monograph->getSeriesId());

		if ($this->_data['series']->getAbstractsNotRequired() == 0) {
			$this->addCheck(new FormValidatorLocale($this, 'abstract', 'required', 'author.submit.form.abstractRequired'));
		}

	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'abstract', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor');
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign_by_ref('countries', $countries);
		
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
		$monograph->setDiscipline($this->getData('discipline'), null); // Localized
		$monograph->setSubjectClass($this->getData('subjectClass'), null); // Localized
		$monograph->setSubject($this->getData('subject'), null); // Localized
		$monograph->setCoverageGeo($this->getData('coverageGeo'), null); // Localized
		$monograph->setCoverageChron($this->getData('coverageChron'), null); // Localized
		$monograph->setCoverageSample($this->getData('coverageSample'), null); // Localized
		$monograph->setType($this->getData('type'), null); // Localized
		$monograph->setLanguage($this->getData('language'));
		$monograph->setSponsor($this->getData('sponsor'), null); // Localized
		$monograph->setCitations($this->getData('citations'));
		if ($monograph->getSubmissionProgress() <= $this->step) {
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress($this->step + 1);
		}

		// Update authors
		$authors = $this->getData('authors');
		for ($i=0, $count=count($authors); $i < $count; $i++) {
			if ($authors[$i]['authorId'] > 0) {
				// Update an existing author
				$author =& $monograph->getAuthor($authors[$i]['authorId']);
				$isExistingAuthor = true;

			} else {
				// Create a new author
				$author = new Author();
				$isExistingAuthor = false;
			}

			if ($author != null) {
				$author->setMonographId($monograph->getId());
				$author->setFirstName($authors[$i]['firstName']);
				$author->setMiddleName($authors[$i]['middleName']);
				$author->setLastName($authors[$i]['lastName']);
				$author->setAffiliation($authors[$i]['affiliation']);
				$author->setCountry($authors[$i]['country']);
				$author->setEmail($authors[$i]['email']);
				$author->setUrl($authors[$i]['url']);
				if (array_key_exists('competingInterests', $authors[$i])) {
					$author->setCompetingInterests($authors[$i]['competingInterests'], null);
				}
				$author->setBiography($authors[$i]['biography'], null);
				$author->setPrimaryContact($this->getData('primaryContact') == $i ? 1 : 0);
				$author->setSequence($authors[$i]['seq']);

				if ($isExistingAuthor == false) {
					$monograph->addAuthor($author);
				}
			}
			unset($author);
		}

		// Remove deleted authors
		$deletedAuthors = explode(':', $this->getData('deletedAuthors'));
		for ($i=0, $count=count($deletedAuthors); $i < $count; $i++) {
			$monograph->removeAuthor($deletedAuthors[$i]);
		}

		parent::execute();

		// Save the monograph
		$monographDao->updateMonograph($monograph);

		return $this->monographId;
	}
}

?>
