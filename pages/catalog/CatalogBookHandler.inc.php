<?php

/**
 * @file pages/catalog/CatalogBookHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogBookHandler
 * @ingroup pages_catalog
 *
 * @brief Handle requests for the book-specific part of the public-facing
 *   catalog.
 */

import('classes.handler.Handler');

// import UI base classes
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.core.JSONMessage');

class CatalogBookHandler extends Handler {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}


	//
	// Overridden functions from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpPublishedSubmissionAccessPolicy');
		$this->addPolicy(new OmpPublishedSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Display a published submission in the public catalog.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function book($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$publishedSubmission = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH);
		$this->setupTemplate($request, $publishedSubmission);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION); // submission.synopsis; submission.copyrightStatement

		$templateMgr->assign('publishedSubmission', $publishedSubmission); /** @var $publishedSubmission PublishedSubmission */

		// Provide the publication formats to the template
		$publicationFormats = $publishedSubmission->getPublicationFormats(true);
		$availablePublicationFormats = array();
		$availableRemotePublicationFormats = array();
		foreach ($publicationFormats as $format) {
			if ($format->getIsAvailable()) {
				$availablePublicationFormats[] = $format;
				if ($format->getRemoteURL()) {
					$availableRemotePublicationFormats[] = $format;
				}
			}
		}
		$templateMgr->assign(array(
			'publicationFormats' => $availablePublicationFormats,
			'remotePublicationFormats' => $availableRemotePublicationFormats,
		));

		// Assign chapters (if they exist)
		$chapters = $publishedSubmission->getChapters();
		$templateMgr->assign('chapters', $chapters->toAssociativeArray());

		$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
		$templateMgr->assign(array(
			'pubIdPlugins' => PluginRegistry::loadCategory('pubIds', true),
			'licenseUrl' => $publishedSubmission->getLicenseURL(),
			'ccLicenseBadge' => Application::getCCLicenseBadge($publishedSubmission->getLicenseURL())
		));
		
		// Keywords
		$submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');
		$templateMgr->assign('keywords', $submissionKeywordDao->getKeywords($publishedMonograph->getId(), array(AppLocale::getLocale())));
		
		// Citations
		$citationDao = DAORegistry::getDAO('CitationDAO');
		$parsedCitations = $citationDao->getBySubmissionId($publishedSubmission->getId());
		$templateMgr->assign('parsedCitations', $parsedCitations);

		// Retrieve editors for an edited volume
		$authors = $publishedSubmission->getAuthors(true);
		$editors = array();
		if ($publishedSubmission->getWorkType() == WORK_TYPE_EDITED_VOLUME) {
			foreach ($authors as $author) {
				if ($author->getIsVolumeEditor()) {
					$editors[] = $author;
				}
			}
		}
		$templateMgr->assign(array(
			'authors' => $authors,
			'editors' => $editors,
		));

		// Consider public identifiers
		$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
		$templateMgr->assign('pubIdPlugins', $pubIdPlugins);

		// e-Commerce
		$press = $request->getPress();
		$paymentManager = Application::getPaymentManager($press);

		$availableFiles = $publishedSubmission->getAvailableFiles();

		// Only pass files in pub formats that are also available
		$filteredAvailableFiles = array();
		foreach ($availableFiles as $file) {
			foreach ($availablePublicationFormats as $format) {
				if ($file->getAssocId() == $format->getId()) {
					$filteredAvailableFiles[] = $file;
					break;
				}
			}
		}
		$templateMgr->assign('availableFiles', $filteredAvailableFiles);

		// Provide the currency to the template, if configured.
		$currencyDao = DAORegistry::getDAO('CurrencyDAO');
		if ($currency = $press->getSetting('currency')) {
			$templateMgr->assign('currency', $currencyDao->getCurrencyByAlphaCode($currency));
		}

		// Display
		if (!HookRegistry::call('CatalogBookHandler::book', array(&$request, &$publishedSubmission))) {
			return $templateMgr->display('frontend/pages/book.tpl');
		}
	}

	/**
	 * Use an inline viewer to view a published submission publication
	 * format file.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function view($args, $request) {
		$this->download($args, $request, true);
	}

	/**
	 * Download a published submission publication format file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @param $view boolean True iff inline viewer should be used, if available
	 */
	function download($args, $request, $view = false) {
		$dispatcher = $request->getDispatcher();
		$publishedSubmission = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH);
		$this->setupTemplate($request, $publishedSubmission); /** @var $publishedSubmission PublishedSubmission */
		$press = $request->getPress();

		$monographId = array_shift($args); // Validated thru auth
		$representationId = array_shift($args);
		$bestFileId = array_shift($args);

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var $publicationFormatDao PublicationFormatDAO */
		$publicationFormat = $publicationFormatDao->getByBestId($representationId, $publishedSubmission->getId(), $publishedSubmission->getSubmissionVersion());
		if (!$publicationFormat || !$publicationFormat->getIsAvailable() || $remoteURL = $publicationFormat->getRemoteURL()) fatalError('Invalid publication format specified.');

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /** @var $submissionFileDao SubmissionFileDAO */
		import('lib.pkp.classes.submission.SubmissionFile'); // File constants
		$submissionFile = $submissionFileDao->getByBestId($bestFileId, $publishedSubmission->getId(), $publishedSubmission->getSubmissionVersion());
		if (!$submissionFile) $dispatcher->handle404();

		$fileIdAndRevision = $submissionFile->getFileIdAndRevision();
		list($fileId, $revision) = array_map(function($a) {
			return (int) $a;
		}, preg_split('/-/', $fileIdAndRevision));
		import('lib.pkp.classes.file.SubmissionFileManager');
		$monographFileManager = new SubmissionFileManager($publishedSubmission->getContextId(), $publishedSubmission->getId());

		switch ($submissionFile->getAssocType()) {
			case ASSOC_TYPE_PUBLICATION_FORMAT: // Publication format file
				if ($submissionFile->getAssocId() != $publicationFormat->getId() || $submissionFile->getDirectSalesPrice() === null) fatalError('Invalid monograph file specified!');
				break;
			case ASSOC_TYPE_SUBMISSION_FILE: // Dependent file
				$genreDao = DAORegistry::getDAO('GenreDAO');
				$genre = $genreDao->getById($submissionFile->getGenreId());
				if (!$genre->getDependent()) fatalError('Invalid monograph file specified!');
				return $monographFileManager->downloadById($fileId, $revision);
				break;
			default: fatalError('Invalid monograph file specified!');
		}

		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign(array(
			'publishedSubmission' => $publishedSubmission,
			'publicationFormat' => $publicationFormat,
			'submissionFile' => $submissionFile,
			'chapter' => $chapterDao->getChapter($submissionFile->getData('chapterId')),
			'downloadUrl' => $dispatcher->url($request, ROUTE_PAGE, null, null, 'download', array($publishedSubmission->getBestId(), $publicationFormat->getBestId(), $submissionFile->getBestId()), array('inline' => true)),
		));

		$ompCompletedPaymentDao = DAORegistry::getDAO('OMPCompletedPaymentDAO');
		$user = $request->getUser();
		if ($submissionFile->getDirectSalesPrice() === '0' || ($user && $ompCompletedPaymentDao->hasPaidPurchaseFile($user->getId(), $fileIdAndRevision))) {
			// Paid purchase or open access.
			if (!$user && $press->getSetting('restrictMonographAccess')) {
				// User needs to register first.
				Validation::redirectLogin();
			}

			if ($view) {
				if (HookRegistry::call('CatalogBookHandler::view', array(&$this, &$publishedSubmission, &$publicationFormat, &$submissionFile))) {
					// If the plugin handled the hook, prevent further default activity.
					exit();
				}
			}

			// Inline viewer not available, or viewing not wanted.
			// Download or show the file.
			$inline = $request->getUserVar('inline')?true:false;
			if (HookRegistry::call('CatalogBookHandler::download', array(&$this, &$publishedSubmission, &$publicationFormat, &$submissionFile, &$inline))) {
				// If the plugin handled the hook, prevent further default activity.
				exit();
			}
			return $monographFileManager->downloadById($fileId, $revision, $inline);
		}

		// Fall-through: user needs to pay for purchase.

		// Users that are not logged in need to register/login first.
		if (!$user) return $request->redirect(null, 'login', null, null, array('source' => $request->url(null, null, null, array($monographId, $representationId, $bestFileId))));

		// They're logged in but need to pay to view.
		import('classes.payment.omp.OMPPaymentManager');
		$paymentManager = new OMPPaymentManager($press);
		if (!$paymentManager->isConfigured()) {
			$request->redirect(null, 'catalog');
		}

		$queuedPayment = $paymentManager->createQueuedPayment(
			$request,
			PAYMENT_TYPE_PURCHASE_FILE,
			$user->getId(),
			$fileIdAndRevision,
			$submissionFile->getDirectSalesPrice(),
			$press->getSetting('currency')
		);
		$paymentManager->queuePayment($queuedPayment);

		$paymentForm = $paymentManager->getPaymentForm($queuedPayment);
		$paymentForm->display($request);
	}

	/**
	 * Set up common template variables.
	 * @param $request PKPRequest
	 * @param $publishedSubmission PublishedSubmission
	 */
	function setupTemplate($request, $publishedSubmission) {
		$templateMgr = TemplateManager::getmanager($request);
		if ($seriesId = $publishedSubmission->getSeriesId()) {
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$series = $seriesDao->getById($seriesId, $publishedSubmission->getContextId());
			$templateMgr->assign('series', $series);
		}

		parent::setupTemplate($request);
	}
}


