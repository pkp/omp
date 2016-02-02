<?php

/**
 * @file pages/catalog/CatalogBookHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
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
	function CatalogBookHandler() {
		parent::Handler();
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
		import('classes.security.authorization.OmpPublishedMonographAccessPolicy');
		$this->addPolicy(new OmpPublishedMonographAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Display a published monograph in the public catalog.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function book($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION); // submission.synopsis; submission.copyrightStatement

		$publishedMonograph = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH);
		$templateMgr->assign('publishedMonograph', $publishedMonograph);

		// Assign chapters (if they exist)
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$chapters = $chapterDao->getChapters($publishedMonograph->getId());
		$templateMgr->assign_by_ref('chapters', $chapters->toAssociativeArray());

		// Determine which pubId plugins are enabled.
		$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
		$enabledPubIdTypes = array();
		$metaCustomHeaders = '';

		foreach ((array) $pubIdPlugins as $plugin) {
			if ($plugin->getEnabled()) {
				$enabledPubIdTypes[] = $plugin->getPubIdType();
				// check to see if the format has a pubId set.  If not, generate one.
				$publicationFormats = $publishedMonograph->getPublicationFormats(true);
				foreach ($publicationFormats as $publicationFormat) {
					if ($publicationFormat->getStoredPubId($plugin->getPubIdType()) == '') {
						$plugin->getPubId($publicationFormat);
					}
					if ($plugin->getPubIdType() == 'doi') {
						$pubId = strip_tags($publicationFormat->getStoredPubId('doi'));
						$metaCustomHeaders .= '<meta name="DC.Identifier.DOI" content="' . $pubId . '"/><meta name="citation_doi" content="'. $pubId . '"/>';
					}
				}
			}
		}
		$templateMgr->assign('enabledPubIdTypes', $enabledPubIdTypes);
		$templateMgr->assign('metaCustomHeaders', $metaCustomHeaders);
		$templateMgr->assign('ccLicenseBadge', Application::getCCLicenseBadge($publishedMonograph->getLicenseURL()));

		// e-Commerce
		import('classes.payment.omp.OMPPaymentManager');
		$ompPaymentManager = new OMPPaymentManager($request);
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		if ($ompPaymentManager->isConfigured()) {
			$availableFiles = array_filter(
				$submissionFileDao->getLatestRevisions($publishedMonograph->getId()),
				create_function('$a', 'return $a->getViewable() && $a->getDirectSalesPrice() !== null && $a->getAssocType() == ASSOC_TYPE_PUBLICATION_FORMAT;')
			);
			$availableFilesByPublicationFormat = array();
			foreach ($availableFiles as $availableFile) {
				$availableFilesByPublicationFormat[$availableFile->getAssocId()][] = $availableFile;
			}

			// Expose variables to template
			$templateMgr->assign('availableFiles', $availableFilesByPublicationFormat);

		}

		// Provide the currency to the template, if configured.
		$currencyDao = DAORegistry::getDAO('CurrencyDAO');
		$press = $request->getPress();
		if ($currency = $press->getSetting('currency')) {
			$templateMgr->assign('currency', $currencyDao->getCurrencyByAlphaCode($currency));
		}

		if ($seriesId = $publishedMonograph->getSeriesId()) {
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$series = $seriesDao->getById($seriesId, $publishedMonograph->getContextId());
			$templateMgr->assign('series', $series);
		}

		// Display
		$templateMgr->display('frontend/pages/book.tpl');
	}

	/**
	 * Use an inline viewer to view a published monograph publication
	 * format file.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function view($args, $request) {
		$this->download($args, $request, true);
	}

	/**
	 * Download a published monograph publication format file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @param $view boolean True iff inline viewer should be used, if available
	 */
	function download($args, $request, $view = false) {
		$this->setupTemplate($request);
		$press = $request->getPress();

		$monographId = (int) array_shift($args); // Validated thru auth
		$representationId = (int) array_shift($args);
		$fileIdAndRevision = array_shift($args);

		$publishedMonograph = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH);
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($representationId, $publishedMonograph->getId());
		if (!$publicationFormat || !$publicationFormat->getIsApproved() || !$publicationFormat->getIsAvailable()) fatalError('Invalid publication format specified.');

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		list($fileId, $revision) = array_map(create_function('$a', 'return (int) $a;'), preg_split('/-/', $fileIdAndRevision));
		import('classes.monograph.MonographFile'); // File constants
		$submissionFile = $submissionFileDao->getRevision($fileId, $revision, SUBMISSION_FILE_PROOF, $monographId);
		if (!$submissionFile || $submissionFile->getAssocType() != ASSOC_TYPE_PUBLICATION_FORMAT || $submissionFile->getAssocId() != $representationId || $submissionFile->getDirectSalesPrice() === null) {
			fatalError('Invalid monograph file specified!');
		}

		$ompCompletedPaymentDao = DAORegistry::getDAO('OMPCompletedPaymentDAO');
		$user = $request->getUser();
		if ($submissionFile->getDirectSalesPrice() === '0' || ($user && $ompCompletedPaymentDao->hasPaidPurchaseFile($user->getId(), $fileIdAndRevision))) {
			// Paid purchase or open access.
			if (!$user && $press->getSetting('restrictMonographAccess')) {
				// User needs to register first.
				Validation::redirectLogin();
			}

			// If inline viewing is requested, permit plugins to
			// handle the document.
			PluginRegistry::loadCategory('viewableFiles', true);
			if ($view) {
				if (HookRegistry::call('CatalogBookHandler::view', array(&$this, &$publishedMonograph, &$publicationFormat, &$submissionFile))) {
					// If the plugin handled the hook, prevent further default activity.
					exit();
				}
			}

			// Inline viewer not available, or viewing not wanted.
			// Download the file.
			$inline = false;
			if (!HookRegistry::call('CatalogBookHandler::download', array(&$this, &$publishedMonograph, &$publicationFormat, &$submissionFile, &$inline))) {
				import('lib.pkp.classes.file.SubmissionFileManager');
				$monographFileManager = new SubmissionFileManager($publishedMonograph->getContextId(), $monographId);
				return $monographFileManager->downloadFile($fileId, $revision, $inline);
			}
		}

		// Fall-through: user needs to pay for purchase.

		// Users that are not logged in need to register/login first.
		if (!$user) return $request->redirect(null, 'login', null, null, array('source' => $request->url(null, null, null, array($monographId, $representationId, $fileIdAndRevision))));

		// They're logged in but need to pay to view.
		import('classes.payment.omp.OMPPaymentManager');
		$ompPaymentManager = new OMPPaymentManager($request);
		if (!$ompPaymentManager->isConfigured()) {
			$request->redirect(null, 'catalog');
		}

		$queuedPayment = $ompPaymentManager->createQueuedPayment(
			$press->getId(),
			PAYMENT_TYPE_PURCHASE_FILE,
			$user->getId(),
			$fileIdAndRevision,
			$submissionFile->getDirectSalesPrice(),
			$press->getSetting('currency')
		);

		$ompPaymentManager->displayPaymentForm(
			$ompPaymentManager->queuePayment($queuedPayment),
			$queuedPayment
		);
	}
}

?>
