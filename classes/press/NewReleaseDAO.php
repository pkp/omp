<?php

/**
 * @file classes/press/NewReleaseDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NewReleaseDAO
 *
 * @ingroup press
 *
 * @see NewRelease
 *
 * @brief Operations for setting new release status on various items.
 */

namespace APP\press;

use APP\facades\Repo;
use PKP\submission\PKPSubmission;

class NewReleaseDAO extends \PKP\db\DAO
{
    /**
     * Get monograph IDs by association.
     *
     * @param int $assocType Application::ASSOC_TYPE_...
     * @param int $assocId
     *
     * @return array [monographId => true]
     */
    public function getMonographIdsByAssoc($assocType, $assocId)
    {
        $result = $this->retrieve(
            'SELECT submission_id FROM new_releases WHERE assoc_type = ? AND assoc_id = ?',
            [(int) $assocType, (int) $assocId]
        );

        $returner = [];
        foreach ($result as $row) {
            $returner[$row->submission_id] = true;
        }
        return $returner;
    }

    /**
     * Get monographs by association.
     *
     * @param int $assocType Application::ASSOC_TYPE_...
     * @param int $assocId
     *
     * @return array Monograph
     */
    public function getMonographsByAssoc($assocType, $assocId)
    {
        $result = $this->retrieve(
            'SELECT	n.submission_id AS submission_id
			FROM	new_releases n,
				submissions s,
				publications p
			WHERE	n.submission_id = s.submission_id
				AND p.publication_id = s.current_publication_id
				AND n.assoc_type = ? AND n.assoc_id = ?
				AND s.status = ?
			ORDER BY p.date_published DESC',
            [(int) $assocType, (int) $assocId, PKPSubmission::STATUS_PUBLISHED]
        );

        $returner = [];
        foreach ($result as $row) {
            $returner[] = Repo::submission()->get($row->submission_id);
        }
        return $returner;
    }

    /**
     * Insert a new NewRelease.
     *
     * @param int $monographId
     * @param int $assocType Application::ASSOC_TYPE_...
     * @param int $assocId
     */
    public function insertNewRelease($monographId, $assocType, $assocId)
    {
        $this->update(
            'INSERT INTO new_releases
				(submission_id, assoc_type, assoc_id)
				VALUES
				(?, ?, ?)',
            [
                (int) $monographId,
                (int) $assocType,
                (int) $assocId
            ]
        );
    }

    /**
     * Delete a new release by ID.
     */
    public function deleteByMonographId(int $monographId): int
    {
        return DB::table('new_releases')
            ->where('submission_id', '=', $monographId)
            ->delete();
    }

    /**
     * Delete a new release by association.
     *
     * @param int $assocType Application::ASSOC_TYPE_...
     */
    public function deleteByAssoc(int $assocType, int $assocId): int
    {
        return DB::table('new_releases')
            ->where('assoc_type', '=', $assocType)
            ->where('assoc_id', '=', $assocId)
            ->delete();
    }

    /**
     * Delete a new release.
     *
     * @param $assocType Application::ASSOC_TYPE_...
     */
    public function deleteNewRelease(int $monographId, int $assocType, int $assocId): int
    {
        return DB::table('new_releases')
            ->where('submission_id', '=', $monographId)
            ->where('assoc_type', '=', $assocType)
            ->where('assoc_id', '=', $assocId)
            ->delete();
    }

    /**
     * Check if the passed monograph id is marked as new release
     * on the passed associated object.
     *
     * @param int $monographId The monograph id to check the new release state.
     * @param int $assocType The associated object type that the monograph
     * is checked for a new release mark.
     * @param int $assocId The associated object id that the monograph is
     * checked for a new release mark.
     *
     * @return bool Whether or not the monograph is marked as a new release.
     */
    public function isNewRelease($monographId, $assocType, $assocId)
    {
        $result = $this->retrieve(
            'SELECT submission_id FROM new_releases WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
            [(int) $monographId, (int) $assocType, (int) $assocId]
        );
        return (bool) $result->current();
    }

    /**
     * Return the monograph's new release settings in all assoc types
     *
     * @param int $monographId The monograph ID to get the new release state
     *
     * @return array
     */
    public function getNewReleaseAll($monographId)
    {
        $result = $this->retrieve(
            'SELECT assoc_type, assoc_id FROM new_releases WHERE submission_id = ?',
            [(int) $monographId]
        );

        $newRelease = [];
        foreach ($result as $row) {
            $newRelease[] = [
                'assoc_type' => (int) $row->assoc_type,
                'assoc_id' => (int) $row->assoc_id,
            ];
        }
        return $newRelease;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\press\NewReleaseDAO', '\NewReleaseDAO');
}
