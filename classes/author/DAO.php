<?php
/**
 * @file classes/author/DAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DAO
 *
 * @brief Operations for retrieving and modifying Author objects.
 */

namespace APP\author;

use APP\monograph\Chapter;
use Illuminate\Support\Facades\DB;

class DAO extends \PKP\author\DAO
{
    public $chapterAuthorsTable = 'submission_chapter_authors';

    /**
     * Add an author to a chapter.
     */
    public function insertChapterAuthor(int $authorId, int $chapterId, bool $isPrimary = false, int $sequence = 0)
    {
        DB::table($this->chapterAuthorsTable)->insert([
            'author_id' => $authorId,
            'chapter_id' => $chapterId,
            'primary_contact' => $isPrimary,
            'seq' => $sequence,
        ]);
    }

    /**
     * Remove an author from a chapter
     */
    public function deleteChapterAuthorById(int $authorId, int $chapterId)
    {
        DB::table($this->chapterAuthorsTable)
            ->where('author_id', '=', $authorId)
            ->where('chapter_id', '=', $chapterId)
            ->delete();
    }

    /**
     * Remove all authors from a chapter
     *
     * TODO: May need to go (or taken into consideration) to \chapter\DAO when available
     */
    public function deleteChapterAuthors(Chapter $chapter)
    {
        DB::table($this->chapterAuthorsTable)
            ->where('chapter_id', '=', $chapter->getId())
            ->delete();
    }
}
