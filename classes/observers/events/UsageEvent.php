<?php

/**
 * @file classes/observers/events/UsageEvent.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class UsageEvent
 *
 * @ingroup observers_events
 *
 * @brief Adds chapter and series tracking to the usage event data.
 *
 */

namespace APP\observers\events;

use APP\core\Application;
use APP\core\PageRouter;
use APP\monograph\Chapter;
use APP\section\Section;
use APP\submission\Submission;
use Exception;
use PKP\context\Context;
use PKP\submission\Representation;
use PKP\submissionFile\SubmissionFile;

class UsageEvent extends \PKP\observers\events\UsageEvent
{
    public ?Chapter $chapter;
    public ?Section $series;

    public function __construct(int $assocType, Context $context, ?Submission $submission = null, ?Representation $publicationFormat = null, ?SubmissionFile $submissionFile = null, ?Chapter $chapter = null, ?Section $series = null)
    {
        $this->chapter = $chapter;
        $this->series = $series;
        parent::__construct($assocType, $context, $submission, $publicationFormat, $submissionFile);
    }

    /**
     * Get the canonical URL for the usage object
     *
     * @throws Exception
     */
    protected function getCanonicalUrl(): string
    {
        if (in_array($this->assocType, [Application::ASSOC_TYPE_CHAPTER, Application::ASSOC_TYPE_SERIES, Application::ASSOC_TYPE_PRESS])) {
            $canonicalUrlPage = $canonicalUrlOp = null;
            $canonicalUrlParams = [];
            switch ($this->assocType) {
                case Application::ASSOC_TYPE_CHAPTER:
                    $canonicalUrlOp = 'book';
                    $canonicalUrlParams = [$this->submission->getId()];
                    $router = $this->request->getRouter(); /** @var PageRouter $router */
                    $op = $router->getRequestedOp($this->request);
                    $args = $router->getRequestedArgs($this->request);
                    if ($op == 'book' && count($args) > 1) {
                        $submissionId = array_shift($args);
                        $subPath = empty($args) ? 0 : array_shift($args);
                        if ($subPath === 'version') {
                            $publicationId = (int) array_shift($args);
                            $canonicalUrlParams[] = 'version';
                            $canonicalUrlParams[] = $publicationId;
                            $subPath = empty($args) ? 0 : array_shift($args);
                        }
                        if ($subPath === 'chapter') {
                            $canonicalUrlParams[] = 'chapter';
                            $canonicalUrlParams[] = $this->chapter->getId();
                        }
                    }
                    break;
                case Application::ASSOC_TYPE_SERIES:
                    $router = $this->request->getRouter(); /** @var PageRouter $router */
                    $args = $router->getRequestedArgs($this->request);
                    $canonicalUrlOp = 'series';
                    $canonicalUrlParams = [$args[0]]; // series path
                    break;
                case Application::ASSOC_TYPE_PRESS:
                    $router = $this->request->getRouter(); /** @var PageRouter $router */
                    $page = $router->getRequestedPage($this->request);
                    if ($page == 'catalog') {
                        $canonicalUrlPage = 'catalog';
                        $canonicalUrlOp = 'index';
                        break;
                    } else {
                        return parent::getCanonicalUrl();
                    }
            }
            $canonicalUrl = $this->getRouterCanonicalUrl($this->request, $canonicalUrlPage, $canonicalUrlOp, $canonicalUrlParams);
            return $canonicalUrl;
        } else {
            return parent::getCanonicalUrl();
        }
    }
}
