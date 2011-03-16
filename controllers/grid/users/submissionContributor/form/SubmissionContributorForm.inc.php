<?php

/**
 * @file controllers/grid/users/submissionContributor/form/SubmissionContributorForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionContributorForm
 * @ingroup controllers_grid_users_submissionContributor_form
 *
 * @brief Form for adding/editing a submissionContributor
 */

import('lib.pkp.classes.form.Form');

class SubmissionContributorForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monograph;

	/** SubmissionContributor the submissionContributor being edited **/
	var $_submissionContributor;

	/**
	 * Constructor.
	 */
	function SubmissionContributorForm($monograph, $submissionContributor) {
		parent::Form('controllers/grid/users/submissionContributor/form/submissionContributorForm.tpl');
		$this->setMonograph($monograph);
		$this->setSubmissionContributor($submissionContributor);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'firstName', 'required', 'submission.submit.form.authorRequiredFields'));
		$this->addCheck(new FormValidator($this, 'lastName', 'required', 'submission.submit.form.authorRequiredFields'));
		$this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'installer.form.emailRequired'));
		$this->addCheck(new FormValidatorUrl($this, 'url', 'optional', 'user.profile.form.urlInvalid'));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	* Get the submissionContributor
	* @return SubmissionContributor
	*/
	function getSubmissionContributor() {
		return $this->_submissionContributor;
	}

	/**
	* Set the submissionContributor
	* @param @submissionContributor SubmissionContributor
	*/
	function setSubmissionContributor($submissionContributor) {
		$this->_submissionContributor =& $submissionContributor;
	}

	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the MonographId
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
	}


	//
	// Overridden template methods
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
				'affiliation' => $submissionContributor->getAffiliation(Locale::getLocale()),
				'country' => $submissionContributor->getCountry(),
				'email' => $submissionContributor->getEmail(),
				'url' => $submissionContributor->getUrl(),
				'userGroupId' => $submissionContributor->getUserGroupId(),
				'biography' => $submissionContributor->getBiography(Locale::getLocale()),
				'primaryContact' => $submissionContributor->getPrimaryContact()
			);
		}
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$submissionContributor =& $this->getSubmissionContributor();

		$templateMgr =& TemplateManager::getManager();
		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign_by_ref('countries', $countries);

		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByRoleId($context->getId(), ROLE_ID_AUTHOR);
		$authorUserGroups = array();
		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$authorUserGroups[$userGroup->getId()] = $userGroup->getLocalizedName();
			unset($userGroup);
		}
		$templateMgr->assign_by_ref('authorUserGroups', $authorUserGroups);

		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'authorId',
			'firstName',
			'middleName',
			'lastName',
			'affiliation',
			'country',
			'email',
			'url',
			'userGroupId',
			'biography',
			'primaryContact'
		));
	}

	/**
	 * Save submissionContributor
	 * @see Form::execute()
	 * @see Form::execute()
	 */
	function execute() {
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$monograph = $this->getMonograph();

		$submissionContributor =& $this->getSubmissionContributor();
		if (!$submissionContributor) {
			// this is a new submission contributor
			$submissionContributor = new Author();
			$submissionContributor->setMonographId($monograph->getId());
			$existingSubmissionContributor = false;
		} else {
			$existingSubmissionContributor = true;
		}

		assert($monograph->getId() == $submissionContributor->getMonographId());

		$submissionContributor->setFirstName($this->getData('firstName'));
		$submissionContributor->setMiddleName($this->getData('middleName'));
		$submissionContributor->setLastName($this->getData('lastName'));
		$submissionContributor->setAffiliation($this->getData('affiliation'), Locale::getLocale()); // localized
		$submissionContributor->setCountry($this->getData('country'));
		$submissionContributor->setEmail($this->getData('email'));
		$submissionContributor->setUrl($this->getData('url'));
		$submissionContributor->setUserGroupId($this->getData('userGroupId'));
		$submissionContributor->setBiography($this->getData('biography'), Locale::getLocale()); // localized
		$submissionContributor->setPrimaryContact(($this->getData('primaryContact') ? true : false));

		if ($existingSubmissionContributor) {
			$authorDao->updateAuthor($submissionContributor);
			$authorId = $submissionContributor->getId();
		} else {
			$authorId = $authorDao->insertAuthor($submissionContributor);
		}

		return $authorId;
	}
}

?>
