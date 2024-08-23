<?php
/**
 * @file classes/submission/Collector.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Collector
 *
 * @brief A helper class to configure a Query Builder to get a collection of submissions
 */

namespace APP\submission;

use APP\core\Application;
use APP\facades\Repo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Collector extends \PKP\submission\Collector
{
    public const ORDERBY_SERIES_POSITION = 'seriesPosition';

    /** @var array */
    public $columns;

    /** @var array|null */
    public $seriesIds = null;

    /** @var bool */
    protected $orderByFeatured = false;

    public function __construct(DAO $dao)
    {
        $this->dao = $dao;
    }

    /**
     * Limit results to submissions assigned to these series
     */
    public function filterBySeriesIds(array $seriesIds): self
    {
        $this->seriesIds = $seriesIds;
        return $this;
    }

    /**
     * Put featured items first in the results
     *
     * If filtering by series or categories, this will put featured
     * items in that series or category first. By default, it puts
     * the items featured in the context at the top.
     */
    public function orderByFeatured(): self
    {
        $this->orderByFeatured = true;
        return $this;
    }

    /**
     * Add APP-specific filtering methods for submission sub objects DOI statuses
     *
     */
    protected function addDoiStatusFilterToQuery(Builder $q)
    {
        $q->whereIn('s.current_publication_id', function (Builder $q) {
            $q->select('current_p.publication_id')
                ->from('publications as current_p')
                ->leftJoin('submission_chapters as current_c', 'current_p.publication_id', '=', 'current_c.publication_id')
                ->leftJoin('publication_formats as current_pf', 'current_p.publication_id', '=', 'current_pf.publication_id')
                ->leftJoin('dois as pd', 'pd.doi_id', '=', 'current_p.doi_id')
                ->leftJoin('dois as cd', 'cd.doi_id', '=', 'current_c.doi_id')
                ->leftJoin('dois as pfd', 'pfd.doi_id', '=', 'current_pf.doi_id')
                ->whereIn('pd.status', $this->doiStatuses)
                ->orWhereIn('cd.status', $this->doiStatuses)
                ->orWhereIn('pfd.status', $this->doiStatuses);
        });
    }


    /**
     * Add APP-specific filtering methods for checking if submission sub objects have DOIs assigned
     */
    protected function addHasDoisFilterToQuery(Builder $q)
    {
        $q->whereIn('s.current_publication_id', function (Builder $q) {
            $q->select('current_p.publication_id')
                ->from('publications', 'current_p')
                ->leftJoin('submission_chapters as current_c', 'current_p.publication_id', '=', 'current_c.publication_id')
                ->leftJoin('submission_chapter_settings as current_cs', 'current_cs.chapter_id', '=', 'current_c.chapter_id')
                ->leftJoin('publication_formats as current_pf', 'current_p.publication_id', '=', 'current_pf.publication_id')
                ->where(function (Builder $q) {
                    $q->when($this->hasDois === true, function (Builder $q) {
                        $q->when(in_array(Repo::doi()::TYPE_PUBLICATION, $this->enabledDoiTypes), function (Builder $q) {
                            $q->whereNotNull('current_p.doi_id');
                        });
                        $q->when(in_array(Repo::doi()::TYPE_CHAPTER, $this->enabledDoiTypes), function (Builder $q) {
                            $q->orWhereNotNull('current_c.doi_id');
                        });
                        $q->when(in_array(Repo::doi()::TYPE_REPRESENTATION, $this->enabledDoiTypes), function (Builder $q) {
                            $q->orWhereNotNull('current_pf.doi_id');
                        });
                    });
                    $q->when($this->hasDois === false, function (Builder $q) {
                        $q->when(in_array(Repo::doi()::TYPE_PUBLICATION, $this->enabledDoiTypes), function (Builder $q) {
                            $q->whereNull('current_p.doi_id');
                        });
                        $q->when(in_array(Repo::doi()::TYPE_CHAPTER, $this->enabledDoiTypes), function (Builder $q) {
                            $q->orWhere(function (Builder $q) {
                                $q->whereNull('current_c.doi_id');
                                $q->whereNotNull('current_c.chapter_id');
                                $q->where(function (Builder $q) {
                                    $q->where('current_cs.setting_name', '=', 'isPageEnabled');
                                    $q->where('current_cs.setting_value', '=', 1);
                                });
                            });
                        });
                        $q->when(in_array(Repo::doi()::TYPE_REPRESENTATION, $this->enabledDoiTypes), function (Builder $q) {
                            $q->orWhere(function (Builder $q) {
                                $q->whereNull('current_pf.doi_id');
                                $q->whereNotNull('current_pf.publication_format_id');
                            });
                        });
                    });
                });
        });
    }

    /**
     * @copydoc CollectorInterface::getQueryBuilder()
     */
    public function getQueryBuilder(): Builder
    {
        $q = parent::getQueryBuilder();

        if (is_array($this->seriesIds)) {
            $q->leftJoin('publications as publication_s', 's.current_publication_id', '=', 'publication_s.publication_id');
            $q->whereIn('publication_s.series_id', $this->seriesIds);
        }

        // order by series position
        if ($this->orderBy === self::ORDERBY_SERIES_POSITION) {
            $this->columns[] = 'po.series_position';
            $q->reorder()->orderBy('po.series_position', $this->orderDirection);
        }

        if (!empty($this->orderByFeatured)) {
            if (is_array($this->seriesIds)) {
                $assocType = Application::ASSOC_TYPE_SERIES;
                $assocIds = $this->seriesIds;
            } elseif (is_array($this->categoryIds)) {
                $assocType = Application::ASSOC_TYPE_CATEGORY;
                $assocIds = $this->categoryIds;
            } else {
                $assocType = Application::ASSOC_TYPE_PRESS;
                $assocIds = is_array($this->contextIds)
                    ? $this->contextIds
                    : [Application::SITE_CONTEXT_ID];
            }

            $q->leftJoin('features as sf', function ($join) use ($assocType, $assocIds) {
                $join->on('s.submission_id', '=', 'sf.submission_id')
                    ->where('sf.assoc_type', '=', $assocType)
                    ->whereIn('sf.assoc_id', $assocIds);
            });

            // Featured sorting should be the first sort parameter. We sort by
            // the seq parameter, with null values last

            $q->addSelect('sf.seq');
            $q->addSelect(DB::raw('case when sf.seq is null then 1 else 0 end'));
            array_unshift(
                $q->orders,
                ['type' => 'raw', 'sql' => 'case when sf.seq is null then 1 else 0 end'],
                ['column' => 'sf.seq', 'direction' => 'ASC']
            );
        }

        return $q;
    }

    protected function getReviewStages(): array
    {
        return [WORKFLOW_STAGE_ID_EXTERNAL_REVIEW, WORKFLOW_STAGE_ID_INTERNAL_REVIEW];
    }
}
