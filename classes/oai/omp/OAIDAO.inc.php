<?php

/**
 * @file classes/oai/omp/OAIDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OAIDAO
 * @ingroup oai_omp
 *
 * @see OAI
 *
 * @brief DAO operations for the OMP OAI interface.
 */

namespace APP\oai\omp;

use APP\facades\Repo;
use Illuminate\Support\Facades\DB;
use PKP\db\DAORegistry;
use PKP\oai\OAISet;
use PKP\oai\PKPOAIDAO;
use PKP\plugins\HookRegistry;

use PKP\submission\PKPSubmission;

class OAIDAO extends PKPOAIDAO
{
    /** @var PublicationFormatDAO */
    public $_publicationFormatDao;

    /** @var SeriesDAO */
    public $_seriesDao;

    /** @var PressDAO */
    public $_pressDao;

    /** @var array */
    public $_pressCache;

    /** @var array */
    public $_seriesCache;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
        $this->_seriesDao = DAORegistry::getDAO('SeriesDAO');
        $this->_pressDao = DAORegistry::getDAO('PressDAO');
    }

    /**
     * Cached function to get a press
     *
     * @param $pressId int
     *
     * @return Press
     */
    public function getPress($pressId)
    {
        if (!isset($this->_pressCache[$pressId])) {
            $this->_pressCache[$pressId] = $this->_pressDao->getById($pressId);
        }
        return $this->_pressCache[$pressId];
    }

    /**
     * Cached function to get a press series
     *
     * @param $seriesId int
     *
     * @return Series
     */
    public function getSeries($seriesId)
    {
        if (!isset($this->_seriesCache[$seriesId])) {
            $this->_seriesCache[$seriesId] = $this->_seriesDao->getById($seriesId);
        }
        return $this->_seriesCache[$seriesId];
    }

    //
    // Sets
    //

    /**
     * Return hierarchy of OAI sets (presses plus press series).
     *
     * @param $pressId int
     * @param $offset int
     * @param $total int
     *
     * @return array OAISet
     */
    public function getSets($pressId = null, $offset, $limit, &$total)
    {
        if (isset($pressId)) {
            $presses = [$this->getPress($pressId)];
        } else {
            $pressFactory = $this->_pressDao->getAll();
            $presses = $pressFactory->toArray();
        }

        // FIXME Set descriptions
        $sets = [];
        foreach ($presses as $press) {
            $title = $press->getLocalizedName();

            $dataObjectTombstoneDao = DAORegistry::getDAO('DataObjectTombstoneDAO'); /* @var $dataObjectTombstoneDao DataObjectTombstoneDAO */
            $publicationFormatSets = $dataObjectTombstoneDao->getSets(ASSOC_TYPE_PRESS, $press->getId());

            if (!array_key_exists(self::setSpec($press), $publicationFormatSets)) {
                array_push($sets, new OAISet(self::setSpec($press), $title, ''));
            }

            $seriesFactory = $this->_seriesDao->getByPressId($press->getId());
            foreach ($seriesFactory->toArray() as $series) {
                $setSpec = self::setSpec($press, $series);
                if (array_key_exists($setSpec, $publicationFormatSets)) {
                    unset($publicationFormatSets[$setSpec]);
                }
                array_push($sets, new OAISet($setSpec, $series->getLocalizedTitle(), ''));
            }
            foreach ($publicationFormatSets as $publicationFormatSetSpec => $publicationFormatSetName) {
                array_push($sets, new OAISet($publicationFormatSetSpec, $publicationFormatSetName, ''));
            }
        }

        HookRegistry::call('OAIDAO::getSets', [&$this, $pressId, $offset, $limit, $total, &$sets]);

        $total = count($sets);
        $sets = array_slice($sets, $offset, $limit);

        return $sets;
    }

    /**
     * Return the press ID and series ID corresponding to a press/series pairing.
     *
     * @param $pressSpec string
     * @param $seriesSpec string
     * @param $restrictPressId int
     *
     * @return array (int, int, int)
     */
    public function getSetPressSeriesId($pressSpec, $seriesSpec, $restrictPressId = null)
    {
        $press = $this->_pressDao->getByPath($pressSpec);
        if (!isset($press) || (isset($restrictPressId) && $press->getId() != $restrictPressId)) {
            return [0, 0];
        }

        $pressId = $press->getId();
        $seriesId = null;

        if (isset($seriesSpec)) {
            $series = $this->_seriesDao->getByPath($seriesSpec, $press->getId());
            if ($series && is_a($series, 'Series')) {
                $seriesId = $series->getId();
            } else {
                $seriesId = 0;
            }
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

        $record->identifier = $this->oai->publicationFormatIdToIdentifier($publicationFormatId);
        $record->sets = [self::setSpec($press, $series)];

        if ($isRecord) {
            $publicationFormat = $this->_publicationFormatDao->getById($publicationFormatId);
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
     * @copydoc PKPOAIDAO::_getRecordsRecordSet
     *
     * @param null|mixed $submissionId
     */
    public function _getRecordsRecordSetQuery($setIds, $from, $until, $set, $submissionId = null, $orderBy = 'press_id, data_object_id')
    {
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
                return $query->where('ms.last_modified', '>=', $this->datetimeToDB($from));
            })
            ->when($until, function ($query, $until) {
                return $query->where('ms.last_modified', '<=', $this->datetimeToDB($until));
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
                        $join->where('tsop.assoc_type', '=', ASSOC_TYPE_PRESS);
                        $join->where('tsop.assoc_id', '=', (int) $pressId);
                    })->addSelect(['tsop.assoc_id AS press_id']);
                }, function ($query) {
                    return $query->addSelect([DB::raw('NULL AS press_id')]);
                })
                    ->when(isset($seriesId), function ($query, $seriesId) {
                    return $query->join('data_object_tombstone_oai_set_objects AS tsos', function ($join) use ($seriesId) {
                        $join->on('tsos.tombstone_id', '=', 'dot.tombstone_id');
                        $join->where('tsos.assoc_type', '=', ASSOC_TYPE_SERIES);
                        $join->where('tsos.assoc_id', '=', (int) $seriesId);
                    })->addSelect(['tsos.assoc_id AS series_id']);
                }, function ($query) {
                    return $query->addSelect([DB::raw('NULL AS series_id')]);
                })
                    ->when(isset($set), function ($query) use ($set) {
                    return $query->where('dot.set_spec', '=', $set);
                })
                    ->when($from, function ($query, $from) {
                    return $query->where('dot.date_deleted', '>=', $from);
                })
                    ->when($until, function ($query, $until) {
                    return $query->where('dot.date_deleted', '<=', $until);
                })
                    ->when($submissionId, function ($query, $submissionId) {
                    return $query->where('dot.data_object_id', '=', (int) $submissionId);
                })
            )
            ->orderBy(DB::raw($orderBy));
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\oai\omp\OAIDAO', '\OAIDAO');
}
