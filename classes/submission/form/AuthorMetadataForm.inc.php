<?php

/**
 * @file classes/submission/form/AuthorMetadataForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorMetadataForm
 * @ingroup submission_form
 *
 * @brief Form to change metadata information for a submission.
 */

// $Id$


import('form.Form');

class AuthorMetadataForm extends Form {
	/** @var Article current article */
	var $author;

	/** @var boolean can edit metadata */
	var $canEdit;

	/** @var boolean can view authors */
	var $canViewAuthors;

	/**
	 * Constructor.
	 */
	function AuthorMetadataForm($author) {
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roleId = $roleDao->getRoleIdFromPath(Request::getRequestedPage());

//		$copyAssignmentDao =& DAORegistry::getDAO('CopyAssignmentDAO');
/*
		$user =& Request::getUser();
		$roleId = $roleDao->getRoleIdFromPath(Request::getRequestedPage());

		// If the user is an editor of this article, make the form editable.
		$this->canEdit = false;
		if ($roleId != null && ($roleId == ROLE_ID_EDITOR || $roleId == ROLE_ID_SECTION_EDITOR)) {
			$this->canEdit = true;
		}

		// If the user is an author and the article hasn't passed the Copyediting stage, make the form editable.
		if ($roleId == ROLE_ID_AUTHOR) {
			$copyAssignment = $copyAssignmentDao->getCopyAssignmentByArticleId($monograph->getArticleId());
			if ($monograph->getStatus() != STATUS_PUBLISHED && ($copyAssignment == null || $copyAssignment->getDateCompleted() == null)) {
				$this->canEdit = true;
			}
		}

		// Copy editors are also allowed to edit metadata, but only if they have
		// a current assignment to the article.
		if ($roleId != null && ($roleId == ROLE_ID_COPYEDITOR)) {
			$copyAssignment = $copyAssignmentDao->getCopyAssignmentByArticleId($monograph->getArticleId());
			if ($copyAssignment != null && $monograph->getStatus() != STATUS_PUBLISHED) {
				if ($copyAssignment->getDateNotified() != null && $copyAssignment->getDateFinalCompleted() == null) {
					$this->canEdit = true;
				}
			}
		}

		if ($this->canEdit) {
			parent::Form('submission/metadata/metadataEdit.tpl');
			$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired'));
		} else {
			parent::Form('submission/metadata/metadataView.tpl');
		}
*/
		// If the user is a reviewer of this article, do not show authors.
		$this->canViewAuthors = true;
	/*	if ($roleId != null && $roleId == ROLE_ID_REVIEWER) {
			$this->canViewAuthors = false;
		}
*/
		$this->author = $author;
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorArray($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', array('firstName', 'lastName', 'email')));

		parent::Form('submission/metadata/authorMetadataEdit.tpl');

	}

	/**
	 * Initialize form data from current article.
	 */
	function initData() {

		if (isset($this->author)) {
			$author =& $this->author;
			$this->_data = array (
						'monographId' => $author->getMonographId(),
						'authorId' => $author->getId(),
						'firstName' => $author->getFirstName(),
						'middleName' => $author->getMiddleName(),
						'lastName' => $author->getLastName(),
						'affiliation' => $author->getAffiliation(),
						'country' => $author->getCountry(),
						'countryLocalized' => $author->getCountryLocalized(),
						'email' => $author->getEmail(),
						'url' => $author->getUrl(),
						'biography' => $author->getBiography(null) // Localized
					);
		} else {

			$this->_data = array (
						'monographId' => $this->monographId,
						'authorId' => null
				    );
		}
	}

	/**
	 * Get the field names for which data can be localized
	 * @return array
	 */
	function getLocaleFieldNames() {

		return array(
			'backUrl','monographId','authorId','firstName','middleName','lastName','affiliation','country','email','url','biography'
		);
	}

	/**
	 * Display the form.
	 */
	function display() {

/*		$press =& Request::getPress();
		$settingsDao =& DAORegistry::getDAO('JournalSettingsDAO');
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$sectionDao =& DAORegistry::getDAO('SectionDAO');

*/		$templateMgr =& TemplateManager::getManager();
/*		$templateMgr->assign('articleId', isset($this->author)?$this->author->getArticleId():null);
		$templateMgr->assign('journalSettings', $settingsDao->getJournalSettings($journal->getJournalId()));
		$templateMgr->assign('rolePath', Request::getRequestedPage());
*/		$templateMgr->assign('canViewAuthors', $this->canViewAuthors);

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$templateMgr->assign('countries', $countryDao->getCountries());
		$templateMgr->assign('backUrl', Request::getCompleteUrl());
/*		$templateMgr->assign('helpTopicId','submission.indexingAndMetadata');
		if ($this->author) {
			$templateMgr->assign_by_ref('section', $sectionDao->getSection($this->author->getSectionId()));
		}

		if ($this->canEdit) {
			import('article.Article');
			$hideAuthorOptions = array(
				AUTHOR_TOC_DEFAULT => Locale::Translate('editor.article.hideTocAuthorDefault'),
				AUTHOR_TOC_HIDE => Locale::Translate('editor.article.hideTocAuthorHide'),
				AUTHOR_TOC_SHOW => Locale::Translate('editor.article.hideTocAuthorShow')
			);
			$templateMgr->assign('hideAuthorOptions', $hideAuthorOptions);
		}
*/
		parent::display();
	}


	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array (
					'backUrl',
					'authorId',
					'firstName',
					'middleName',
					'lastName',
					'affiliation',
					'country',
					'countryLocalized',
					'email',
					'url',
					'biography'
			)
		);

//		$sectionDao =& DAORegistry::getDAO('SectionDAO');
//		$section =& $sectionDao->getSection($this->author->getSectionId());
//		if (!$section->getAbstractsNotRequired()) {
//			$this->addCheck(new FormValidatorLocale($this, 'abstract', 'required', 'author.submit.form.abstractRequired'));
//		}

	}

	/**
	 * Save changes to article.
	 * @return int the article ID
	 */
	function execute() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$authorDao =& DAORegistry::getDAO('AuthorDAO');

		// Update authors
		if ($this->author == null) {
			// Update an existing author
			$author = new Author();
		} else {
			$author = $this->author;
			
		}
		$this->readInputData();

		$author->setMonographId($this->getData('monographId'));
		$author->setId($this->getData('authorId'));
		$author->setFirstName($this->getData('firstName'));
		$author->setMiddleName($this->getData('middleName'));
		$author->setLastName($this->getData('lastName'));
		$author->setAffiliation($this->getData('affiliation'));
		$author->setCountry($this->getData('country'));
		$author->setEmail($this->getData('email'));
		$author->setUrl($this->getData('url'));
		$author->setBiography($this->getData('biography'), null); // Localized

		// Save the author
		$authorDao->updateAuthor($author);

		// Update search index
//		import('search.ArticleSearchIndex');
//		ArticleSearchIndex::indexArticleMetadata($monograph);

		Request::redirectUrl($this->getData('backUrl'));


		return $author->getId();
	}

	/**
	 * Determine whether or not the current user is allowed to edit metadata.
	 * @return boolean
	 */
	function getCanEdit() {
		return $this->canEdit;
	}
}

?>
