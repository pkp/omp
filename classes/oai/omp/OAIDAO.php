<?php

/**
 * @file classes/oai/omp/OAIDAO.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OAIDAO
 *
 * @ingroup oai_omp
 *
 * @see OAI
 *
 * @brief DAO operations for the OMP OAI interface.
 */

namespace APP\oai\omp;

use APP\core\Application;
use APP\facades\Repo;
use APP\press\Press;
use APP\press\PressDAO;
use APP\publicationFormat\PublicationFormatDAO;
use APP\section\Section;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use PKP\db\DAO;
use PKP\db\DAORegistry;
use PKP\oai\OAISet;
use PKP\oai\PKPOAIDAO;
use PKP\plugins\Hook;
use PKP\submission\PKPSubmission;
use PKP\tombstone\DataObjectTombstoneDAO;

class OAIDAO extends PKPOAIDAO
{
    public DAO|PublicationFormatDAO $publicationFormatDao;
    public DAO|PressDAO $pressDao;
    public array $pressCache;
    public array $seriesCache;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
        $this->pressDao = DAORegistry::getDAO('PressDAO');
    }

    /**
     * Cached function to get a press
     */
    public function getPress(int $pressId): Press
    {
        return $this->pressCache[$pressId] ??= $this->pressDao->getById($pressId);
    }

    /**
     * Cached function to get a press series
     */
    public function getSeries(?int $seriesId): ?Section
    {
        if (!isset($this->seriesCache[$seriesId])) {
            $this->seriesCache[$seriesId] = $seriesId ? Repo::section()->get($seriesId) : null;
        }
        return $this->seriesCache[$seriesId];
    }

    //
    // Sets
    //

    /**
     * Return hierarchy of OAI sets (presses plus press series).
     *
     * @hook OAIDAO::getSets [[&$this, $pressId, $offset, $limit, $total, &$sets]]
     */
    public function getSets(?int $pressId, int $offset, int $limit, int &$total): array
    {
        if (isset($pressId)) {
            $presses = [$this->getPress($pressId)];
        } else {
            $pressFactory = $this->pressDao->getAll();
            $presses = $pressFactory->toArray();
        }

        // FIXME Set descriptions
        $sets = [];
        foreach ($presses as $press) {
            $title = $press->getLocalizedName();

            /** @var DataObjectTombstoneDAO $dataObjectTombstoneDao */
            $dataObjectTombstoneDao = DAORegistry::getDAO('DataObjectTombstoneDAO');
            $publicationFormatSets = $dataObjectTombstoneDao->getSets(Application::ASSOC_TYPE_PRESS, $press->getId());

            if (!array_key_exists(self::setSpec($press), $publicationFormatSets)) {
                $sets[] = new OAISet(self::setSpec($press), $title, '');
            }

            $seriesFactory = Repo::section()
                ->getCollector()
                ->filterByContextIds([$press->getId()])
                ->getMany();
            foreach ($seriesFactory as $series) {
                $setSpec = self::setSpec($press, $series);
                if (array_key_exists($setSpec, $publicationFormatSets)) {
                    unset($publicationFormatSets[$setSpec]);
                }
                $sets[] = new OAISet($setSpec, $series->getLocalizedTitle(), '');
            }
            foreach ($publicationFormatSets as $publicationFormatSetSpec => $publicationFormatSetName) {
                $sets[] = new OAISet($publicationFormatSetSpec, $publicationFormatSetName, '');
            }
        }

        Hook::call('OAIDAO::getSets', [&$this, $pressId, $offset, $limit, $total, &$sets]);

        $total = count($sets);
        return array_slice($sets, $offset, $limit);
    }

    /**
     * Return the press ID and series ID corresponding to a press/series pairing.
     *
     * @return int[] (int, int)
     */
    public function getSetPressSeriesId(string $pressSpec, ?string $seriesSpec, ?int $restrictPressId = null): array
    {
        $press = $this->pressDao->getByPath($pressSpec);
        if (!isset($press) || (isset($restrictPressId) && $press->getId() != $restrictPressId)) {
            return [0, 0];
        }

        $pressId = $press->getId();
        $seriesId = null;

        if (isset($seriesSpec)) {
            $series = Repo::section()->getByPath($seriesSpec, $press->getId());
            $seriesId = !is_null($series) ? $series->getId() : 0;
        }

        return [$pressId, $seriesId];
    }

    public static function setSpec($press, $series = null): string
    {
        // path is restricted to ascii alphanumeric, '-' and '_' so it only contains valid setSpec chars
        return isset($series)
            ? $press->getPath() . ':' . $series->getPath()
            : $press->getPath();
    }


    //
    // Protected methods.
    //
    /**
     * @see lib/pkp/classes/oai/PKPOAIDAO::setOAIData()
     */
    public function setOAIData($record, $row, $isRecord = true)
    {
        $press = $this->getPress($row['press_id']);
        $series = $this->getSeries($row['series_id']);
        $publicationFormatId = $row['data_object_id'];

        $oai = $this->oai;
        $record->identifier = $oai->publicationFormatIdToIdentifier($publicationFormatId);
        $record->sets = [self::setSpec($press, $series)];

        if ($isRecord) {
            $publicationFormat = $this->publicationFormatDao->getById($publicationFormatId);
            $publication = Repo::publication()->get($publicationFormat->getData('publicationId'));
            $submission = Repo::submission()->get($publication->getData('submissionId'));
            $record->setData('publicationFormat', $publicationFormat);
            $record->setData('monograph', $submission);
            $record->setData('press', $press);
            $record->setData('series', $series);
        }

        return $record;
    }

    /**
     * @copydoc PKPOAIDAO::getRecordsRecordSetQuery
     *
     * The $publicationId parameter is unused here; it is kept for signature
     * compatibility with the base class per-version OAI record support.
     */
    public function getRecordsRecordSetQuery(
        array $setIds,
        int|string|null $from,
        int|string|null $until,
        ?string $set,
        ?int $submissionId = null,
        string $orderBy = 'press_id, data_object_id',
        ?int $publicationId = null
    ): Builder {
        $pressId = array_shift($setIds);
        $seriesId = array_shift($setIds);

        return DB::table('publication_formats AS pf')
            ->select([
                'ms.last_modified AS last_modified',
                'pf.publication_format_id AS data_object_id',
                DB::raw('NULL AS tombstone_id'),
                DB::raw('NULL AS set_spec'),
                DB::raw('NULL AS oai_identifier'),
                'p.press_id AS press_id',
                'pub.series_id AS series_id',
            ])
            ->join('publications AS pub', 'pub.publication_id', '=', 'pf.publication_id')
            ->join('submissions AS ms', 'ms.current_publication_id', '=', 'pub.publication_id')
            ->leftJoin('series AS s', 's.series_id', '=', 'pub.series_id')
            ->join('presses AS p', 'p.press_id', '=', 'ms.context_id')
            ->where('p.enabled', '=', 1)
            ->when($pressId, function ($query, $pressId) {
                return $query->where('p.press_id', '=', $pressId);
            })
            ->when($seriesId, function ($query, $seriesId) {
                return $query->where('pub.series_id', '=', $seriesId);
            })
            ->where('ms.status', '=', PKPSubmission::STATUS_PUBLISHED)
            ->where('pf.is_available', '=', 1)
            ->whereNotNull('pub.date_published')
            ->when($from, function ($query, $from) {
                return $query->whereDate('ms.last_modified', '>=', \DateTime::createFromFormat('U', $from));
            })
            ->when($until, function ($query, $until) {
                return $query->whereDate('ms.last_modified', '<=', \DateTime::createFromFormat('U', $until));
            })
            ->when($submissionId, function ($query, $submissionId) {
                return $query->where('pf.publication_format_id', '=', $submissionId);
            })
            ->union(
                DB::table('data_object_tombstones AS dot')
                    ->select([
                        'dot.date_deleted AS last_modified',
                        'dot.data_object_id AS data_object_id',
                        'dot.tombstone_id',
                        'dot.set_spec',
                        'dot.oai_identifier',
                    ])
                    ->when(isset($pressId), function ($query, $pressId) {
                        return $query->join('data_object_tombstone_oai_set_objects AS tsop', function ($join) use ($pressId) {
                            $join->on('tsop.tombstone_id', '=', 'dot.tombstone_id');
                            $join->where('tsop.assoc_type', '=', Application::ASSOC_TYPE_PRESS);
                            $join->where('tsop.assoc_id', '=', (int) $pressId);
                        })->addSelect(['tsop.assoc_id AS press_id']);
                    }, function ($query) {
                        return $query->addSelect([DB::raw('NULL AS press_id')]);
                    })
                    ->when(isset($seriesId), function ($query, $seriesId) {
                        return $query->join('data_object_tombstone_oai_set_objects AS tsos', function ($join) use ($seriesId) {
                            $join->on('tsos.tombstone_id', '=', 'dot.tombstone_id');
                            $join->where('tsos.assoc_type', '=', Application::ASSOC_TYPE_SERIES);
                            $join->where('tsos.assoc_id', '=', (int) $seriesId);
                        })->addSelect(['tsos.assoc_id AS series_id']);
                    }, function ($query) {
                        return $query->addSelect([DB::raw('NULL AS series_id')]);
                    })
                    ->when(isset($set), function ($query) use ($set) {
                        return $query->where('dot.set_spec', '=', $set);
                    })
                    ->when($from, function ($query, $from) {
                        return $query->whereDate('dot.date_deleted', '>=', \DateTime::createFromFormat('U', $from));
                    })
                    ->when($until, function ($query, $until) {
                        return $query->whereDate('dot.date_deleted', '<=', \DateTime::createFromFormat('U', $until));
                    })
                    ->when($submissionId, function ($query, $submissionId) {
                        return $query->where('dot.data_object_id', '=', (int) $submissionId);
                    })
            )
            ->orderBy(DB::raw($orderBy));
    }
}
