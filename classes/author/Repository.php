<?php

/**
 * @file classes/author/Repository.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2000-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Repository
 *
 * @brief A repository to find and manage authors.
 */

namespace APP\author;

use APP\monograph\Chapter;
use Illuminate\Support\Collection;

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
    public function addToChapter(int $authorId, int $chapterId, bool $isPrimary = false, int $sequence = 0): void
    {
        $this->dao->insertChapterAuthor(
            $authorId,
            $chapterId,
            $isPrimary,
            $sequence
        );
    }

    /**
     * Get the sequence of each author assigned to a chapter.
     *
     * @return Collection<int,float> Sequences keyed by author ID
     */
    public function getChapterAuthorSequences(int $chapterId): Collection
    {
        return $this->dao->getChapterAuthorSequences($chapterId);
    }

    /**
     * Remove an author from a chapter.
     */
    public function removeFromChapter(int $authorId, int $chapterId): void
    {
        $this->dao->deleteChapterAuthorById(
            $authorId,
            $chapterId
        );
    }

    /**
     * Remove all authors from a chapter.
     */
    public function removeChapterAuthors(Chapter $chapter): void
    {
        $this->dao->deleteChapterAuthors(
            $chapter
        );
    }
}
