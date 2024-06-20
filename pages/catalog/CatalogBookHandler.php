<?php

/**
 * @file pages/catalog/CatalogBookHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CatalogBookHandler
 *
 * @ingroup pages_catalog
 *
 * @brief Handle requests for the book-specific part of the public-facing
 *   catalog.
 */

namespace APP\pages\catalog;

use APP\core\Application;
use APP\core\Request;
use APP\core\Services;
use APP\facades\Repo;
use APP\handler\Handler;
use APP\monograph\Chapter;
use APP\monograph\ChapterDAO;
use APP\observers\events\UsageEvent;
use APP\payment\omp\OMPCompletedPaymentDAO;
use APP\payment\omp\OMPPaymentManager;
use APP\publication\Publication;
use APP\security\authorization\OmpPublishedSubmissionAccessPolicy;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\citation\CitationDAO;
use PKP\core\PKPApplication;
use PKP\core\PKPRequest;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\orcid\OrcidManager;
use PKP\plugins\Hook;
use PKP\plugins\PluginRegistry;
use PKP\security\authorization\ContextRequiredPolicy;
use PKP\security\Validation;
use PKP\submission\Genre;
use PKP\submission\GenreDAO;
use PKP\submission\PKPSubmission;
use PKP\submissionFile\SubmissionFile;

class CatalogBookHandler extends Handler
{
    /** @var Publication The requested publication */
    public $publication;

    /** @var null|Chapter The requested chapter */
    public $chapter = null;

    /** @var array this array contains ids of all publications, those contain the requested chapter */
    public $chapterPublicationIds = [];

    /** @var bool Is this a request for a specific version */
    public $isVersionRequest = false;

    /** @var bool Is this a request for a chapter */
    public $isChapterRequest = false;

