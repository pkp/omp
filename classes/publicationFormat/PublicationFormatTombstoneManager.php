<?php

/**
 * @file classes/publicationFormat/PublicationFormatTombstoneManager.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatTombstoneManager
 *
 * @ingroup publicationFormat
 *
 * @brief Class defining basic operations for publication format tombstones.
 */

namespace APP\publicationFormat;

use APP\core\Application;
use APP\facades\Repo;
use APP\oai\omp\OAIDAO;
use APP\press\Press;
use APP\submission\Submission;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\plugins\Hook;
use PKP\submission\PKPSubmission;
use PKP\tombstone\DataObjectTombstone;
use PKP\tombstone\DataObjectTombstoneDAO;

class PublicationFormatTombstoneManager
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Insert a tombstone for the passed publication format.
     *
     * @param PublicationFormat $publicationFormat
     * @param Press $press
     *
     * @hook PublicationFormatTombstoneManager::insertPublicationFormatTombstone [[&$publicationFormatTombstone, &$publicationFormat, &$press]]
     */
    public function insertTombstoneByPublicationFormat($publicationFormat, $press)
    {
        $publication = Repo::publication()->get($publicationFormat->getData('publicationId'));
        $monograph = Repo::submission()->get($publication->getData('submissionId'));
        $series = $publication->getData('seriesId') ? Repo::section()->get($publication->getData('seriesId')) : null;

        $dataObjectTombstoneDao = DAORegistry::getDAO('DataObjectTombstoneDAO'); /** @var DataObjectTombstoneDAO $dataObjectTombstoneDao */
        // delete publication format tombstone to ensure that there aren't
        // more than one tombstone for this publication format
        $dataObjectTombstoneDao->deleteByDataObjectId($publicationFormat->getId());
        // insert publication format tombstone
        if ($series) {
            $setSpec = OAIDAO::setSpec($press, $series);
            $setName = $series->getLocalizedTitle();
        } else {
            $setSpec = OAIDAO::setSpec($press);
            $setName = $press->getLocalizedName();
        }
        $oaiIdentifier = 'oai:' . Config::getVar('oai', 'repository_id') . ':' . 'publicationFormat/' . $publicationFormat->getId();
        $OAISetObjectsIds = [
            Application::ASSOC_TYPE_PRESS => $monograph->getData('contextId'),
            Application::ASSOC_TYPE_SERIES => $publication->getData('seriesId')
        ];

        $publicationFormatTombstone = $dataObjectTombstoneDao->newDataObject(); /** @var DataObjectTombstone $publicationFormatTombstone */
        $publicationFormatTombstone->setDataObjectId($publicationFormat->getId());
        $publicationFormatTombstone->stampDateDeleted();
        $publicationFormatTombstone->setSetSpec($setSpec);
        $publicationFormatTombstone->setSetName($setName);
        $publicationFormatTombstone->setOAIIdentifier($oaiIdentifier);
        $publicationFormatTombstone->setOAISetObjectsIds($OAISetObjectsIds);
        $dataObjectTombstoneDao->insertObject($publicationFormatTombstone);

        if (Hook::call('PublicationFormatTombstoneManager::insertPublicationFormatTombstone', [&$publicationFormatTombstone, &$publicationFormat, &$press])) {
            return;
        }
    }

    /**
     * Insert tombstone for every publication format inside
     * the passed array.
     *
     * @param array $publicationFormats
     */
    public function insertTombstonesByPublicationFormats($publicationFormats, $press)
    {
        foreach ($publicationFormats as $publicationFormat) {
            $this->insertTombstoneByPublicationFormat($publicationFormat, $press);
        }
    }

    /**
     * Insert tombstone for every publication format of the
     * published submissions inside the passed press.
     *
     * @param Press $press
     */
    public function insertTombstonesByPress($press)
    {
        $submissions = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$press->getId()])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->getMany();

        foreach ($submissions as $submission) {
            foreach ($submission->getData('publications') as $publication) {
                if ($publication->getData('status') === PKPSubmission::STATUS_PUBLISHED) {
                    $this->insertTombstonesByPublicationId($publication->getId(), $press);
                }
            }
        }
    }

    /**
     * Delete tombstone for every passed publication format.
     *
     * @param array $publicationFormats
     */
    public function deleteTombstonesByPublicationFormats($publicationFormats)
    {
        foreach ($publicationFormats as $publicationFormat) {
            $tombstoneDao = DAORegistry::getDAO('DataObjectTombstoneDAO'); /** @var DataObjectTombstoneDAO $tombstoneDao */
            $tombstoneDao->deleteByDataObjectId($publicationFormat->getId());
        }
    }

    /**
     * Delete tombstone for every publication format inside the passed press.
     *
     * @param int $pressId
     */
    public function deleteTombstonesByPressId($pressId)
    {
        $submissions = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$pressId])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->getMany();

        foreach ($submissions as $submission) {
            foreach ($submission->getData('publications') as $publication) {
                $this->deleteTombstonesByPublicationFormats($publication->getData('publicationFormats'));
            }
        }
    }

    /**
     * Delete tombstones for every publication format in a publication
     *
     */
    public function deleteTombstonesByPublicationId(int $publicationId)
    {
        /** @var PublicationFormatDAO */
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
        $publicationFormats = $publicationFormatDao
            ->getByPublicationId($publicationId);
        $this->deleteTombstonesByPublicationFormats($publicationFormats);
    }

    /**
     * Insert tombstones for every available publication format in a publication
     *
     * This method will delete any existing tombstones to ensure that duplicates
     * are not created.
     *
     * @param Press $context
     */
    public function insertTombstonesByPublicationId(int $publicationId, $context)
    {
        $this->deleteTombstonesByPublicationId($publicationId);
        /** @var PublicationFormatDAO */
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
        $publicationFormats = $publicationFormatDao
            ->getByPublicationId($publicationId);
        foreach ($publicationFormats as $publicationFormat) {
            if ($publicationFormat->getIsAvailable()) {
                $this->insertTombstoneByPublicationFormat($publicationFormat, $context);
            }
        }
    }
}
