<?php

/**
 * @file plugins/generic/googleScholar/GoogleScholarPlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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
				HookRegistry::register('CatalogBookHandler::view', array($this, 'monographFileView'));
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
	 * Inject Google Scholar metadata into monograph file view
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function monographFileView($hookName, $args) {
		$monograph =& $args[1];
		$publicationFormat =& $args[2];
		$submissionFile =& $args[3];

		if ($submissionFile->getFileType() == 'application/pdf') {
			$request = Application::getRequest();
			$templateMgr = TemplateManager::getManager($request);
			$press = $request->getContext();
			$chapter = $templateMgr->getTemplateVars('chapter');
			$series = $templateMgr->getTemplateVars('series');

			$templateMgr->addHeader('googleScholarRevision', '<meta name="gs_meta_revision" content="1.1"/>');
			$templateMgr->addHeader('googleScholarPressTitle', '<meta name="citation_journal_title" content="' . htmlspecialchars($press->getName($press->getPrimaryLocale())) . '"/>');
			if ($series && $issn = $series->getOnlineISSN()) {
				$templateMgr->addHeader('googleScholarIssn', '<meta name="citation_issn" content="' . htmlspecialchars($issn) . '"/> ');
			}

			$identificationCodes = $publicationFormat->getIdentificationCodes();
			while ($identificationCode = $identificationCodes->next()) {
				if ($identificationCode->getCode() == "02" || $identificationCode->getCode() == "15") {
					// 02 and 15: ONIX codes for ISBN-10 or ISBN-13
					$templateMgr->addHeader('googleScholarIsbn' . $identificationCode->getCode(), '<meta name="citation_isbn" content="' . htmlspecialchars($identificationCode->getValue()) . '"/>');
				}
			}

			foreach ($chapter?$chapter->getAuthors()->toArray():$monograph->getAuthors() as $i => $author) {
				$templateMgr->addHeader('googleScholarAuthor' . $i, '<meta name="citation_author" content="' . htmlspecialchars($author->getFullName(false)) . '"/>');
				if ($affiliation = htmlspecialchars($author->getAffiliation($monograph->getLocale()))) {
					$templateMgr->addHeader('googleScholarAuthor' . $i . 'Affiliation', '<meta name="citation_author_institution" content="' . $affiliation . '"/>');
				}
			}

			if ($chapter) {
				$templateMgr->addHeader('googleScholarTitle', '<meta name="citation_title" content="' . htmlspecialchars($chapter->getTitle($monograph->getLocale())) . '"/>');
			} else {
				$templateMgr->addHeader('googleScholarTitle', '<meta name="citation_title" content="' . htmlspecialchars($monograph->getTitle($monograph->getLocale())) . '"/>');
			}

			$templateMgr->addHeader('googleScholarDate', '<meta name="citation_publication_date" content="' . strftime('%Y/%m/%d', strtotime($monograph->getDatePublished())) . '"/>');

			foreach((array) $templateMgr->getTemplateVars('pubIdPlugins') as $pubIdPlugin) {
				if ($pubId = $monograph->getStoredPubId($pubIdPlugin->getPubIdType())) {
					$templateMgr->addHeader('googleScholarPubId' . $pubIdPlugin->getPubIdDisplayType(), '<meta name="citation_' . htmlspecialchars(strtolower($pubIdPlugin->getPubIdDisplayType())) . '" content="' . htmlspecialchars($pubId) . '"/>');
				}
			}

			if ($language = $monograph->getLanguage()) $templateMgr->addHeader('googleScholarLanguage', '<meta name="citation_language" content="' . htmlspecialchars($language) . '"/>');

			$i=0;
			$submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO');
			$supportedLocales = array_keys(AppLocale::getSupportedFormLocales());
			if ($subjects = $submissionSubjectDao->getSubjects($monograph->getId(), $supportedLocales)) foreach ($subjects as $locale => $subjectLocale) {
				foreach ($subjectLocale as $gsKeyword) $templateMgr->addHeader('googleScholarSubject' . $i++, '<meta name="citation_keywords" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($gsKeyword) . '"/>');
			}

			$i=0;
			$submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');
			if ($keywords = $submissionKeywordDao->getKeywords($monograph->getId(), $supportedLocales)) foreach ($keywords as $locale => $keywordLocale) {
				foreach ($keywordLocale as $gsKeyword) $templateMgr->addHeader('googleScholarKeyword' . $i++, '<meta name="citation_keywords" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($gsKeyword) . '"/>');
			}

			$templateMgr->addHeader('googleScholarPdfUrl' . $i++, '<meta name="citation_pdf_url" content="' . $request->url(null, 'catalog', 'download', array($monograph->getBestId(), $publicationFormat->getId(), $submissionFile->getFileIdAndRevision())) . '"/>');
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