    //
    // Overridden functions from PKPHandler
    //
    /**
     * @see PKPHandler::authorize()
     *
     * @param Request $request
     * @param array $args
     * @param array $roleAssignments
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $this->addPolicy(new ContextRequiredPolicy($request));
        $this->addPolicy(new OmpPublishedSubmissionAccessPolicy($request, $args, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments);
    }


    //
    // Public handler methods
    //
    /**
     * Display a published submission in the public catalog.
     *
     * @param array $args
     * @param Request $request
     *
     * @hook CatalogBookHandler::book [[&$request, &$submission, &$this->publication, &$this->chapter]]
     */
    public function book($args, $request)
    {
        $templateMgr = TemplateManager::getManager($request);
        $submission = $this->getAuthorizedContextObject(PKPApplication::ASSOC_TYPE_SUBMISSION);
        $user = $request->getUser();
        $this->setupTemplate($request, $submission);

        // Serve 404 if no submission available OR submission is unpublished and no user is logged in OR submission is unpublished and we have a user logged in but the user does not have access to preview
        if (!$submission || ($submission->getData('status') !== PKPSubmission::STATUS_PUBLISHED && !$user) || ($submission->getData('status') !== PKPSubmission::STATUS_PUBLISHED && $user && !Repo::submission()->canPreview($user, $submission))) {
            $request->getDispatcher()->handle404();
        }

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

        if (!$this->publication || ($this->publication->getData('status') !== PKPSubmission::STATUS_PUBLISHED && !Repo::submission()->canPreview($user, $submission))) {
            $request->getDispatcher()->handle404();
        }

        // If the publication has been reached through an outdated
        // urlPath, redirect to the latest version
        if (!ctype_digit((string) $submissionId) && $submissionId !== $this->publication->getData('urlPath') && !$subPath) {
            $newArgs = $this->publication->getData('urlPath')
                ? $this->publication->getData('urlPath')
                : $this->publication->getId();
            $request->redirect(null, $request->getRequestedPage(), $request->getRequestedOp(), $newArgs);
        }

        // If a chapter is requested, set this chapter
        if ($subPath === 'chapter') {
            $chapterId = empty($args) ? 0 : (int) array_shift($args);
            $this->setChapter($chapterId, $request);
        } elseif (!empty($args) && $args[0] === 'chapter') {
            $chapterId = isset($args[1]) ? (int) $args[1] : 0;
            $this->setChapter($chapterId, $request);
        }

        if ($this->isChapterRequest) {
            if (!$this->chapter->isPageEnabled()) {
                $request->getDispatcher()->handle404();
            }
            $chapterAuthors = $this->chapter->getAuthors();
            $chapterAuthors = $chapterAuthors->toArray();

            $datePublished = $submission->getEnableChapterPublicationDates() && $this->chapter->getDatePublished()
                ? $this->chapter->getDatePublished()
                : $this->publication->getData('datePublished');

            // Get the earliest published Version of the chapter
            $sourceChapter = $this->getSourceChapter($submission);
            if ($sourceChapter) {
                // Get the earliest publishing date of the chapter
                $firstDatePublished = $this->getChaptersFirstPublishedDate($submission, $sourceChapter);
            } else {
                $firstDatePublished = $datePublished;
            }

            $templateMgr->assign([
                'chapter' => $this->chapter,
                'chapterAuthors' => $chapterAuthors,
                'sourceChapter' => $sourceChapter,
                'firstDatePublished' => $firstDatePublished ?: $datePublished,
                'datePublished' => $datePublished,
                'chapterPublicationIds' => $this->chapterPublicationIds,
            ]);
        }

        // Get the earliest published publication
        $firstPublication = $submission->getData('publications')->reduce(function ($a, $b) {
            return empty($a) || strtotime((string) $b->getData('datePublished')) < strtotime((string) $a->getData('datePublished')) ? $b : $a;
        }, 0);

        $userGroups = Repo::userGroup()->getCollector()
            ->filterByContextIds([$submission->getData('contextId')])
            ->getMany();

        $templateMgr->assign([
            'isChapterRequest' => $this->isChapterRequest,
            'publishedSubmission' => $submission,
            'publication' => $this->publication,
            'firstPublication' => $firstPublication,
            'currentPublication' => $submission->getCurrentPublication(),
            'authorString' => $this->publication->getAuthorString($userGroups),
        ]);

        // Provide the publication formats to the template
        $availablePublicationFormats = [];
        $availableRemotePublicationFormats = [];
        foreach ($this->publication->getData('publicationFormats') as $format) {
            if ($format->getIsAvailable()) {
                $availablePublicationFormats[] = $format;
                if ($format->getData('urlRemote')) {
                    $availableRemotePublicationFormats[] = $format;
                }
            }
        }
        $templateMgr->assign([
            'publicationFormats' => $availablePublicationFormats,
            'remotePublicationFormats' => $availableRemotePublicationFormats,
        ]);

        // Assign chapters (if they exist)
        /** @var ChapterDAO */
        $chapterDao = DAORegistry::getDAO('ChapterDAO');
        $templateMgr->assign('chapters', $chapterDao->getByPublicationId($this->publication->getId())->toAssociativeArray());

        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        if ($this->isChapterRequest && $this->chapter->getData('licenseUrl')) {
            $ccLicenseBadge = Application::get()->getCCLicenseBadge($this->chapter->getData('licenseUrl'));
        } else {
            $ccLicenseBadge = Application::get()->getCCLicenseBadge($this->publication->getData('licenseUrl'));
        }
        $templateMgr->assign([
            'pubIdPlugins' => PluginRegistry::loadCategory('pubIds', true),
            'ccLicenseBadge' => $ccLicenseBadge,
        ]);

        // Categories
        $templateMgr->assign([
            'categories' => Repo::category()->getCollector()
                ->filterByPublicationIds([$this->publication->getId()])
                ->getMany()
                ->toArray()
        ]);

        // Citations
        if ($this->publication->getData('citationsRaw')) {
            /** @var CitationDAO */
            $citationDao = DAORegistry::getDAO('CitationDAO');
            $parsedCitations = $citationDao->getByPublicationId($this->publication->getId());
            $templateMgr->assign([
                'citations' => $parsedCitations->toArray(),
                'parsedCitations' => $parsedCitations, // compatible with older themes
            ]);
        }

        // Retrieve editors for an edited volume
        $editors = [];
        if ($submission->getData('workType') == $submission::WORK_TYPE_EDITED_VOLUME) {
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

        $pubFormatFiles = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$submission->getId()])
            ->filterByAssoc(Application::ASSOC_TYPE_PUBLICATION_FORMAT)
            ->getMany();

