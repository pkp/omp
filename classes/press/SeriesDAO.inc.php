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

use APP\facades\Repo;
use PKP\context\PKPSectionDAO;
use PKP\db\DAORegistry;
use PKP\db\DAOResultFactory;
use PKP\plugins\HookRegistry;

use PKP\submission\PKPSubmission;

class SeriesDAO extends PKPSectionDAO
{
    /**
     * Retrieve an series by ID.
     *
     * @param $seriesId int
     * @param $pressId int optional
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
     * @param $path string
     * @param $pressId int
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
     * @param $row array
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

        HookRegistry::call('SeriesDAO::_fromRow', [&$series, &$row]);

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
     * @param $series object
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
     * @param $series Series
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
     * @param $series Series
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
     * @param $seriesId int
     * @param $contextId int optional
     */
    public function deleteById($seriesId, $contextId = null)
    {
        // Validate the $contextId, if supplied.
        if (!$this->seriesExists($seriesId, $contextId)) {
            return false;
        }

        $subEditorsDao = DAORegistry::getDAO('SubEditorsDAO'); /* @var $subEditorsDao SubEditorsDAO */
        $subEditorsDao->deleteBySubmissionGroupId($seriesId, ASSOC_TYPE_SECTION, $contextId);

        // Remove monographs from this series
        $submissionIds = Repo::submission()->getIds(
            Repo::submission()
                ->getCollector()
                ->filterBySeriesIds([$seriesId])
        );
        $publications = Repo::publication()->getMany(
            Repo::publication()
                ->getCollector()
                ->filterBySubmissionIds($submissionIds->toArray())
        );
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
     * @param $pressId int
     */
    public function deleteByPressId($pressId)
    {
        $this->deleteByContextId($pressId);
    }

    /**
     * Retrieve all series for a press.
     *
     * @param $pressId int Press ID
     * @param $rangeInfo DBResultRange optional
     * @param $submittableOnly boolean optional. Whether to return only series
     *  that can be submitted to by anyone.
     * @param $withPublicationsOnly boolean optional
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
     * @param $pressId int Press ID
     * @param $rangeInfo DBResultRange optional
     * @param $submittableOnly boolean optional. Whether to return only series
     *  that can be submitted to by anyone.
     * @param $withPublicationsOnly boolean optional
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
                ' . ($withPublicationsOnly ? ' LEFT JOIN publications AS p ON p.series_id = s.series_id ' : '') . '
                WHERE s.press_id = ?
                ' . ($submittableOnly ? ' AND s.editor_restricted = 0' : '') . '
                ' . ($withPublicationsOnly ? ' AND  p.status = ?' : '') . '
                GROUP BY s.series_id
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
     * @param $seriesId int
     * @param $pressId int
     *
     * @return boolean
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
     * @param $seriesId int
     * @param $categoryId int
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
     * @param $seriesId int
     */
    public function removeCategories($seriesId)
    {
        $this->update('DELETE FROM series_categories WHERE series_id = ?', [(int) $seriesId]);
    }

    /**
     * Get the categories associated with a given series.
     *
     * @param $seriesId int
     * @param null|mixed $pressId
     *
     * @return DAOResultFactory
     */
    public function getCategories($seriesId, $pressId = null)
    {
        $params = [(int) $seriesId];
        if ($pressId) {
            $params[] = (int) $pressId;
        }
        return new DAOResultFactory(
            $this->retrieve(
                'SELECT	c.*
				FROM	categories c,
					series_categories sc,
					series s
				WHERE	c.category_id = sc.category_id AND
					s.series_id = ? AND
					' . ($pressId ? ' c.context_id = s.press_id AND s.press_id = ? AND' : '') . '
					s.series_id = sc.series_id',
                $params
            ),
            DAORegistry::getDAO('CategoryDAO'),
            '_fromRow'
        );
    }

    /**
     * Get the categories not associated with a given series.
     *
     * @param $seriesId int
     * @param null|mixed $pressId
     *
     * @return DAOResultFactory
     */
    public function getUnassignedCategories($seriesId, $pressId = null)
    {
        $params = [(int) $seriesId];
        if ($pressId) {
            $params[] = (int) $pressId;
        }
        return new DAOResultFactory(
            $this->retrieve(
                'SELECT	c.*
				FROM	series s
					JOIN categories c ON (c.context_id = s.press_id)
					LEFT JOIN series_categories sc ON (s.series_id = sc.series_id AND sc.category_id = c.category_id)
				WHERE	s.series_id = ? AND
					' . ($pressId ? ' s.press_id = ? AND' : '') . '
					sc.series_id IS NULL',
                $params
            ),
            DAORegistry::getDAO('CategoryDAO'),
            '_fromRow'
        );
    }

    /**
     * Check if an series exists with the specified ID.
     *
     * @param $seriesId int
     *
     * @return boolean
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
