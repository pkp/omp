<?php
/**
 * @file classes/author/Collector.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class author
 *
 * @brief Extends the author collector to support OMP.
 */

namespace APP\author;

use Illuminate\Database\Query\Builder;

class Collector extends \PKP\author\Collector
{
    /** @var array|null */
    public $chapterIds = null;

    public function __construct(DAO $dao)
    {
        $this->dao = $dao;
    }

    /**
     * Limit results to authors assigned to this chapter by chapterId
     */
    public function filterByChapterIds(?array $chapterIds): self
    {
        $this->chapterIds = $chapterIds;
        return $this;
    }

    /**
     * @copydoc CollectorInterface::getQueryBuilder()
     */
    public function getQueryBuilder(): Builder
    {
        $q = parent::getQueryBuilder();

        $q->when($this->chapterIds !== null, function ($query) {
            $query->whereIn('author_id', function ($query) {
                return $query->select('author_id')
                    ->from('submission_chapter_authors')
                    ->whereIn('chapter_id', $this->chapterIds);
            });
        });

        return $q;
    }
}
