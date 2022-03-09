<?php

/**
 * @file classes/observers/events/Usage.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Usage
 * @ingroup observers_events
 *
 * @brief Usage event.
 *
 */

namespace APP\observers\events;

use APP\core\Application;
use APP\monograph\Chapter;
use APP\press\Series;
use APP\submission\Submission;
use PKP\context\Context;
use PKP\observers\traits\UsageEvent;
use PKP\submission\Representation;
use PKP\submissionFile\SubmissionFile;

class Usage
{
    use UsageEvent;

    /** Chapter ID */
    public ?Chapter $chapter;

    public function __construct(int $assocType, Context $context, Submission $submission = null, Representation $publicationFormat = null, SubmissionFile $submissionFile = null, Chapter $chapter = null, Series $series = null)
    {
        $this->constructUsageEvent($assocType, $context, $submission, $publicationFormat, $submissionFile);

        if (in_array($assocType, [Application::ASSOC_TYPE_CHAPTER, Application::ASSOC_TYPE_SERIES, Application::ASSOC_TYPE_PRESS])) {
            $canonicalUrlPage = $canonicalUrlOp = null;
            $canonicalUrlParams = [];
            switch ($assocType) {
                case Application::ASSOC_TYPE_CHAPTER:
                    $canonicalUrlOp = 'book';
                    $canonicalUrlParams = [$submission->getId()];
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
                            $canonicalUrlParams[] = $chapter->getId();
                        }
                    }
                    break;
                case Application::ASSOC_TYPE_SERIES:
                    $router = $this->request->getRouter(); /** @var PageRouter $router */
                    $args = $router->getRequestedArgs($this->request);
                    $canonicalUrlOp = 'series';
                    $canonicalUrlParams = [$args[0]];
                    break;
                case Application::ASSOC_TYPE_PRESS:
                    $router = $this->request->getRouter(); /** @var PageRouter $router */
                    $page = $router->getRequestedPage($this->request);
                    if ($page == 'catalog') {
                        $canonicalUrlPage = 'catalog';
                        $canonicalUrlOp = 'index';
                    }
                    break;
            }
            $canonicalUrl = $this->getCanonicalUrl($this->request, $canonicalUrlPage, $canonicalUrlOp, $canonicalUrlParams);
            $this->canonicalUrl = $canonicalUrl;
        }
        $this->chapter = $chapter;
        $this->series = $series;
    }
}
