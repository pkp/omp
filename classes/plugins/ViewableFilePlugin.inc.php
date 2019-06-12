<?php

/**
 * @file classes/plugins/ViewableFilePlugin.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ViewableFilePlugin
 * @ingroup plugins
 *
 * @brief Abstract class for article galley plugins
 */

import('lib.pkp.classes.plugins.PKPViewableFilePlugin');

abstract class ViewableFilePlugin extends PKPViewableFilePlugin {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path) {
		if (!parent::register($category, $path)) return false;

		if ($this->getEnabled()) {
			HookRegistry::register('CatalogBookHandler::view', array($this, 'callback'));
		}
		return true;
	}

	/**
	 * Display this galley in some manner.
	 *
	 * @param $publishedSubmission PublishedSubmission
	 * @param $submissionFile SubmissionFile
	 */
	function displaySubmissionFile($publishedSubmission, $publicationFormat, $submissionFile) {
		$templateMgr = TemplateManager::getManager($this->getRequest());
		$templateFilename = $this->getTemplateFilename();
		if ($templateFilename === null) return '';

		// Set up the viewable file template variables.
		$submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$genreDao = DAORegistry::getDAO('GenreDAO');

		// Find a good candidate for a publication date
		$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO');
		$publicationDates = $publicationDateDao->getByPublicationFormatId($publicationFormat->getId());
		$bestPublicationDate = null;
		while ($publicationDate = $publicationDates->next()) {
			// 11: Date of first publication; 01: Publication date
			if ($publicationDate->getRole() != '11' && $publicationDate->getRole() != '01') continue;

			if ($bestPublicationDate) $bestPublicationDate = min($bestPublicationDate, $publicationDate->getUnixTime());
			else $bestPublicationDate = $publicationDate->getUnixTime();
		}

		$templateMgr->assign(array(
			'submissionKeywords' => $submissionKeywordDao->getKeywords($publishedSubmission->getId(), array_merge(array(AppLocale::getLocale()), array_keys(AppLocale::getSupportedLocales()))),
			'publishedSubmission' => $publishedSubmission,
			'publicationFormat' => $publicationFormat,
			'submissionFile' => $submissionFile,
			'chapter' => $chapterDao->getChapter($submissionFile->getData('chapterId')),
			'genre' => $genreDao->getById($submissionFile->getGenreId()),
			'bestPublicationDate' => $bestPublicationDate,
		));

		// Fetch the viewable file template render.
		$templateMgr->assign('viewableFileContent', $templateMgr->fetch($this->getTemplatePath() . $templateFilename));

		// Show the front-end.
		$templateMgr->display('frontend/pages/viewFile.tpl');
	}

	/**
	 * Determine whether this plugin can handle the specified content.
	 * @param $publishedSubmission PublishedSubmission
	 * @param $publicationFormat PublicationFormat
	 * @param $submissionFile SubmissionFile
	 * @return boolean True iff the plugin can handle the content
	 */
	function canHandle($publishedSubmission, $publicationFormat, $submissionFile) {
		return false;
	}

	/**
	 * Callback that renders the galley.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function callback($hookName, $args) {
		$publishedSubmission =& $args[1];
		$publicationFormat =& $args[2];
		$submissionFile =& $args[3];

		if ($this->canHandle($publishedSubmission, $publicationFormat, $submissionFile)) {
			$this->displaySubmissionFile($publishedSubmission, $publicationFormat, $submissionFile);
			return true;
		}

		return false;
	}
}

?>
