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
		if ($roleId != null && $roleId == ROLE_ID_REVIEWER) {
			$this->canViewAuthors = false;
		}

		$this->author = $author;
		$this->addCheck(new FormValidatorPost($this));

		parent::Form('submission/metadata/authorMetadataEdit.tpl');

	}

	/**
	 * Initialize form data from current article.
	 */
	function initData() {

		if (isset($this->author)) {
			$author =& $this->author;
			$this->_data = array (
						'authorId' => $author->getAuthorId(),
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
				if ($author->getPrimaryContact()) {
					$this->setData('primaryContact', $i);
				}
		}
	}

	/**
	 * Get the field names for which data can be localized
	 * @return array
	 */
	function getLocaleFieldNames() {

		return array(
			'title', 'abstract', 'coverPageAltText', 'showCoverPage', 'hideCoverPageToc', 'hideCoverPageAbstract', 'originalFileName', 'fileName', 'width', 'height',
			'discipline', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor'
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
/*
		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$templateMgr->assign('countries', $countryDao->getCountries());

		$templateMgr->assign('helpTopicId','submission.indexingAndMetadata');
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

		$sectionDao =& DAORegistry::getDAO('SectionDAO');
		$section =& $sectionDao->getSection($this->author->getSectionId());
		if (!$section->getAbstractsNotRequired()) {
			$this->addCheck(new FormValidatorLocale($this, 'abstract', 'required', 'author.submit.form.abstractRequired'));
		}

	}

	/**
	 * Save changes to article.
	 * @return int the article ID
	 */
	function execute() {
		$monographDao =& DAORegistry::getDAO('ArticleDAO');
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$sectionDao =& DAORegistry::getDAO('SectionDAO');

		$monograph =& $this->author;
		$monograph->setTitle($this->getData('title'), null); // Localized

		$section =& $sectionDao->getSection($monograph->getSectionId());
		$monograph->setAbstract($this->getData('abstract'), null); // Localized

		import('file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
		if ($publicFileManager->uploadedFileExists('coverPage')) {
			$journal = Request::getJournal();
			$originalFileName = $publicFileManager->getUploadedFileName('coverPage');
			$newFileName = 'cover_article_' . $this->getData('articleId') . '_' . $this->getFormLocale() . '.' . $publicFileManager->getExtension($originalFileName);
			$publicFileManager->uploadJournalFile($journal->getJournalId(), 'coverPage', $newFileName);
			$monograph->setOriginalFileName($publicFileManager->truncateFileName($originalFileName, 127), $this->getFormLocale());
			$monograph->setFileName($newFileName, $this->getFormLocale());

			// Store the image dimensions.
			list($width, $height) = getimagesize($publicFileManager->getJournalFilesPath($journal->getJournalId()) . '/' . $newFileName);
			$monograph->setWidth($width, $this->getFormLocale());
			$monograph->setHeight($height, $this->getFormLocale());
		}

		$monograph->setCoverPageAltText($this->getData('coverPageAltText'), null); // Localized
		$showCoverPage = array_map(create_function('$arrayElement', 'return (int)$arrayElement;'), (array) $this->getData('showCoverPage'));
		foreach (array_keys($this->getData('coverPageAltText')) as $locale) {
			if (!array_key_exists($locale, $showCoverPage)) {
				$showCoverPage[$locale] = 0;
			}
		}
		$monograph->setShowCoverPage($showCoverPage, null); // Localized

		$hideCoverPageToc = array_map(create_function('$arrayElement', 'return (int)$arrayElement;'), (array) $this->getData('hideCoverPageToc'));
		foreach (array_keys($this->getData('coverPageAltText')) as $locale) {
			if (!array_key_exists($locale, $hideCoverPageToc)) {
				$hideCoverPageToc[$locale] = 0;
			}
		}
		$monograph->setHideCoverPageToc($hideCoverPageToc, null); // Localized

		$hideCoverPageAbstract = array_map(create_function('$arrayElement', 'return (int)$arrayElement;'), (array) $this->getData('hideCoverPageAbstract'));
		foreach (array_keys($this->getData('coverPageAltText')) as $locale) {
			if (!array_key_exists($locale, $hideCoverPageAbstract)) {
				$hideCoverPageAbstract[$locale] = 0;
			}
		}
		$monograph->setHideCoverPageAbstract($hideCoverPageAbstract, null); // Localized

		$monograph->setDiscipline($this->getData('discipline'), null); // Localized
		$monograph->setSubjectClass($this->getData('subjectClass'), null); // Localized
		$monograph->setSubject($this->getData('subject'), null); // Localized
		$monograph->setCoverageGeo($this->getData('coverageGeo'), null); // Localized
		$monograph->setCoverageChron($this->getData('coverageChron'), null); // Localized
		$monograph->setCoverageSample($this->getData('coverageSample'), null); // Localized
		$monograph->setType($this->getData('type'), null); // Localized
		$monograph->setLanguage($this->getData('language')); // Localized
		$monograph->setSponsor($this->getData('sponsor'), null); // Localized
		$monograph->setHideAuthor($this->getData('hideAuthor') ? $this->getData('hideAuthor') : 0);

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
				$author->setFirstName($authors[$i]['firstName']);
				$author->setMiddleName($authors[$i]['middleName']);
				$author->setLastName($authors[$i]['lastName']);
				$author->setAffiliation($authors[$i]['affiliation']);
				$author->setCountry($authors[$i]['country']);
				$author->setEmail($authors[$i]['email']);
				$author->setUrl($authors[$i]['url']);
				if (array_key_exists('competingInterests', $authors[$i])) {
					$author->setCompetingInterests($authors[$i]['competingInterests'], null); // Localized
				}
				$author->setBiography($authors[$i]['biography'], null); // Localized
				$author->setPrimaryContact($this->getData('primaryContact') == $i ? 1 : 0);
				$author->setSequence($authors[$i]['seq']);

				if ($isExistingAuthor == false) {
					$monograph->addAuthor($author);
				}
			}
		}

		// Remove deleted authors
		$deletedAuthors = explode(':', $this->getData('deletedAuthors'));
		for ($i=0, $count=count($deletedAuthors); $i < $count; $i++) {
			$monograph->removeAuthor($deletedAuthors[$i]);
		}

		// Save the article
		$monographDao->updateArticle($monograph);

		// Update search index
		import('search.ArticleSearchIndex');
		ArticleSearchIndex::indexArticleMetadata($monograph);

		return $monograph->getArticleId();
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
