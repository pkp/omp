<?php

/**
 * @file classes/press/FeatureDAO.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class FeatureDAO
 *
 * @see Feature
 *
 * @brief Operations for setting Featured status on various items.
 */

namespace APP\press;

use Illuminate\Support\Facades\DB;

class FeatureDAO extends \PKP\db\DAO
{
    /**
     * Get monograph IDs by association.
     *
     * @param $assocType \APP\core\Application::ASSOC_TYPE_...
     *
     * @return array Associative array seq => monograph ID
     */
    public function getMonographIdsByAssoc(int $assocType, int $assocId): array
    {
        $result = $this->retrieve(
            'SELECT submission_id, seq FROM features WHERE assoc_type = ? AND assoc_id = ? ORDER BY seq',
            [$assocType, $assocId]
        );

        $returner = [];
        foreach ($result as $row) {
            $returner[$row->seq] = $row->submission_id;
        }
        return $returner;
    }

    /**
     * Get feature sequences by association.
     *
     * @param $assocType \APP\core\Application::ASSOC_TYPE_...
     *
     * @return array Associative array monograph ID => seq
     */
    public function getSequencesByAssoc(int $assocType, int $assocId)
    {
        return array_flip($this->getMonographIdsByAssoc($assocType, $assocId));
    }

    /**
     * Insert a new feature.
     *
     * @param int $assocType \APP\core\Application::ASSOC_TYPE_...
     */
    public function insertFeature(int $monographId, int $assocType, int $assocId, int $seq)
    {
        $this->update(
            'INSERT INTO features
				(submission_id, assoc_type, assoc_id, seq)
				VALUES
				(?, ?, ?, ?)',
            [
                $monographId,
                $assocType,
                $assocId,
                $seq
            ]
        );
    }

    /**
     * Delete a feature by ID.
     */
    public function deleteByMonographId(int $monographId): int
    {
        return DB::table('features')
            ->where('submission_id', '=', $monographId)
            ->delete();
    }

    /**
     * Delete a feature by association.
     *
     * @param $assocType \APP\core\Application::ASSOC_TYPE_...
     */
    public function deleteByAssoc(int $assocType, int $assocId): int
    {
        return DB::table('features')
            ->where('assoc_type', '=', $assocType)
            ->where('assoc_id', '=', $assocId)
            ->delete();
    }

    /**
     * Delete a feature.
     *
     * @param int $assocType \APP\core\Application::ASSOC_TYPE_...
     */
    public function deleteFeature(int $monographId, int $assocType, int $assocId): int
    {
        return DB::table('features')
            ->where('submission_id', '=', $monographId)
            ->where('assoc_type', '=', $assocType)
            ->where('assoc_id', '=', $assocId)
            ->delete();
    }

    /**
     * Check if the passed monograph id is featured on the
     * passed associated object.
     *
     * @param $monographId The monograph id to check the feature state.
     * @param $assocType The associated object type that the monograph
     * is featured.
     * @param $assocId The associated object id that the monograph is
     * featured.
     *
     * @return bool Whether or not the monograph is featured.
     */
    public function isFeatured(int $monographId, int $assocType, int $assocId): bool
    {
        return DB::table('features')
            ->where('submission_id', '=', $monographId)
            ->where('assoc_type', '=', $assocType)
            ->where('assoc_id', '=', $assocId)
            ->count() > 0;
    }

    /**
     * Return the monograph's featured settings in all assoc types
     *
     * @param $monographId The monograph id to get the feature state.
     */
    public function getFeaturedAll(int $monographId): array
    {
        $result = $this->retrieve(
            'SELECT assoc_type, assoc_id, seq FROM features WHERE submission_id = ?',
            [$monographId]
        );

        $featured = [];
        foreach ($result as $row) {
            $featured[] = [
                'assoc_type' => (int) $row->assoc_type,
                'assoc_id' => (int) $row->assoc_id,
                'seq' => (int) $row->seq,
            ];
        }
        return $featured;
    }

    /**
     * Get the current sequence position of the passed monograph id.
     *
     * @param $monographId The monograph id to check the sequence position.
     * @param $assocType The monograph associated object type.
     * @param $assocId The monograph associated object id.
     *
     * @return int|bool The monograph sequence position or false if no
     * monograph feature is set.
     */
    public function getSequencePosition(int $monographId, int $assocType, int $assocId): int|bool
    {
        $result = $this->retrieve(
            'SELECT seq FROM features WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
            [$monographId, $assocType, $assocId]
        );
        $row = $result->current();
        return $row ? $row->seq : false;
    }

    public function setSequencePosition(int $monographId, int $assocType, int $assocId, int $sequencePosition): void
    {
        DB::table('features')
            ->where('submission_id', '=', $monographId)
            ->where('assoc_type', '=', $assocType)
            ->where('assoc_id', '=', $assocId)
            ->update(['seq' => $sequencePosition]);
    }

    /**
     * Resequence features by association.
     *
     * @param $assocType \APP\core\Application::ASSOC_TYPE_...
     * @param $assocId Identifier per $assocType
     *
     * @return array Associative array of id => seq for resequenced set
     */
    public function resequenceByAssoc(int $assocType, int $assocId): array
    {
        $result = $this->retrieve(
            'SELECT submission_id FROM features WHERE assoc_type = ? AND assoc_id = ? ORDER BY seq',
            [$assocType, $assocId]
        );

        $returner = [];
        foreach ($result as $key => $value) {
            $this->update(
                'UPDATE features SET seq = ? WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
                [
                    $key + 1,
                    $value->submission_id,
                    (int) $assocType,
                    (int) $assocId
                ]
            );
            $returner[$value->submission_id] = $key;
        }
        return $returner;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\press\FeatureDAO', '\FeatureDAO');
}
