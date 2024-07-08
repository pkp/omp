<?php

/**
 * @file classes/press/FeatureDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class FeatureDAO
 *
 * @ingroup press
 *
 * @see Feature
 *
 * @brief Operations for setting Featured status on various items.
 */

namespace APP\press;

class FeatureDAO extends \PKP\db\DAO
{
    /**
     * Get monograph IDs by association.
     *
     * @param int $assocType Application::ASSOC_TYPE_...
     * @param int $assocId
     *
     * @return array Associative array seq => monograph ID
     */
    public function getMonographIdsByAssoc($assocType, $assocId)
    {
        $result = $this->retrieve(
            'SELECT submission_id, seq FROM features WHERE assoc_type = ? AND assoc_id = ? ORDER BY seq',
            [(int) $assocType, (int) $assocId]
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
     * @param int $assocType Application::ASSOC_TYPE_...
     * @param int $assocId
     *
     * @return array Associative array monograph ID => seq
     */
    public function getSequencesByAssoc($assocType, $assocId)
    {
        return array_flip($this->getMonographIdsByAssoc($assocType, $assocId));
    }

    /**
     * Insert a new feature.
     *
     * @param int $monographId
     * @param int $assocType Application::ASSOC_TYPE_...
     * @param int $assocId
     * @param int $seq
     */
    public function insertFeature($monographId, $assocType, $assocId, $seq)
    {
        $this->update(
            'INSERT INTO features
				(submission_id, assoc_type, assoc_id, seq)
				VALUES
				(?, ?, ?, ?)',
            [
                (int) $monographId,
                (int) $assocType,
                (int) $assocId,
                (int) $seq
            ]
        );
    }

    /**
     * Delete a feature by ID.
     */
    public function deleteByMonographId(int $monographId)
    {
        return DB::table('features')
            ->where('submission_id', '=', $monographId)
            ->delete();
    }

    /**
     * Delete a feature by association.
     *
     * @param $assocType Application::ASSOC_TYPE_...
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
     * @param int $assocType Application::ASSOC_TYPE_...
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
     * @param int $monographId The monograph id to check the feature state.
     * @param int $assocType The associated object type that the monograph
     * is featured.
     * @param int $assocId The associated object id that the monograph is
     * featured.
     *
     * @return bool Whether or not the monograph is featured.
     */
    public function isFeatured($monographId, $assocType, $assocId)
    {
        $result = $this->retrieve(
            'SELECT submission_id FROM features WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
            [(int) $monographId, (int) $assocType, (int) $assocId]
        );
        return (bool) $result->current();
    }

    /**
     * Return the monograph's featured settings in all assoc types
     *
     * @param int $monographId The monograph id to get the feature state.
     *
     * @return array
     */
    public function getFeaturedAll($monographId)
    {
        $result = $this->retrieve(
            'SELECT assoc_type, assoc_id, seq FROM features WHERE submission_id = ?',
            [(int) $monographId]
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
     * @param int $monographId The monograph id to check the sequence position.
     * @param int $assocType The monograph associated object type.
     * @param int $assocId The monograph associated object id.
     *
     * @return int or boolean The monograph sequence position or false if no
     * monograph feature is set.
     */
    public function getSequencePosition($monographId, $assocType, $assocId)
    {
        $result = $this->retrieve(
            'SELECT seq FROM features WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
            [(int) $monographId, (int) $assocType, (int) $assocId]
        );
        $row = $result->current();
        return $row ? $row->seq : false;
    }

    public function setSequencePosition($monographId, $assocType, $assocId, $sequencePosition)
    {
        $this->update(
            'UPDATE features SET seq = ? WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
            [(int) $sequencePosition, (int) $monographId, (int) $assocType, (int) $assocId]
        );
    }

    /**
     * Resequence features by association.
     *
     * @param int $assocType Application::ASSOC_TYPE_...
     * @param int $assocId per $assocType
     *
     * @return array Associative array of id => seq for resequenced set
     */
    public function resequenceByAssoc($assocType, $assocId)
    {
        $result = $this->retrieve(
            'SELECT submission_id FROM features WHERE assoc_type = ? AND assoc_id = ? ORDER BY seq',
            [(int) $assocType, (int) $assocId]
        );

        $returner = [];
        $i = 2;
        foreach ($result as $row) {
            $this->update(
                'UPDATE features SET seq = ? WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
                [
                    $i,
                    $row->submission_id,
                    (int) $assocType,
                    (int) $assocId
                ]
            );
            $returner[$row->submission_id] = $i;
            $i += 2;
        }
        return $returner;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\press\FeatureDAO', '\FeatureDAO');
}
