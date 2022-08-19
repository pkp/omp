<?php

/**
 * @file classes/press/SeriesDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SeriesDAO
 * @ingroup press
 *
 * @see Series
 *
 * @brief Operations for retrieving and modifying Series objects.
 */

namespace APP\press;

use APP\core\Application;
use APP\facades\Repo;
use PKP\context\PKPSectionDAO;
use PKP\db\DAORegistry;
use PKP\db\DAOResultFactory;
use PKP\plugins\Hook;
use Illuminate\Support\Facades\DB;

use PKP\submission\PKPSubmission;

class SeriesDAO extends PKPSectionDAO
{
    /**
     * Retrieve an series by ID.
     *
     * @param int $seriesId
     * @param int $pressId optional
     *
     * @return Series|null
     */
    public function getById($seriesId, $pressId = null)
    {
        $params = [(int) $seriesId];
        if ($pressId) {
            $params[] = (int) $pressId;
        }

        $result = $this->retrieve(
            'SELECT	*
			FROM	series
			WHERE	series_id = ?
			' . ($pressId ? ' AND press_id = ?' : ''),
            $params
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Retrieve a series by path.
     *
     * @param string $path
     * @param int $pressId
     *
     * @return Series|null
     */
    public function getByPath($path, $pressId)
    {
        $result = $this->retrieve(
            'SELECT * FROM series WHERE path = ? AND press_id = ?',
            [(string) $path, (int) $pressId]
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Construct a new data object corresponding to this DAO.
     *
     * @return Series
     */
    public function newDataObject()
    {
        return new Series();
    }

    /**
     * Internal function to return an Series object from a row.
     *
     * @param array $row
     *
     * @return Series
     */
    public function _fromRow($row)
    {
        $series = parent::_fromRow($row);

        $series->setId($row['series_id']);
        $series->setPressId($row['press_id']);
        $series->setFeatured($row['featured']);
        $series->setImage(unserialize($row['image']));
        $series->setPath($row['path']);
        $series->setIsInactive($row['is_inactive']);

        $this->getDataObjectSettings('series_settings', 'series_id', $row['series_id'], $series);

        Hook::call('SeriesDAO::_fromRow', [&$series, &$row]);

        return $series;
    }

    /**
     * Get the list of fields for which data can be localized.
     *
     * @return array
     */
    public function getLocaleFieldNames()
    {
        return array_merge(
            parent::getLocaleFieldNames(),
            ['description', 'prefix', 'subtitle']
        );
    }

    /**
     * Get a list of additional fields.
     *
     * @return array
     */
    public function getAdditionalFieldNames()
    {
        return array_merge(
            parent::getAdditionalFieldNames(),
            ['onlineIssn', 'printIssn', 'sortOption']
        );
    }

    /**
     * Update the localized fields for this table
     *
     * @param object $series
     */
    public function updateLocaleFields($series)
    {
        $this->updateDataObjectSettings(
            'series_settings',
            $series,
            ['series_id' => (int) $series->getId()]
        );
    }

    /**
     * Insert a new series.
     *
     * @param Series $series
     */
    public function insertObject($series)
    {
        $this->update(
            'INSERT INTO series
				(press_id, seq, featured, path, image, editor_restricted, is_inactive)
			VALUES
				(?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $series->getPressId(),
                (float) $series->getSequence(),
                (int) $series->getFeatured(),
                (string) $series->getPath(),
                serialize($series->getImage() ? $series->getImage() : []),
                (int) $series->getEditorRestricted(),
                (int) $series->getIsInactive() ? 1 : 0,
            ]
        );

        $series->setId($this->getInsertId());
        $this->updateLocaleFields($series);
        return $series->getId();
    }

    /**
     * Update an existing series.
     *
     * @param Series $series
     */
    public function updateObject($series)
    {
        $this->update(
            'UPDATE series
			SET	press_id = ?,
				seq = ?,
				featured = ?,
				path = ?,
				image = ?,
				editor_restricted = ?,
				is_inactive = ?
			WHERE	series_id = ?',
            [
                (int) $series->getPressId(),
                (float) $series->getSequence(),
                (int) $series->getFeatured(),
                (string) $series->getPath(),
                serialize($series->getImage() ? $series->getImage() : []),
                (int) $series->getEditorRestricted(),
                (int) $series->getIsInactive(),
                (int) $series->getId(),
            ]
        );
        $this->updateLocaleFields($series);
    }

    /**
     * Delete an series by ID.
     *
     * @param int $seriesId
     * @param int $contextId optional
     */
    public function deleteById($seriesId, $contextId = null)
    {
        // Validate the $contextId, if supplied.
        if (!$this->seriesExists($seriesId, $contextId)) {
            return false;
        }

        $subEditorsDao = DAORegistry::getDAO('SubEditorsDAO'); /** @var SubEditorsDAO $subEditorsDao */
        $subEditorsDao->deleteBySubmissionGroupId($seriesId, ASSOC_TYPE_SECTION, $contextId);

        // Remove monographs from this series
        $submissionIds = Repo::submission()
                ->getCollector()
                ->filterBySeriesIds([$seriesId])
                ->filterByContextIds([Application::CONTEXT_ID_ALL])
                ->getIds();

        $publications = Repo::publication()->getCollector()
                ->filterBySubmissionIds($submissionIds->toArray())
                ->getMany();

        foreach ($publications as $publication) {
            Repo::publication()->edit($publication, ['seriesId' => 0]);
        }

        // Delete the series and settings.
        $this->update('DELETE FROM series WHERE series_id = ?', [(int) $seriesId]);
        $this->update('DELETE FROM series_settings WHERE series_id = ?', [(int) $seriesId]);
    }

    /**
     * Delete series by press ID
     * NOTE: This does not delete dependent entries EXCEPT from series_editors. It is intended
     * to be called only when deleting a press.
     *
     * @param int $pressId
     */
    public function deleteByPressId($pressId)
    {
        $this->deleteByContextId($pressId);
    }

    /**
     * Retrieve all series for a press.
     *
     * @param int $pressId Press ID
     * @param DBResultRange $rangeInfo optional
     * @param bool $submittableOnly optional. Whether to return only series
     *  that can be submitted to by anyone.
     * @param bool $withPublicationsOnly optional
     *
     * @return DAOResultFactory containing Series ordered by sequence
     */
    public function getByPressId($pressId, $rangeInfo = null, $submittableOnly = false, $withPublicationsOnly = false)
    {
        return $this->getByContextId($pressId, $rangeInfo, $submittableOnly, $withPublicationsOnly);
    }

    /**
     * Retrieve all series for a press.
     *
     * @param int $pressId Press ID
     * @param DBResultRange $rangeInfo optional
     * @param bool $submittableOnly optional. Whether to return only series
     *  that can be submitted to by anyone.
     * @param bool $withPublicationsOnly optional
     *
     * @return DAOResultFactory containing Series ordered by sequence
     */
    public function getByContextId($pressId, $rangeInfo = null, $submittableOnly = false, $withPublicationsOnly = false)
    {
        $params = [(int) $pressId];
        if ($withPublicationsOnly) {
            $params[] = PKPSubmission::STATUS_PUBLISHED;
        }

        $result = $this->retrieveRange(
            'SELECT s.*
                FROM series AS s
                WHERE s.press_id = ?
                ' . ($submittableOnly ? ' AND s.editor_restricted = 0' : '') . '
                ' . ($withPublicationsOnly ? ' AND (SELECT COUNT(*) FROM publications AS p WHERE p.series_id = s.series_id AND p.status = ?) > 0' : '') . '
                ORDER BY s.seq',
            $params,
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_fromRow');
    }

    /**
     * Retrieve the IDs and titles of the series for a press in an associative array.
     *
     * @return array
     */
    public function getTitlesByPressId($pressId, $submittableOnly = false)
    {
        $seriesTitles = [];

        $seriesIterator = $this->getByPressId($pressId, null);
        while ($series = $seriesIterator->next()) {
            if ($submittableOnly) {
                if (!$series->getEditorRestricted()) {
                    $seriesTitles[$series->getId()] = $series->getLocalizedTitle();
                }
            } else {
                $seriesTitles[$series->getId()] = $series->getLocalizedTitle();
            }
        }

        return $seriesTitles;
    }

    /**
     * Check if an series exists with the specified ID.
     *
     * @param int $seriesId
     * @param int $pressId
     *
     * @return bool
     */
    public function seriesExists($seriesId, $pressId)
    {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS row_count FROM series WHERE series_id = ? AND press_id = ?',
            [(int) $seriesId, (int) $pressId]
        );
        $row = $result->current();
        return $row && $row->row_count;
    }

    /**
     * Get the ID of the last inserted series.
     *
     * @return int
     */
    public function getInsertId()
    {
        return $this->_getInsertId('series', 'series_id');
    }

    /**
     * Associate a category with a series.
     *
     * @param int $seriesId
     * @param int $categoryId
     */
    public function addCategory($seriesId, $categoryId)
    {
        $this->update(
            'INSERT INTO series_categories
				(series_id, category_id)
			VALUES
				(?, ?)',
            [(int) $seriesId, (int) $categoryId]
        );
    }

    /**
     * Unassociate all categories with a series
     *
     * @param int $seriesId
     */
    public function removeCategories($seriesId)
    {
        $this->update('DELETE FROM series_categories WHERE series_id = ?', [(int) $seriesId]);
    }

    /**
     * Get the categories associated with a given series.
     * @return Collection
     */
    public function getCategories(int $seriesId, ?int $pressId = null) : array
    {
        $categoryIds = DB::table('series_categories AS sc')
            ->join('series AS s', 's.series_id', '=', 'sc.series_id')
            ->when($pressId !== null, function($q) use ($pressId) {
                $q->where('s.press_id', '=', $pressId);
            })
            ->where('s.series_id', '=', $seriesId)
            ->pluck('sc.category_id');
        return array_map(fn($categoryId) => Repo::category()->get($categoryId), iterator_to_array($categoryIds));
    }

    /**
     * Get the categories not associated with a given series.
     */
    public function getUnassignedCategories(int $seriesId, ?int $pressId = null) : array
    {
        $categoryIds = DB::table('series AS s')
            ->join('categories AS c', 'c.context_id', '=', 's.press_id')
            ->leftJoin('series_categories AS sc', function($join) {
                $join->where('s.series_id', '=', 'sc.series_id')
                     ->where('sc.category_id', '='. 'c.category_id');
            })
            ->when($pressId !== null, function($q) {
                $q->where('s.press_id', '=', $pressId);
            })
            ->where('s.series_id', '=', $seriesId)
            ->whereNull('sc.series_id')
            ->pluck('c.category_id');
        return array_map([Repo::category(), 'get'], $categoryIds);
    }

    /**
     * Check if an series exists with the specified ID.
     *
     * @param int $seriesId
     *
     * @return bool
     */
    public function categoryAssociationExists($seriesId, $categoryId)
    {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS row_count FROM series_categories WHERE series_id = ? AND category_id = ?',
            [(int) $seriesId, (int) $categoryId]
        );
        $row = $result->current();
        return $row && $row->row_count;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\press\SeriesDAO', '\SeriesDAO');
}
