<?php

/**
 * @file pages/catalog/CatalogBookHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CatalogBookHandler
 * @ingroup pages_catalog
 *
 * @brief Handle requests for the book-specific part of the public-facing
 *   catalog.
 */

use APP\core\Application;
use APP\core\Services;
use APP\facades\Repo;
use APP\handler\Handler;
use APP\monograph\Chapter;
use APP\observers\events\Usage;
use APP\payment\omp\OMPPaymentManager;
use APP\security\authorization\OmpPublishedSubmissionAccessPolicy;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\core\PKPApplication;
use PKP\core\PKPRequest;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\plugins\HookRegistry;
use PKP\plugins\PluginRegistry;
use PKP\security\Validation;
use PKP\submission\Genre;
use PKP\submission\PKPSubmission;

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
     * @param PKPRequest $request
     * @param array $args
     * @param array $roleAssignments
     */
    public function authorize($request, &$args, $roleAssignments)
    {
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
     * @param PKPRequest $request
     */
    public function book($args, $request)
    {
        $templateMgr = TemplateManager::getManager($request);
        $submission = $this->getAuthorizedContextObject(PKPApplication::ASSOC_TYPE_SUBMISSION);
        $this->setupTemplate($request, $submission);

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

        if (!$this->publication || $this->publication->getData('status') !== PKPSubmission::STATUS_PUBLISHED) {
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

        $templateMgr->assign([
            'isChapterRequest' => $this->isChapterRequest,
            'publishedSubmission' => $submission,
            'publication' => $this->publication,
            'firstPublication' => $firstPublication,
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
        $templateMgr->assign([
            'publicationFormats' => $availablePublicationFormats,
            'remotePublicationFormats' => $availableRemotePublicationFormats,
        ]);

        // Assign chapters (if they exist)
        $templateMgr->assign('chapters', DAORegistry::getDAO('ChapterDAO')->getByPublicationId($this->publication->getId())->toAssociativeArray());

        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        $templateMgr->assign([
            'pubIdPlugins' => PluginRegistry::loadCategory('pubIds', true),
            'ccLicenseBadge' => Application::get()->getCCLicenseBadge($this->publication->getData('licenseUrl')),
        ]);

        // Categories
        $templateMgr->assign([
            'categories' => iterator_to_array(
                Repo::category()->getMany(Repo::category()->getCollector()
                    ->filterByPublicationIds([$this->publication->getId()]))
            ),
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
        if ($submission->getWorkType() == $submission::WORK_TYPE_EDITED_VOLUME) {
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

        $collector = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$submission->getId()])
            ->filterByAssoc(ASSOC_TYPE_PUBLICATION_FORMAT);

        $pubFormatFiles = Repo::submissionFile()->getMany($collector);

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

        // Ask robots not to index outdated versions and point to the canonical url for the latest version
        if ($this->publication->getId() !== $submission->getCurrentPublication()->getId()) {
            $templateMgr->addHeader('noindex', '<meta name="robots" content="noindex">');
            $url = $request->getDispatcher()->url($request, PKPApplication::ROUTE_PAGE, null, 'catalog', 'book', $submission->getBestId());
            $templateMgr->addHeader('canonical', '<link rel="canonical" href="' . $url . '">');
        }

        // Display
        if (!HookRegistry::call('CatalogBookHandler::book', [&$request, &$submission])) {
            $templateMgr->display('frontend/pages/book.tpl');
            if ($this->isChapterRequest) {
                event(new Usage(Application::ASSOC_TYPE_CHAPTER, $request->getContext(), $submission, null, null, $this->chapter));
            } else {
                event(new Usage(Application::ASSOC_TYPE_SUBMISSION, $request->getContext(), $submission));
            }
            return;
        }
    }

    /**
     * Use an inline viewer to view a published submission publication
     * format file.
     *
     * @param array $args
     * @param PKPRequest $request
     */
    public function view($args, $request)
    {
        $this->download($args, $request, true);
    }

    /**
     * Download a published submission publication format file.
     *
     * @param array $args
     * @param PKPRequest $request
     * @param bool $view True iff inline viewer should be used, if available
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
        if (!$publicationFormat || !$publicationFormat->getIsAvailable() || $remoteURL = $publicationFormat->getRemoteURL()) {
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
            case ASSOC_TYPE_PUBLICATION_FORMAT: // Publication format file
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
                if (HookRegistry::call('CatalogBookHandler::view', [&$this, &$submission, &$publicationFormat, &$submissionFile])) {
                    // If the plugin handled the hook, prevent further default activity.
                    exit;
                }
            }

            // Inline viewer not available, or viewing not wanted.
            // Download or show the file.
            $inline = $request->getUserVar('inline') ? true : false;
            if (HookRegistry::call('CatalogBookHandler::download', [&$this, &$submission, &$publicationFormat, &$submissionFile, &$inline])) {
                // If the plugin handled the hook, prevent further default activity.
                exit;
            }

            // if the file is a publication format file (i.e. not a dependent file e.g. CSS or images), fire an usage event.
            if ($submissionFile->getData('assocId') == $publicationFormat->getId()) {
                $assocType = Application::ASSOC_TYPE_SUBMISSION_FILE;
                $genreDao = DAORegistry::getDAO('GenreDAO');
                $genre = $genreDao->getById($submissionFile->getData('genreId'));
                // TO-DO: is this correct ?
                if ($genre->getCategory() != Genre::GENRE_CATEGORY_DOCUMENT || $genre->getSupplementary() || $genre->getDependent()) {
                    $assocType = Application::ASSOC_TYPE_SUBMISSION_FILE_COUNTER_OTHER;
                }
                event(new Usage($assocType, $request->getContext(), $submission, $publicationFormat, $submissionFile, $chapter));
            }
            $returner = true;
            HookRegistry::call('FileManager::downloadFileFinished', [&$returner]);
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
     * @param PKPRequest $request
     * @param Submission $submission
     */
    public function setupTemplate($request, $submission = null)
    {
        $templateMgr = TemplateManager::getmanager($request);
        if ($seriesId = $submission->getSeriesId()) {
            $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
            $series = $seriesDao->getById($seriesId, $submission->getData('contextId'));
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
            $chapterDao = DAORegistry::getDAO('ChapterDAO');
            $chapters = $chapterDao->getBySourceChapterId($chapterId);
            $chapters = $chapters->toAssociativeArray();
            $chaptersCount = count($chapters);
            if ($chaptersCount > 0) {
                /** @var Chapter $chapter */
                foreach ($chapters as $chapter) {
                    $publicationId = (int) $chapter->getData('publicationId');
                    if ($publicationId === $this->publication->getId()
                        && $this->publication->getData('status') === PKPSubmission::STATUS_PUBLISHED) {
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
        $chapterDao = DAORegistry::getDAO('ChapterDAO');
        $chapters = $chapterDao->getBySourceChapterId($this->chapter->getSourceChapterId());
        $chapters = $chapters->toAssociativeArray();
        $publishedPublications = $submission->getPublishedPublications();

        /** @var Chapter $chapter */
        foreach ($chapters as $chapter) {
            /** @var Publication $publication */
            foreach ($publishedPublications as $publication) {
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