        $availableFiles = [];
        foreach ($pubFormatFiles as $pubFormatFile) {
            if ($pubFormatFile->getDirectSalesPrice() !== null) {
                $availableFiles[] = $pubFormatFile;
            }
        }

        // Only pass files in pub formats that are also available
        $filteredAvailableFiles = [];
        /** @var SubmissionFile $submissionFile */
        foreach ($availableFiles as $submissionFile) {
            foreach ($availablePublicationFormats as $format) {
                if ($submissionFile->getData('assocId') == $format->getId()) {
                    $filteredAvailableFiles[] = $submissionFile;
                    break;
                }
            }
        }
        $templateMgr->assign('availableFiles', $filteredAvailableFiles);

        // Provide the currency to the template, if configured.
        if ($currencyCode = $request->getContext()->getData('currency')) {
            $templateMgr->assign('currency', Locale::getCurrencies()->getByLetterCode($currencyCode));
        }

        // Add data for backwards compatibility
        $templateMgr->assign([
            'keywords' => $this->publication->getLocalizedData('keywords'),
            'licenseUrl' => $this->publication->getData('licenseUrl'),
        ]);

        // Add Orcid icon
        $templateMgr->assign([
            'orcidIcon' => OrcidManager::getIcon(),
        ]);

        // Ask robots not to index outdated versions and point to the canonical url for the latest version
        if ($this->publication->getId() != $submission->getData('currentPublicationId')) {
            $templateMgr->addHeader('noindex', '<meta name="robots" content="noindex">');
            $url = $request->getDispatcher()->url($request, PKPApplication::ROUTE_PAGE, null, 'catalog', 'book', $submission->getBestId());
            $templateMgr->addHeader('canonical', '<link rel="canonical" href="' . $url . '">');
        }

