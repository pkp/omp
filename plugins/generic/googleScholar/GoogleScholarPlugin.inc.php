<?php

/**
 * @file plugins/generic/googleScholar/GoogleScholarPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class GoogleScholarPlugin
 * @ingroup plugins_generic_googleScholar
 *
 * @brief Inject Google Scholar meta tags into monograph views to facilitate indexing.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class GoogleScholarPlugin extends GenericPlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		if (parent::register($category, $path, $mainContextId)) {
			if ($this->getEnabled($mainContextId)) {
				HookRegistry::register('CatalogBookHandler::book', array(&$this, 'monographView'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Get the name of the settings file to be installed on new context
	 * creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Inject Google Scholar metadata into monograph/edited volume landing page
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function monographView($hookName, $args) {
		$request = $args[0];
		$submission = $args[1];
		$templateMgr = TemplateManager::getManager($request);

		$publication = $submission->getCurrentPublication();
		$press = $request->getContext();
		$series = $templateMgr->getTemplateVars('series');
		$availableFiles = $templateMgr->getTemplateVars('availableFiles');


		// Google scholar metadata  revision
		$templateMgr->addHeader('googleScholarRevision', '<meta name="gs_meta_revision" content="1.1"/>');

		// Book/Edited volume title of the submission
		$templateMgr->addHeader('googleScholarTitle', '<meta name="citation_title" content="' . htmlspecialchars($publication->getLocalizedTitle()) . '"/>');

		// Publication date
		$templateMgr->addHeader('googleScholarDate', '<meta name="citation_publication_date" content="' . strftime('%Y-%m-%d', strtotime($publication->getData('datePublished'))) . '"/>');

		// Authors in order
		$authors = $submission->getAuthors();
		$i = 0;
		foreach ($authors as $author) {
			$templateMgr->addHeader('googleScholarAuthor' . $i++, '<meta name="citation_author" content="' . htmlspecialchars($author->getFullName(false)) . '"/>');
		}

		// Abstract
		$i = 0;
		if ($abstracts = $submission->getAbstract(null)) foreach ($abstracts as $locale => $abstract) {
			$templateMgr->addHeader('googleScholarAbstract' . $i++, '<meta name="citation_abstract" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars(strip_tags($abstract)) . '"/>');
		}

		// Publication DOI
		if ($publication->getData('pub-id::doi')) {
			$templateMgr->addHeader('googleScholarPublicationDOI', '<meta name="citation_doi" content="' . htmlspecialchars($publication->getData('pub-id::doi')) . '"/>');
		}

		// Language
		if ($languages = $publication->getData('languages')) foreach ($languages as $language) {
			$templateMgr->addHeader('googleScholarLanguage', '<meta name="citation_language" content="' . htmlspecialchars($language) . '"/>');
		}

		// Subjects
		$i = 0;
		$submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO');
		/* @var $submissionSubjectDao SubmissionSubjectDAO */
		$supportedLocales = array_keys(AppLocale::getSupportedFormLocales());
		if ($subjects = $submissionSubjectDao->getSubjects($publication->getId(), $supportedLocales)) foreach ($subjects as $locale => $subjectLocale) {
			foreach ($subjectLocale as $gsKeyword) $templateMgr->addHeader('googleScholarSubject' . $i++, '<meta name="citation_keywords" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($gsKeyword) . '"/>');
		}

		// Keywords
		$i = 0;
		$submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');
		/* @var $submissionKeywordDao SubmissionKeywordDAO */
		if ($keywords = $submissionKeywordDao->getKeywords($publication->getId(), $supportedLocales)) foreach ($keywords as $locale => $keywordLocale) {
			foreach ($keywordLocale as $gsKeyword) $templateMgr->addHeader('googleScholarKeyword' . $i++, '<meta name="citation_keywords" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($gsKeyword) . '"/>');
		}

		// Publication URL and ISBN numbers
		$publicationFormats = $publication->getData('publicationFormats');
		$i = 0;
		foreach ($availableFiles as $availableFile) {
			foreach ($publicationFormats as $publicationFormat) {

				if (($availableFile->getData("chapterId") == false) && (int)$publicationFormat->getData('id') == (int)$availableFile->getData('assocId')) {
					$identificationCodes = $publicationFormat->getIdentificationCodes();
					while ($identificationCode = $identificationCodes->next()) {
						if ($identificationCode->getCode() == "02" || $identificationCode->getCode() == "15") {
							// 02 and 15: ONIX codes for ISBN-10 or ISBN-13
							$templateMgr->addHeader('googleScholarIsbn' . $i++, '<meta name="citation_isbn" content="' . htmlspecialchars($identificationCode->getValue()) . '"/>');
						}
					}
					switch ($availableFile->getData('mimetype')) {
						case 'application/pdf':
							$templateMgr->addHeader('googleScholarPdfUrl' . $i++, '<meta name="citation_pdf_url" content="' . $request->url(null, 'catalog', 'download', array($submission->getData('id'), $availableFile->getData('assocId'), $availableFile->getData('fileId'))) . '"/>');
							break;
						case 'text/xml' or 'text/html':
							$templateMgr->addHeader('googleScholarPdfUrl' . $i++, '<meta name="citation_fulltext_html_url" content="' . $request->url(null, 'catalog', 'download', array($submission->getData('id'), $availableFile->getData('assocId'), $availableFile->getData('fileId'))) . '"/>');
							break;
					}

				}
			}
		}
		// Publisher
		$templateMgr->addHeader('googleScholarPublisher', '<meta name="citation_publisher" content="' . htmlspecialchars($press->getName($press->getPrimaryLocale())) . '"/>');

		// Series ISSN (online)
		$series = $templateMgr->getTemplateVars('series');
		if ($series && $issn = $series->getOnlineISSN()) {
			$templateMgr->addHeader('googleScholarIssn', '<meta name="citation_issn" content="' . htmlspecialchars($issn) . '"/> ');
		}

		return false;
	}

	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.googleScholar.name');
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.googleScholar.description');
	}
}


