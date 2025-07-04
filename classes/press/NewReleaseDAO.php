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
use Illuminate\Support\Facades\DB;
use PKP\submission\PKPSubmission;

class NewReleaseDAO extends \PKP\db\DAO
{
    /**
     * Get monograph IDs by association.
     *
     * @param $assocType \APP\core\Application::ASSOC_TYPE_...
     *
     * @return array [monographId => true]
     */
    public function getMonographIdsByAssoc(int $assocType, int $assocId): array
    {
        $result = $this->retrieve(
            'SELECT submission_id FROM new_releases WHERE assoc_type = ? AND assoc_id = ?',
            [$assocType, $assocId]
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
     * @param $assocType \APP\core\Application::ASSOC_TYPE_...
     *
     * @return Submission[]
     */
    public function getMonographsByAssoc(int $assocType, int $assocId): array
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
            [$assocType, $assocId, PKPSubmission::STATUS_PUBLISHED]
        );

        $returner = [];
        foreach ($result as $row) {
            $returner[] = Repo::submission()->get($row->submission_id);
        }
        return $returner;
    }

    /**
     * Insert a new new release.
     *
     * @param int $assocType \APP\core\Application::ASSOC_TYPE_...
     */
    public function insertNewRelease(int $monographId, int $assocType, int $assocId): void
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
     * @param $monographId The monograph id to check the new release state.
     * @param $assocType The associated object type that the monograph
     * is checked for a new release mark.
     * @param $assocId The associated object id that the monograph is
     * checked for a new release mark.
     *
     * @return bool Whether or not the monograph is marked as a new release.
     */
    public function isNewRelease(int $monographId, int $assocType, int $assocId): bool
    {
        return DB::table('new_releases')
            ->where('submission_id', '=', $submissionId)
            ->where('assoc_type', '=', $assocType)
            ->where('assoc_id', '=', $assocId)
            ->count() > 0;
    }

    /**
     * Return the monograph's new release settings in all assoc types
     *
     * @param $monographId The monograph ID to get the new release state
     */
    public function getNewReleaseAll(int $monographId): array
    {
        $result = $this->retrieve(
            'SELECT assoc_type, assoc_id FROM new_releases WHERE submission_id = ?',
            [$monographId]
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
