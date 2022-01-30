<?php

/**
 * @file classes/monograph/ChapterDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterDAO
 * @ingroup monograph
 *
 * @see Chapter
 * @see AuthorDAO
 *
 * @brief Operations for retrieving and modifying Chapter objects.
 */

namespace APP\monograph;

use PKP\db\DAOResultFactory;
use PKP\plugins\HookRegistry;
use PKP\plugins\PKPPubIdPluginDAO;

class ChapterDAO extends \PKP\db\DAO implements PKPPubIdPluginDAO
{
    /**
     * Retrieve a chapter by ID.
     *
     * @param int $chapterId
     * @param int $publicationId optional
     *
     * @return Chapter|null
     */
    public function getChapter($chapterId, $publicationId = null)
    {
        $params = [(int) $chapterId];
        if ($publicationId !== null) {
            $params[] = (int) $publicationId;
        }
        $result = $this->retrieve(
            'SELECT * FROM submission_chapters WHERE chapter_id = ?'
            . ($publicationId !== null ? ' AND publication_id = ? ' : ''),
            $params
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Retrieve all chapters of a publication.
     *
     * @param int $publicationId
     * @param bool $orderBySequence
     *
     * @return DAOResultFactory
     */
    public function getByPublicationId($publicationId, $orderBySequence = true)
    {
        return new DAOResultFactory(
            $this->retrieve(
                'SELECT	spc.*
                FROM submission_chapters spc
                INNER JOIN publications p ON (spc.publication_id = p.publication_id)
                WHERE p.publication_id = ?'
                . ($orderBySequence ? ' ORDER BY spc.seq ASC' : ''),
                [(int) $publicationId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Retrieve all chapters of a press.
     *
     * @param int $pressId
     *
     * @return DAOResultFactory
     */
    public function getByContextId($pressId)
    {
        return new DAOResultFactory(
            $this->retrieve(
                'SELECT	spc.*
                FROM submission_chapters spc
                INNER JOIN publications p ON (spc.publication_id = p.publication_id)
                INNER JOIN submissions s ON (p.submission_id = s.submission_id)
                WHERE s.context_id = ?',
                [(int) $pressId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Retrieve all chapters by source chapter id.
     *
     * @param int $sourceChapterId
     * @param bool $orderBySequence
     *
     * @return DAOResultFactory
     */
    public function getBySourceChapterId(int $sourceChapterId, bool $orderByPublicationId = true) : DAOResultFactory
    {
        return new DAOResultFactory(
            $this->retrieve(
                'SELECT	*
                FROM submission_chapters
                WHERE source_chapter_id = ?
                OR (source_chapter_id IS NULL AND chapter_id = ?)'
                . ($orderByPublicationId ? ' ORDER BY publication_id ASC' : ''),
                [$sourceChapterId, $sourceChapterId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Get the list of fields for which locale data is stored.
     *
     * @return array
     */
    public function getLocaleFieldNames()
    {
        return ['title', 'subtitle','abstract'];
    }

    /**
     * Get a list of additional fields that do not have
     * dedicated accessors.
     *
     * @return array
     */
    public function getAdditionalFieldNames()
    {
        $additionalFields = parent::getAdditionalFieldNames();
        // FIXME: Move this to a PID plug-in.
        $additionalFields[] = 'pub-id::publisher-id';
        $additionalFields[] = 'datePublished';
        $additionalFields[] = 'pages';
        $additionalFields[] = 'isPageEnabled';
        return $additionalFields;
    }

    /**
     * Get a new data object
     *
     * @return Chapter
     */
    public function newDataObject()
    {
        return new Chapter();
    }

    /**
     * Internal function to return a Chapter object from a row.
     *
     * @param array $row
     *
     * @return Chapter
     */
    public function _fromRow($row)
    {
        $chapter = $this->newDataObject();
        $chapter->setId((int) $row['chapter_id']);
        $chapter->setData('publicationId', (int) $row['publication_id']);
        $chapter->setSequence((int) $row['seq']);
        $chapter->setData('sourceChapterId', (int) ($row['source_chapter_id'] ?? $row['chapter_id']));

        $this->getDataObjectSettings('submission_chapter_settings', 'chapter_id', $row['chapter_id'], $chapter);

        HookRegistry::call('ChapterDAO::_fromRow', [&$chapter, &$row]);

        return $chapter;
    }

    /**
     * Update the settings for this object
     *
     * @param object $chapter
     */
    public function updateLocaleFields($chapter)
    {
        $this->updateDataObjectSettings(
            'submission_chapter_settings',
            $chapter,
            ['chapter_id' => $chapter->getId()]
        );
    }

    /**
     * Insert a new board chapter.
     *
     * @param Chapter $chapter
     */
    public function insertChapter($chapter)
    {
        $params = [
            (int) $chapter->getData('publicationId'),
            (int) $chapter->getSequence(),
            $chapter->getSourceChapterId(),
        ];
        $query = 'INSERT INTO submission_chapters (publication_id, seq, source_chapter_id) VALUES (?, ?, ?)';

        $this->update($query, $params);
        $chapter->setId($this->getInsertId());
        $this->updateObject($chapter);
        return $chapter->getId();
    }

    /**
     * Update an existing board chapter.
     *
     * @param Chapter $chapter
     */
    public function updateObject($chapter) : void
    {
        $this->update(
            'UPDATE submission_chapters
                SET	publication_id = ?,
                    seq = ?,
                    source_chapter_id = ?
                WHERE
                    chapter_id = ?',
            [
                (int) $chapter->getData('publicationId'),
                (int) $chapter->getSequence(),
                $chapter->getSourceChapterId(),
                (int) $chapter->getId()
            ]
        );
        $this->updateLocaleFields($chapter);
    }

    /**
     * Delete a board chapter, including membership info
     *
     * @param Chapter $chapter
     */
    public function deleteObject($chapter)
    {
        $this->deleteById($chapter->getId());
    }

    /**
     * Delete a board chapter, including membership info
     *
     * @param int $chapterId
     */
    public function deleteById($chapterId)
    {
        $this->update('DELETE FROM submission_chapter_authors WHERE chapter_id = ?', [(int) $chapterId]);
        $this->update('DELETE FROM submission_chapter_settings WHERE chapter_id = ?', [(int) $chapterId]);
        $this->update('DELETE FROM submission_chapters WHERE chapter_id = ?', [(int) $chapterId]);
        $this->update('DELETE FROM submission_file_settings WHERE setting_name = ? AND setting_value = ?', ['chapterId', (int) $chapterId]);
    }

    /**
     * Sequentially renumber  chapters in their sequence order, optionally by monographId
     *
     * @param int $publicationId
     */
    public function resequenceChapters($publicationId)
    {
        $params = [];
        if ($publicationId !== null) {
            $params[] = (int) $publicationId;
        }

        $result = $this->retrieve(
            'SELECT chapter_id FROM submission_chapters
            WHERE 1=1'
            . ($publicationId !== null ? ' AND publication_id = ?' : '')
            . ' ORDER BY seq',
            $params
        );

        $i = 0;
        foreach ($result as $row) {
            $this->update(
                'UPDATE submission_chapters SET seq = ? WHERE chapter_id = ?',
                [++$i, $row->chapter_id]
            );
        }
    }

    /**
     * Get the ID of the last inserted board chapter.
     *
     * @return int
     */
    public function getInsertId()
    {
        return $this->_getInsertId('submission_chapters', 'chapter_id');
    }

    /**
     * @copydoc PKPPubIdPluginDAO::pubIdExists()
     */
    public function pubIdExists($pubIdType, $pubId, $excludePubObjectId, $contextId)
    {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS row_count
            FROM submission_chapter_settings scs
            INNER JOIN submission_chapters sc ON scs.chapter_id = sc.chapter_id
            INNER JOIN publications p ON sc.publication_id = p.publication_id
            INNER JOIN submissions s ON p.submission_id = s.submission_id
            WHERE scs.setting_name = ?
            AND scs.setting_value = ?
            AND sc.chapter_id <> ?
            AND s.context_id = ?',
            [
                'pub-id::' . $pubIdType,
                $pubId,
                (int) $excludePubObjectId,
                (int) $contextId
            ]
        );
        $row = $result->current();
        return $row ? (bool) $row->row_count : false;
    }

    /**
     * @copydoc PKPPubIdPluginDAO::changePubId()
     */
    public function changePubId($pubObjectId, $pubIdType, $pubId)
    {
        $this->replace(
            'submission_chapter_settings',
            [
                'chapter_id' => (int) $pubObjectId,
                'locale' => '',
                'setting_name' => 'pub-id::' . $pubIdType,
                'setting_type' => 'string',
                'setting_value' => (string) $pubId
            ],
            ['chapter_id', 'locale', 'setting_name']
        );
    }

    /**
     * @copydoc PKPPubIdPluginDAO::deletePubId()
     */
    public function deletePubId($pubObjectId, $pubIdType)
    {
        $this->update(
            'DELETE FROM submission_chapter_settings WHERE setting_name = ? AND chapter_id = ?',
            [
                'pub-id::' . $pubIdType,
                (int) $pubObjectId
            ]
        );
        $this->flushCache();
    }

    /**
     * @copydoc PKPPubIdPluginDAO::deleteAllPubIds()
     */
    public function deleteAllPubIds($contextId, $pubIdType)
    {
        $chapters = $this->getByContextId($contextId);
        while ($chapter = $chapters->next()) {
            $this->update(
                'DELETE FROM submission_chapter_settings WHERE setting_name = ? AND chapter_id = ?',
                [
                    'pub-id::' . $pubIdType,
                    (int) $chapter->getId()
                ]
            );
        }
        $this->flushCache();
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\monograph\ChapterDAO', '\ChapterDAO');
}
