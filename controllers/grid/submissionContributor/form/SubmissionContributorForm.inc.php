<?php

/**
 * @file controllers/grid/submissionContributor/form/SubmissionContributorForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionContributorForm
 * @ingroup controllers_grid_submissionContributor_form
 *
 * @brief Form for adding/editing a submissionContributor
 * stores/retrieves from an associative array
 */

import('form.Form');

class SubmissionContributorForm extends Form {
	/** SubmissionContributor the submissionContributor being edited **/
	var $_submissionContributor;

	/**
	 * Constructor.
	 */
	function SubmissionContributorForm($submissionContributor) {
		parent::Form('controllers/grid/submissionContributor/form/submissionContributorForm.tpl');

		//FIXME: Author?
		assert(!$submissionContributor || is_a($submissionContributor, 'Author'));
		$this->_submissionContributor =& $submissionContributor;

		// Validation checks for this form
		//$this->addCheck(new FormValidator($this, 'editedSubmissionContributor', 'required', 'submission.submissionContributors.grid.editedSubmissionContributorRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the submissionContributor
	 * @return SubmissionContributor
	 */
	function &getSubmissionContributor() {
		return $this->_submissionContributor;
	}

	//
	// Template methods from Form
	//
	/**
	 * Initialize form data from the associated submissionContributor.
	 * @param $submissionContributor SubmissionContributor
	 */
	function initData() {
		$submissionContributor =& $this->getSubmissionContributor();

		if ( $submissionContributor ) {
			$this->_data = array(
				'authorId' => $submissionContributor->getId(),
				'firstName' => $submissionContributor->getFirstName(),
				'middleName' => $submissionContributor->getMiddleName(),
				'lastName' => $submissionContributor->getLastName(),
				'affiliation' => $submissionContributor->getAffiliation(),
				'country' => $submissionContributor->getCountry(),
				'email' => $submissionContributor->getEmail(),
				'url' => $submissionContributor->getUrl(),
				'competingInterests' => $submissionContributor->getCompetingInterests(null),
				'biography' => $submissionContributor->getBiography(null),
				'primaryContact' => $submissionContributor->getPrimaryContact(),
				// FIXME: need to implement roles
				'role' => 'Author'
			);
		}
	}

	/**
	 * Display the form.
	 */
	function display($request) {
		$submissionContributor =& $this->getSubmissionContributor();
		assert(!$submissionContributor || is_a($submissionContributor, 'Author'));

		$templateMgr =& TemplateManager::getManager();
		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign_by_ref('countries', $countries);

		parent::display($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('editedSubmissionContributor'));


	}

	/**
	 * Save submissionContributor
	 */
	function execute() {
		$submissionContributor =& $this->getSubmissionContributor();


		return true;
	}
}

?>
