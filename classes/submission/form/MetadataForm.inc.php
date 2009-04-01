<?php

/**
 * @file classes/submission/form/MetadataForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MetadataForm
 * @ingroup submission_form
 *
 * @brief Form to change metadata information for a submission.
 */

// $Id$


import('form.Form');
import('inserts.monographComponents.MonographComponentsInsert');

class MetadataForm extends Form {
	/** @var Monograph current monograph */
	var $monograph;

	/** @var boolean can edit metadata */
	var $canEdit;

	/** @var boolean can view authors */
	var $canViewAuthors;
	var $formComponents;
	/**
	 * Constructor.
	 */
	function MetadataForm(&$monograph) {
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roleId = $roleDao->getRoleIdFromPath(Request::getRequestedPage());

		$this->formComponents =& new MonographComponentsInsert($monograph, $this);

//		$copyAssignmentDao =& DAORegistry::getDAO('CopyAssignmentDAO');
/*
		$user =& Request::getUser();
		$roleId = $roleDao->getRoleIdFromPath(Request::getRequestedPage());

		// If the user is an editor of this monograph, make the form editable.
		$this->canEdit = false;
		if ($roleId != null && ($roleId == ROLE_ID_EDITOR || $roleId == ROLE_ID_SECTION_EDITOR)) {
			$this->canEdit = true;
		}

		// If the user is an author and the monograph hasn't passed the Copyediting stage, make the form editable.
		if ($roleId == ROLE_ID_AUTHOR) {
			$copyAssignment = $copyAssignmentDao->getCopyAssignmentByMonographId($monograph->getMonographId());
			if ($monograph->getStatus() != STATUS_PUBLISHED && ($copyAssignment == null || $copyAssignment->getDateCompleted() == null)) {
				$this->canEdit = true;
			}
		}

		// Copy editors are also allowed to edit metadata, but only if they have
		// a current assignment to the monograph.
		if ($roleId != null && ($roleId == ROLE_ID_COPYEDITOR)) {
			$copyAssignment = $copyAssignmentDao->getCopyAssignmentByMonographId($monograph->getMonographId());
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
		// If the user is a reviewer of this monograph, do not show authors.
		$this->canViewAuthors = true;
		if ($roleId != null && $roleId == ROLE_ID_REVIEWER) {
			$this->canViewAuthors = false;
		}

		$this->monograph =& $monograph;
		$this->addCheck(new FormValidatorPost($this));

		parent::Form('submission/metadata/metadataEdit.tpl');

	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		$this->_data = array();
		$this->_data = array_merge($this->_data, $this->formComponents->initData($this));
		if (isset($this->monograph)) {
			$monograph =& $this->monograph;
			if (!is_array($this->_data))
				$this->_data = array($this->_data);
			$this->_data = array_merge($this->_data,array(

				'title' => $monograph->getTitle(null), // Localized
				'abstract' => $monograph->getAbstract(null), // Localized
				'coverPageAltText' => $monograph->getCoverPageAltText(null), // Localized
				'showCoverPage' => $monograph->getShowCoverPage(null), // Localized
				'hideCoverPageToc' => $monograph->getHideCoverPageToc(null), // Localized
				'hideCoverPageAbstract' => $monograph->getHideCoverPageAbstract(null), // Localized
				'originalFileName' => $monograph->getOriginalFileName(null), // Localized
				'fileName' => $monograph->getFileName(null), // Localized
				'width' => $monograph->getWidth(null), // Localized
				'height' => $monograph->getHeight(null), // Localized
				'discipline' => $monograph->getDiscipline(null), // Localized
				'subjectClass' => $monograph->getSubjectClass(null), // Localized
				'subject' => $monograph->getSubject(null), // Localized
				'coverageGeo' => $monograph->getCoverageGeo(null), // Localized
				'coverageChron' => $monograph->getCoverageChron(null), // Localized
				'coverageSample' => $monograph->getCoverageSample(null), // Localized
				'type' => $monograph->getType(null), // Localized
				'language' => $monograph->getLanguage(),
				'sponsor' => $monograph->getSponsor(null), // Localized
				'hideAuthor' => $monograph->getHideAuthor()

			));

		}

	}

	/**
	 * Get the field names for which data can be localized
	 * @return array
	 */
	function getLocaleFieldNames() {

		$fields = array(
			'title', 'abstract', 'coverPageAltText', 'showCoverPage', 'hideCoverPageToc', 'hideCoverPageAbstract', 'originalFileName', 'fileName', 'width', 'height',
			'discipline', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor'
		);
		return array_merge($fields, $this->formComponents->getLocaleFieldNames());
	}

	/**
	 * Display the form.
	 */
	function display() {
		$this->formComponents->display($this);
		$press =& Request::getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
/*		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$sectionDao =& DAORegistry::getDAO('SectionDAO');

*/		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', isset($this->monograph)?$this->monograph->getMonographId():null);
		$templateMgr->assign('pressSettings', $settingsDao->getPressSettings($press->getId()));
		$templateMgr->assign('rolePath', Request::getRequestedPage());
		$templateMgr->assign('canViewAuthors', $this->canViewAuthors);
/*
		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$templateMgr->assign('countries', $countryDao->getCountries());

		$templateMgr->assign('helpTopicId','submission.indexingAndMetadata');
		if ($this->monograph) {
			$templateMgr->assign_by_ref('section', $sectionDao->getSection($this->monograph->getSectionId()));
		}

		if ($this->canEdit) {
			import('monograph.Monograph');
			$hideAuthorOptions = array(
				AUTHOR_TOC_DEFAULT => Locale::Translate('editor.monograph.hideTocAuthorDefault'),
				AUTHOR_TOC_HIDE => Locale::Translate('editor.monograph.hideTocAuthorHide'),
				AUTHOR_TOC_SHOW => Locale::Translate('editor.monograph.hideTocAuthorShow')
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
		$userVars = array (
				'monographId',
				'deletedAuthors',
				'primaryContact',
				'title',
				'abstract',
				'coverPageAltText',
				'showCoverPage',
				'hideCoverPageToc',
				'hideCoverPageAbstract',
				'originalFileName',
				'fileName',
				'width',
				'height',
				'discipline',
				'subjectClass',
				'subject',
				'coverageGeo',
				'coverageChron',
				'coverageSample',
				'type',
				'language',
				'sponsor',
				'hideAuthor'
			);

		$this->readUserVars(array_merge($userVars, $this->formComponents->listUserVars()));
//		$sectionDao =& DAORegistry::getDAO('SectionDAO');
//		$section =& $sectionDao->getSection($this->monograph->getSectionId());
//		if (!$section->getAbstractsNotRequired()) {
//			$this->addCheck(new FormValidatorLocale($this, 'abstract', 'required', 'author.submit.form.abstractRequired'));
//		}

	}

	/**
	 * Save changes to monograph.
	 * @return int the monograph ID
	 */
	function execute() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		// Update monograph
		$monograph =& $this->monograph;
		$monograph->setTitle($this->getData('title'), null); // Localized

		$this->formComponents->execute($this, $monograph);

//		$section =& $sectionDao->getSection($monograph->getSectionId());
		$monograph->setAbstract($this->getData('abstract'), null); // Localized

/*		import('file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
		if ($publicFileManager->uploadedFileExists('coverPage')) {
			$journal = Request::getJournal();
			$originalFileName = $publicFileManager->getUploadedFileName('coverPage');
			$newFileName = 'cover_monograph_' . $this->getData('monographId') . '_' . $this->getFormLocale() . '.' . $publicFileManager->getExtension($originalFileName);
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
*/
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
/*		$authors = $this->getData('authors');
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

*/		// Save the monograph
		$monographDao->updateMonograph($monograph);

		// Update search index
//		import('search.MonographSearchIndex');
//		MonographSearchIndex::indexMonographMetadata($monograph);

		return $monograph->getMonographId();
	}

	/**
	 * Determine whether or not the current user is allowed to edit metadata.
	 * @return boolean
	 */
	function getCanEdit() {
		return $this->canEdit;
	}
	function processEvents() {
		return $this->formComponents->processEvents($this);
	}
}

?>