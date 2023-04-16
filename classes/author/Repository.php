<?php
/**
 * @file classes/author/Repository.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Repository
 *
 * @brief A repository to find and manage authors.
 */

namespace APP\author;

use APP\monograph\Chapter;

class Repository extends \PKP\author\Repository
{
    /** @copydoc DAO::getCollector() */
    public function getCollector(): Collector
    {
        return app(Collector::class);
    }

    /**
     * Add an author to a chapter.
     */
    public function addToChapter(int $authorId, int $chapterId, bool $isPrimary = false, int $sequence = 0)
    {
        $this->dao->insertChapterAuthor(
            $authorId,
            $chapterId,
            $isPrimary,
            $sequence
        );
    }

    /**
     * Remove an author from a chapter.
     */
    public function removeFromChapter(int $authorId, int $chapterId)
    {
        $this->dao->deleteChapterAuthorById(
            $authorId,
            $chapterId
        );
    }

    /**
     * Remove all authors from a chapter.
     */
    public function removeChapterAuthors(Chapter $chapter)
    {
        $this->dao->deleteChapterAuthors(
            $chapter
        );
    }
}
