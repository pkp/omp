<?php
/**
 * @file classes/author/Collector.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Collector
 *
 * @brief Extends the author collector to support OMP.
 */

namespace APP\author;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

class Collector extends \PKP\author\Collector
{
    public ?int $chapterId = null;

    public function __construct(DAO $dao)
    {
        $this->dao = $dao;
    }

    /**
     * Limit results to authors assigned to this chapter by chapterId
     */
    public function filterByChapterId(?int $chapterId): self
    {
        $this->chapterId = $chapterId;
        return $this;
    }

    /**
     * @copydoc CollectorInterface::getQueryBuilder()
     */
    public function getQueryBuilder(): Builder
    {
        $q = parent::getQueryBuilder();

        $q->when($this->chapterId !== null, function (Builder $query) {
            $query->join('submission_chapter_authors as sca', function (JoinClause $join) {
                $join->on('a.author_id', '=', 'sca.author_id')
                    ->where('sca.chapter_id', '=', $this->chapterId);
            });
            // Use the order specified by the submission_chapter_authors table,
            // to ensure that the order of authors reflects the order from the manually sorted chapters grid
            $query->orders = null;
            $query->orderBy('sca.seq');
        });
        return $q;
    }
}
