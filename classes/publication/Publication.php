<?php

/**
 * @file classes/publication/Publication.php
 *
 * Copyright (c) 2016-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Publication
 *
 * @ingroup publication
 *
 * @see DAO
 *
 * @brief Class for Publication.
 */

namespace APP\publication;

use APP\core\Application;
use APP\facades\Repo;
use APP\file\PublicFileManager;
use PKP\publication\PKPPublication;

class Publication extends PKPPublication
{
    /**
     * Get a string indicating all editors of an edited volume
     *
     * @return string
     */
    public function getEditorString()
    {
        $authors = $this->getData('authors');
        $editorNames = [];
        foreach ($authors as $author) {
            if ($author->getIsVolumeEditor()) {
                $editorNames[] = __('submission.editorName', ['editorName' => $author->getFullName()]);
            }
        }

        // Spaces are stripped from the locale strings, so we have to add the
        // space in here.
        return join(__('common.commaListSeparator') . ' ', $editorNames);
    }

    /**
     * Get the URL to a localized cover image
     *
     * @param string $preferredLocale Return the cover image in a specified locale.
     *
     * @return string
     */
    public function getLocalizedCoverImageUrl(int $contextId, $preferredLocale = null)
    {
        $coverImage = $this->getLocalizedData('coverImage', $preferredLocale);

        if (!$coverImage) {
            return Application::get()->getRequest()->getBaseUrl() . '/templates/images/book-default.png';
        }

        $publicFileManager = new PublicFileManager();

        return join('/', [
            Application::get()->getRequest()->getBaseUrl(),
            $publicFileManager->getContextFilesPath($contextId),
            $coverImage['uploadName'],
        ]);
    }

    /**
     * Get the URL to the thumbnail of a localized cover image
     *
     * @return string
     */
    public function getLocalizedCoverImageThumbnailUrl(int $contextId)
    {
        $url = $this->getLocalizedCoverImageUrl($contextId);
        $pathParts = pathinfo($url);
        return join('/', [
            $pathParts['dirname'],
            Repo::publication()->getThumbnailFilename($pathParts['basename']),
        ]);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\publication\Publication', '\Publication');
}