        // Display
        if (!Hook::call('CatalogBookHandler::book', [&$request, &$submission, &$this->publication, &$this->chapter])) {
            $templateMgr->display('frontend/pages/book.tpl');
            if ($this->isChapterRequest) {
                event(new UsageEvent(Application::ASSOC_TYPE_CHAPTER, $request->getContext(), $submission, null, null, $this->chapter));
            } else {
                event(new UsageEvent(Application::ASSOC_TYPE_SUBMISSION, $request->getContext(), $submission));
            }
            return;
        }
    }

    /**
     * Use an inline viewer to view a published submission publication
     * format file.
     *
     * @param array $args
     * @param Request $request
     */
    public function view($args, $request)
    {
        $this->download($args, $request, true);
    }

    /**
     * Download a published submission publication format file.
     *
     * @param array $args
     * @param Request $request
     * @param bool $view True iff inline viewer should be used, if available
     *
     * @hook CatalogBookHandler::view [[&$this, &$submission, &$publicationFormat, &$submissionFile]]
     * @hook CatalogBookHandler::download [[&$this, &$submission, &$publicationFormat, &$submissionFile, &$inline]]
     * @hook FileManager::downloadFileFinished [[&$returner]]
     */
    public function download($args, $request, $view = false)
    {
        $dispatcher = $request->getDispatcher();
        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);
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

        $publicationFormat = Application::get()->getRepresentationDAO()->getByBestId($representationId, $publicationId);
        if (!$publicationFormat || !$publicationFormat->getIsAvailable() || $publicationFormat->getData('urlRemote')) {
            $dispatcher->handle404();
        }

        $publication = null;
        foreach ($submission->getData('publications') as $iPublication) {
            if ($iPublication->getId() == $publicationId) {
                $publication = $iPublication;
                break;
            }
        }

        if (empty($publication)
                || $publication->getData('status') !== PKPSubmission::STATUS_PUBLISHED
                || $publicationFormat->getData('publicationId') !== $publication->getId()) {
            $dispatcher->handle404();
        }

        $submissionFile = Repo::submissionFile()
            ->dao
            ->getByBestId(
                $bestFileId,
                $submission->getId()
            );
        if (!$submissionFile) {
            $dispatcher->handle404();
        }

        $path = $submissionFile->getData('path');
        $filename = Services::get('file')->formatFilename($path, $submissionFile->getLocalizedData('name'));
        switch ($submissionFile->getData('assocType')) {
            case Application::ASSOC_TYPE_PUBLICATION_FORMAT: // Publication format file
                if ($submissionFile->getData('assocId') != $publicationFormat->getId() || $submissionFile->getDirectSalesPrice() === null) {
                    $dispatcher->handle404();
                }
                break;
            case Application::ASSOC_TYPE_SUBMISSION_FILE: // Dependent file
                $genreDao = DAORegistry::getDAO('GenreDAO'); /** @var GenreDAO $genreDao */
                $genre = $genreDao->getById($submissionFile->getGenreId());
                if (!$genre->getDependent()) {
                    $dispatcher->handle404();
                }
                return Services::get('file')->download($submissionFile->getData('fileId'), $filename);
            default: $dispatcher->handle404();
        }

        $urlPath = [$submission->getBestId()];
        if ($publicationId !== $submission->getCurrentPublication()->getId()) {
            $urlPath[] = 'version';
            $urlPath[] = $publicationId;
        }
        $urlPath[] = $publicationFormat->getBestId();
        $urlPath[] = $submissionFile->getBestId();

        $chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var ChapterDAO $chapterDao */
        $chapter = $chapterDao->getChapter($submissionFile->getData('chapterId'));
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'publishedSubmission' => $submission,
            'publicationFormat' => $publicationFormat,
            'submissionFile' => $submissionFile,
            'chapter' => $chapter,
            'downloadUrl' => $dispatcher->url($request, PKPApplication::ROUTE_PAGE, null, null, 'download', $urlPath, ['inline' => true]),
        ]);

        $ompCompletedPaymentDao = DAORegistry::getDAO('OMPCompletedPaymentDAO'); /** @var OMPCompletedPaymentDAO $ompCompletedPaymentDao */
        $user = $request->getUser();
        if ($submissionFile->getDirectSalesPrice() === '0' || ($user && $ompCompletedPaymentDao->hasPaidPurchaseFile($user->getId(), $submissionFile->getId()))) {
            // Paid purchase or open access.
            if (!$user && $press->getData('restrictMonographAccess')) {
                // User needs to register first.
                Validation::redirectLogin();
            }

            if ($view) {
                if (Hook::call('CatalogBookHandler::view', [&$this, &$submission, &$publicationFormat, &$submissionFile])) {
                    // If the plugin handled the hook, prevent further default activity.
                    exit;
                }
            }

            // Inline viewer not available, or viewing not wanted.
            // Download or show the file.
            $inline = $request->getUserVar('inline') ? true : false;
            if (Hook::call('CatalogBookHandler::download', [&$this, &$submission, &$publicationFormat, &$submissionFile, &$inline])) {
                // If the plugin handled the hook, prevent further default activity.
                exit;
            }

            // if the file is a publication format file (i.e. not a dependent file e.g. CSS or images), fire an usage event.
            if ($submissionFile->getData('assocId') == $publicationFormat->getId()) {
                $assocType = Application::ASSOC_TYPE_SUBMISSION_FILE;
                /** @var GenreDAO */
                $genreDao = DAORegistry::getDAO('GenreDAO');
                $genre = $genreDao->getById($submissionFile->getData('genreId'));
                // TO-DO: is this correct ?
                if ($genre->getCategory() != Genre::GENRE_CATEGORY_DOCUMENT || $genre->getSupplementary() || $genre->getDependent()) {
                    $assocType = Application::ASSOC_TYPE_SUBMISSION_FILE_COUNTER_OTHER;
                }
                event(new UsageEvent($assocType, $request->getContext(), $submission, $publicationFormat, $submissionFile, $chapter));
            }
            $returner = true;
            Hook::call('FileManager::downloadFileFinished', [&$returner]);
            return Services::get('file')->download($submissionFile->getData('fileId'), $filename, $inline);
        }

        // Fall-through: user needs to pay for purchase.

        // Users that are not logged in need to register/login first.
        if (!$user) {
            return $request->redirect(null, 'login', null, null, ['source' => $request->url(null, null, null, [$monographId, $representationId, $bestFileId])]);
        }

        // They're logged in but need to pay to view.
        $paymentManager = new OMPPaymentManager($press);
        if (!$paymentManager->isConfigured()) {
            $request->redirect(null, 'catalog');
        }

        $queuedPayment = $paymentManager->createQueuedPayment(
            $request,
            OMPPaymentManager::PAYMENT_TYPE_PURCHASE_FILE,
            $user->getId(),
            $submissionFile->getId(),
            $submissionFile->getDirectSalesPrice(),
            $press->getData('currency')
        );
        $paymentManager->queuePayment($queuedPayment);

        $paymentForm = $paymentManager->getPaymentForm($queuedPayment);
        $paymentForm->display($request);
    }

    /**
     * Set up common template variables.
     *
     * @param Request $request
     * @param Submission $submission
     */
    public function setupTemplate($request, $submission = null)
    {
        $templateMgr = TemplateManager::getManager($request);
        if ($seriesId = $submission->getCurrentPublication()->getData('seriesId')) {
            $series = Repo::section()->get($seriesId, $submission->getData('contextId'));
            $templateMgr->assign('series', $series);
        }
        parent::setupTemplate($request);
    }

    /**
     * Set the requested chapter.
     *
     */
    protected function setChapter(int $chapterId, PKPRequest $request): void
    {
        if ($chapterId > 0) {
            $this->isChapterRequest = true;
            /** @var ChapterDAO */
            $chapterDao = DAORegistry::getDAO('ChapterDAO');
            $chapters = $chapterDao->getBySourceChapterId($chapterId);
            $chapters = $chapters->toAssociativeArray();
            $chaptersCount = count($chapters);
            if ($chaptersCount > 0) {
                /** @var Chapter $chapter */
                foreach ($chapters as $chapter) {
                    $publicationId = (int) $chapter->getData('publicationId');
                    if ($publicationId === $this->publication->getId()) {
                        $this->chapter = $chapter;
                        $this->setChapterPublicationIds();
                        break;
                    }
                }
            }

            if (null === $this->chapter) {
                $request->getDispatcher()->handle404();
            }
        }
    }

    /**
     * Set an array with all publication ids of the requested chapter.
     */
    protected function setChapterPublicationIds(): void
    {
        if ($this->chapter && $this->isChapterRequest) {
            /** @var ChapterDAO */
            $chapterDao = DAORegistry::getDAO('ChapterDAO');
            $chapters = $chapterDao->getBySourceChapterId($this->chapter->getSourceChapterId());
            $chapters = $chapters->toAssociativeArray();
            $publicationIds = [];
            /** @var Chapter $chapter */
            foreach ($chapters as $chapter) {
                if ($chapter->isPageEnabled()) {
                    $publicationId = (int) $chapter->getData('publicationId');
                    $publicationIds[] = $publicationId;
                }
            }
            $this->chapterPublicationIds = $publicationIds;
        }
    }

    /**
     * Get the earliest version of a chapter
     *
     */
    protected function getSourceChapter(Submission $submission): ?Chapter
    {
        /** @var ChapterDAO */
        $chapterDao = DAORegistry::getDAO('ChapterDAO');
        $chapters = $chapterDao->getBySourceChapterId($this->chapter->getSourceChapterId());
        $chapters = $chapters->toAssociativeArray();
        $publications = Repo::publication()->getCollector()->filterBySubmissionIds([$submission->getId()])->getMany();

        /** @var Chapter $chapter */
        foreach ($chapters as $chapter) {
            /** @var Publication $publication */
            foreach ($publications as $publication) {
                if ($publication->getId() === (int) $chapter->getData('publicationId')) {
                    return $chapter;
                }
            }
        }

        return null;
    }

    /**
     * Get the earliest publishing date of the chapter
     *
     *
     */
    protected function getChaptersFirstPublishedDate(Submission $submission, Chapter $sourceChapter): ?string
    {
        $publishedPublications = $submission->getPublishedPublications();
        $firstPublication = null;
        $sourceChapterPublicationId = (int) $sourceChapter->getData('publicationId');

        /** @var Publication $publication */
        foreach ($publishedPublications as $publication) {
            if ($publication->getId() === $sourceChapterPublicationId) {
                $firstPublication = $publication;
                break;
            }
        }

        if ($firstPublication) {
            if ($submission->getEnableChapterPublicationDates() && $sourceChapter->getDatePublished()) {
                return $sourceChapter->getDatePublished();
            }

            return $firstPublication->getData('datePublished');
        }

        return null;
    }
}
