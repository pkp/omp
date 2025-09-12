<?php

/**
 * @file classes/monograph/ChapterDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterDAO
 *
 * @ingroup monograph
 *
 * @see Chapter
 * @see AuthorDAO
 *
 * @brief Operations for retrieving and modifying Chapter objects.
 */

namespace APP\monograph;

use APP\facades\Repo;
use APP\publication\Publication;
use Illuminate\Support\Facades\DB;
use PKP\db\DAOResultFactory;
use PKP\doi\Doi;
use PKP\plugins\Hook;
use PKP\plugins\PKPPubIdPluginDAO;

class ChapterDAO extends \PKP\db\DAO implements PKPPubIdPluginDAO
{
    /**
     * Retrieve a chapter by ID.
     */
    public function getChapter(int $chapterId, ?int $publicationId = null): ?Chapter
    {
        $params = [$chapterId];
        if ($publicationId !== null) {
            $params[] = $publicationId;
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
     * @return DAOResultFactory<Chapter>
     */
    public function getByPublicationId(int $publicationId, bool $orderBySequence = true): DAOResultFactory
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
     * @return DAOResultFactory<Chapter>
     */
    public function getByContextId(int $pressId): DAOResultFactory
    {
        return new DAOResultFactory(
            $this->retrieve(
                'SELECT	spc.*
                FROM submission_chapters spc
                INNER JOIN publications p ON (spc.publication_id = p.publication_id)
                INNER JOIN submissions s ON (p.submission_id = s.submission_id)
                WHERE s.context_id = ?',
                [$pressId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Retrieve all chapters that include a given DOI ID
     *
     * @return DAOResultFactory<Chapter>
     */
    public function getByDoiId(int $doiId): DAOResultFactory
    {
        return new DAOResultFactory(
            $this->retrieve(
                'SELECT spc.*
                FROM submission_chapters spc
                WHERE spc.doi_id = ?',
                [$doiId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Retrieve all chapters by source chapter id.
     *
     * @return DAOResultFactory<Chapter>
     */
    public function getBySourceChapterId(int $sourceChapterId, bool $orderByPublicationId = true): DAOResultFactory
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
     * Retrieve a chapter by source chapter ID and publication ID.
     */
    public function getBySourceChapterAndPublication(int $sourceChapterId, int $publicationId): ?Chapter
    {
        $result = $this->retrieve(
            'SELECT *
            FROM submission_chapters
            WHERE (source_chapter_id = ? OR (source_chapter_id IS NULL AND chapter_id = ?))
            AND publication_id = ?',
            [$sourceChapterId, $sourceChapterId, $publicationId]
        );

        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Get the list of fields for which locale data is stored.
     */
    public function getLocaleFieldNames(): array
    {
        $localFieldNames = parent::getLocaleFieldNames();
        $localFieldNames[] = 'title';
        $localFieldNames[] = 'subtitle';
        $localFieldNames[] = 'abstract';

        return $localFieldNames;
    }

    /**
     * Get a list of additional fields that do not have
     * dedicated accessors.
     */
    public function getAdditionalFieldNames(): array
    {
        $additionalFields = parent::getAdditionalFieldNames();
        // FIXME: Move this to a PID plug-in.
        $additionalFields[] = 'pub-id::publisher-id';
        $additionalFields[] = 'datePublished';
        $additionalFields[] = 'pages';
        $additionalFields[] = 'isPageEnabled';
        $additionalFields[] = 'licenseUrl';
        return $additionalFields;
    }

    /**
     * Get a new data object
     */
    public function newDataObject(): Chapter
    {
        return new Chapter();
    }

    /**
     * Internal function to return a Chapter object from a row.
     *
     * @hook ChapterDAO::_fromRow [[&$chapter, &$row]]
     */
    public function _fromRow(array $row): Chapter
    {
        $chapter = $this->newDataObject();
        $chapter->setId((int) $row['chapter_id']);
        $chapter->setData('publicationId', (int) $row['publication_id']);
        $chapter->setSequence((int) $row['seq']);
        $chapter->setData('sourceChapterId', (int) ($row['source_chapter_id'] ?? $row['chapter_id']));
        $chapter->setData('doiId', $row['doi_id']);

        if (!empty($chapter->getData('doiId'))) {
            $chapter->setData('doiObject', Repo::doi()->get($chapter->getData('doiId')));
        } else {
            $chapter->setData('doiObject', null);
        }

        $this->getDataObjectSettings('submission_chapter_settings', 'chapter_id', $row['chapter_id'], $chapter);

        Hook::call('ChapterDAO::_fromRow', [&$chapter, &$row]);

        return $chapter;
    }

    /**
     * Update the settings for this object
     */
    public function updateLocaleFields(Chapter $chapter): void
    {
        $this->updateDataObjectSettings(
            'submission_chapter_settings',
            $chapter,
            ['chapter_id' => $chapter->getId()]
        );
    }

    /**
     * Insert a new chapter.
     */
    public function insertChapter(Chapter $chapter): int
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
     * Update an existing chapter.
     */
    public function updateObject(Chapter $chapter): void
    {
        $this->update(
            'UPDATE submission_chapters
                SET	publication_id = ?,
                    seq = ?,
                    source_chapter_id = ?,
                    doi_id = ?
                WHERE
                    chapter_id = ?',
            [
                (int) $chapter->getData('publicationId'),
                (int) $chapter->getSequence(),
                $chapter->getSourceChapterId(),
                $chapter->getData('doiId'),
                (int) $chapter->getId()
            ]
        );
        $this->updateLocaleFields($chapter);
    }

    /**
     * Delete a chapter
     */
    public function deleteObject(Chapter $chapter): int
    {
        return $this->deleteById($chapter->getId());
    }

    /**
     * Delete a chapter
     */
    public function deleteById(int $chapterId): int
    {
        DB::table('submission_file_settings')
            ->where('setting_name', '=', 'chapterId')
            ->where('setting_value', '=', $chapterId)
            ->delete();
        return DB::table('submission_chapters')->where('chapter_id', '=', $chapterId)->delete();
    }

    /**
     * Sequentially renumber chapters in their sequence order, optionally by monographId
     *
     */
    public function resequenceChapters(int $publicationId): void
    {
        $params = [];
        if ($publicationId !== null) {
            $params[] = $publicationId;
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
     * @copydoc PKPPubIdPluginDAO::pubIdExists()
     */
    public function pubIdExists(string $pubIdType, string $pubId, int $excludePubObjectId, int $contextId): bool
    {
        return DB::table('submission_chapter_settings AS scs')
            ->join('submission_chapters AS sc', 'scs.chapter_id', '=', 'sc.chapter_id')
            ->join('publications AS p', 'sc.publication_id', '=', 'p.publication_id')
            ->join('submissions AS s', 'p.submission_id', '=', 's.submission_id')
            ->where('scs.setting_name', '=', "pub-id::{$pubIdType}")
            ->where('scs.setting_value', '=', $pubId)
            ->where('sc.chapter_id', '<>', $excludePubObjectId)
            ->where('s.context_id', '=', $contextId)
            ->count() > 0;
    }

    /**
     * @copydoc PKPPubIdPluginDAO::changePubId()
     */
    public function changePubId($pubObjectId, $pubIdType, $pubId)
    {
        DB::table('submission_chapter_settings')->updateOrInsert(
            ['chapter_id' => (int) $pubObjectId, 'locale' => '', 'setting_name' => 'pub-id::' . $pubIdType],
            ['setting_type' => 'string', 'setting_value' => (string) $pubId]
        );
    }

    /**
     * @copydoc PKPPubIdPluginDAO::deletePubId()
     */
    public function deletePubId(int $pubObjectId, string $pubIdType): int
    {
        return DB::table('submission_chapter_settings')
            ->where('setting_name', '=', "pub-id::{$pubIdType}")
            ->where('chapter_id', '=', $pubObjectId)
            ->delete();
    }

    /**
     * @copydoc PKPPubIdPluginDAO::deleteAllPubIds()
     */
    public function deleteAllPubIds(int $contextId, string $pubIdType): int
    {
        $chapters = $this->getByContextId($contextId);
        $affectedRows = 0;
        while ($chapter = $chapters->next()) {
            $affectedRows += $this->deletePubId($chapter->getId(), $pubIdType);
        }
        return $affectedRows;
    }

    /**
     * Get chapters of all minor versions of the same submission, that are
     * with the same version stage, version major and DOI ID
     * as the given chpater
     *
     * @return array<int,Chapter>
     */
    public function getMinorVersionsWithSameDoi(Chapter $chapter): array
    {
        $publication = Repo::publication()->get($chapter->getData('publicationId'));
        if (!$publication) {
            return [];
        }

        $allMinorVersionIds = Repo::publication()->getCollector()
            ->filterBySubmissionIds([$publication->getData('submissionId')])
            ->filterByVersionStage($publication->getData('versionStage'))
            ->filterByVersionMajor($publication->getData('versionMajor'))
            ->getIds();
        $rows = DB::table('submission_chapters')
            ->select('*')
            ->whereIn('publication_id', $allMinorVersionIds)
            ->where('source_chapter_id', '=', $chapter->getData('sourceChapterId'))
            ->where('doi_id', '=', $chapter->getData('doiId'))
            ->get();

        $chapters = [];
        foreach ($rows as $row) {
            $chapters[] = $this->_fromRow((array) $row);
        }
        return $chapters;
    }

    /**
     * Get the first DOI object found for the same chapter (with the same source_chapter_id)
     * in a minor version of the same submission,
     * within the same version stage and version major.
     */
    public function getMinorVersionsDoi(Publication $publication, Chapter $chapter): ?Doi
    {
        // Get all minor versions IDs with the same version stage and version major
        $publicationIds = Repo::publication()
            ->getCollector()
            ->filterBySubmissionIds([$publication->getData('submissionId')])
            ->filterByVersionStage($publication->getData('versionStage'))
            ->filterByVersionMajor($publication->getData('versionMajor'))
            ->getIds()
            ->filter(function ($element) use ($publication) {
                return $element != $publication->getId();
            });

        if (empty($publicationIds)) {
            return null;
        }

        $doiId = DB::table('submission_chapters')
            ->select('doi_id')
            ->whereIn('publication_id', $publicationIds)
            ->where('source_chapter_id', '=', $chapter->getData('sourceChapterId'))
            ->whereNotNull('doi_id')
            ->first()?->doi_id;

        return $doiId ? Repo::doi()->get((int) $doiId) : null;
    }

    /**
     * Get the DOI object found for the same chapter (with the same source_chapter_id)
     * in the current publication.
     */
    public function getCurrentPublicationChapterDoi(Publication $currentPublication, Chapter $chapter): ?Doi
    {
        $doiId = DB::table('submission_chapters')
            ->select('doi_id')
            ->whereIn('publication_id', $currentPublication->getId())
            ->where('source_chapter_id', '=', $chapter->getData('sourceChapterId'))
            ->get();

        return $doiId ? Repo::doi()->get((int) $doiId) : null;
    }
}
