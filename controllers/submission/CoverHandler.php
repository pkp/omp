<?php

/**
 * @file controllers/submission/CoverHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CoverHandler
 * @ingroup controllers_submission
 *
 * @brief Component serving up cover images for submissions.
 */

namespace APP\controllers\submission;

use APP\core\Application;
use APP\security\authorization\OmpPublishedSubmissionAccessPolicy;

use PKP\handler\PKPHandler;

class CoverHandler extends PKPHandler
{
    /** @var Press $press */
    public $_press;

    /** @var int The monograph ID for this handler */
    public $monographId;

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

    /**
     * Set the monograph ID
     *
     * @param int $monographId
     */
    public function setMonographId($monographId)
    {
        $this->monographId = $monographId;
    }

    /**
     * Get the monograph ID
     *
     * @return int
     */
    public function getMonographId()
    {
        return $this->monographId;
    }

    /**
     * Set the current press
     *
     * @param Press $press
     */
    public function setPress($press)
    {
        $this->_press = $press;
    }

    /**
     * Get the current press
     *
     * @return Press
     */
    public function getPress()
    {
        return $this->_press;
    }

    /**
     * Serve the cover image for a published submission.
     */
    public function cover($args, $request)
    {
        // this function is only used on the book page i.e. for published submissiones
        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_MONOGRAPH);

        $coverImageUrl = $submission->getCurrentPublication()->getLocalizedCoverImageUrl($submission->getData('contextId'));
        if (!$coverImageUrl) {
            $coverImageUrl = $request->getBaseUrl() . '/templates/images/book-default.png';
        }

        // Can't use Request::redirectUrl; FireFox doesn't
        // seem to like it for images.
        header('Location: ' . $coverImageUrl);
        exit;
    }

    /**
     * Serve the cover thumbnail for a published submission.
     */
    public function thumbnail($args, $request)
    {
        // use Application::ASSOC_TYPE_MONOGRAPH to set the cover at any workflow stage
        // i.e. also if the monograph has not been published yet
        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_MONOGRAPH);

        $coverImageThumbnailUrl = $submission->getCurrentPublication()->getLocalizedCoverImageThumbnailUrl($submission->getData('contextId'));
        if (!$coverImageThumbnailUrl) {
            $coverImageThumbnailUrl = $request->getBaseUrl() . '/templates/images/book-default_t.png';
        }

        // Can't use Request::redirectUrl; FireFox doesn't
        // seem to like it for images.
        header('Location: ' . $coverImageThumbnailUrl);
        exit;
    }
}
