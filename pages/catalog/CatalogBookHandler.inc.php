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
	/** @var Publication The requested publication */
	public $publication;

	/** @var boolean Is this a request for a specific version */
	public $isVersionRequest = false;

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
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		$this->setupTemplate($request, $submission);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION); // submission.synopsis; submission.copyrightStatement

		// Get the requested publication or default to the current publication
		$submissionId = array_shift($args);
		$subPath = empty($args) ? 0 : array_shift($args);
		if ($subPath === 'version') {
			$this->isVersionRequest = true;
			$publicationId = (int) array_shift($args);
			foreach ($submission->getData('publications') as $publication) {
				if ($publication->getId() === $publicationId) {
					$this->publication = $publication;
				}
			}
		} else {
			$this->publication = $submission->getCurrentPublication();
		}

		if (!$this->publication) {
			$request->getDispatcher()->handle404();
		}

		$templateMgr->assign([
			'publishedSubmission' => $submission,
			'publication' => $this->publication,
			'firstPublication' => reset($submission->getData('publications')),
			'currentPublication' => $submission->getCurrentPublication(),
			'authorString' => $this->publication->getAuthorString(DAORegistry::getDAO('UserGroupDAO')->getByContextId($submission->getData('contextId'))->toArray()),
		]);

		// Provide the publication formats to the template
		$availablePublicationFormats = [];
		$availableRemotePublicationFormats = [];
		foreach ($this->publication->getData('publicationFormats') as $format) {
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
		$templateMgr->assign('chapters', DAORegistry::getDAO('ChapterDAO')->getByPublicationId($this->publication->getId())->toAssociativeArray());

		$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
		$templateMgr->assign(array(
			'pubIdPlugins' => PluginRegistry::loadCategory('pubIds', true),
			'ccLicenseBadge' => Application::getCCLicenseBadge($this->publication->getData('licenseUrl')),
		));

		// Categories
		$templateMgr->assign([
			'categories' => DAORegistry::getDAO('CategoryDAO')->getByPublicationId($this->publication->getId())->toArray(),
		]);

		// Citations
		if ($this->publication->getData('citationsRaw')) {
			$parsedCitations = DAORegistry::getDAO('CitationDAO')->getByPublicationId($this->publication->getId());
			$templateMgr->assign([
				'citations' => $parsedCitations->toArray(),
				'parsedCitations' => $parsedCitations, // compatible with older themes
			]);
		}

		// Retrieve editors for an edited volume
		$editors = [];
		if ($submission->getWorkType() == WORK_TYPE_EDITED_VOLUME) {
			foreach ($this->publication->getData('authors') as $author) {
				if ($author->getIsVolumeEditor()) {
					$editors[] = $author;
				}
			}
		}
		$templateMgr->assign([
			'editors' => $editors,
		]);

		// Consider public identifiers
		$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
		$templateMgr->assign('pubIdPlugins', $pubIdPlugins);

		$availableFiles = array_filter(
			DAORegistry::getDAO('SubmissionFileDAO')->getLatestRevisions($submission->getId(), null, null),
			function($a) {
				return $a->getDirectSalesPrice() !== null && $a->getAssocType() == ASSOC_TYPE_PUBLICATION_FORMAT;
			}
		);

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
		if ($currency = $request->getContext()->getSetting('currency')) {
			$templateMgr->assign('currency', $currencyDao->getCurrencyByAlphaCode($currency));
		}

		// Add data for backwards compatibility
		$templateMgr->assign([
			'keywords' => $this->publication->getLocalizedData('keywords'),
			'licenseUrl' => $this->publication->getData('licenseUrl'),
		]);

		// Ask robots not to index outdated versions and point to the canonical url for the latest version
		if ($this->publication->getId() !== $submission->getCurrentPublication()->getId()) {
			$templateMgr->addHeader('noindex', '<meta name="robots" content="noindex">');
			$url = $request->getDispatcher()->url($request, ROUTE_PAGE, null, 'catalog', 'book', $submission->getBestId());
			$templateMgr->addHeader('canonical', '<link rel="canonical" href="' . $url . '">');
		}

		// Display
		if (!HookRegistry::call('CatalogBookHandler::book', array(&$request, &$submission))) {
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
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		$this->setupTemplate($request, $submission);
		$press = $request->getPress();

		$monographId = array_shift($args); // Validated thru auth
		$subPath = array_shift($args);
		if ($subPath === 'version') {
			$publicationId = array_shift($args);
			$representationId = array_shift($args);
			$bestFileId = array_shift($args);
		} else {
			$publicationId = $submission->getCurrentPublication()->getId();
			$representationId = $subPath;
			$bestFileId = array_shift($args);
		}

		$publicationFormat = DAORegistry::getDAO('PublicationFormatDAO')->getByBestId($representationId, $publicationId);
		if (!$publicationFormat || !$publicationFormat->getIsAvailable() || $remoteURL = $publicationFormat->getRemoteURL()) fatalError('Invalid publication format specified.');

		import('lib.pkp.classes.submission.SubmissionFile'); // File constants
		$submissionFile = DAORegistry::getDAO('SubmissionFileDAO')->getByBestId($bestFileId, $submission->getId());
		if (!$submissionFile) $dispatcher->handle404();

		$fileIdAndRevision = $submissionFile->getFileIdAndRevision();
		list($fileId, $revision) = array_map(function($a) {
			return (int) $a;
		}, preg_split('/-/', $fileIdAndRevision));
		import('lib.pkp.classes.file.SubmissionFileManager');
		$monographFileManager = new SubmissionFileManager($submission->getData('contextId'), $submission->getId());

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

		$urlPath = [$submission->getBestId()];
		if ($publicationId !== $submission->getCurrentPublication()->getId()) {
			$urlPath[] = 'version';
			$urlPath[] = $publicationId;
		}
		$urlPath[] = $publicationFormat->getBestId();
		$urlPath[] = $submissionFile->getBestId();

		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign(array(
			'publishedSubmission' => $submission,
			'publicationFormat' => $publicationFormat,
			'submissionFile' => $submissionFile,
			'chapter' => $chapterDao->getChapter($submissionFile->getData('chapterId')),
			'downloadUrl' => $dispatcher->url($request, ROUTE_PAGE, null, null, 'download', $urlPath, array('inline' => true)),
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
				if (HookRegistry::call('CatalogBookHandler::view', array(&$this, &$submission, &$publicationFormat, &$submissionFile))) {
					// If the plugin handled the hook, prevent further default activity.
					exit();
				}
			}

			// Inline viewer not available, or viewing not wanted.
			// Download or show the file.
			$inline = $request->getUserVar('inline')?true:false;
			if (HookRegistry::call('CatalogBookHandler::download', array(&$this, &$submission, &$publicationFormat, &$submissionFile, &$inline))) {
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
	 * @param $submission Submission
	 */
	function setupTemplate($request, $submission) {
		$templateMgr = TemplateManager::getmanager($request);
		if ($seriesId = $submission->getSeriesId()) {
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$series = $seriesDao->getById($seriesId, $submission->getData('contextId'));
			$templateMgr->assign('series', $series);
		}

		parent::setupTemplate($request);
	}
}


