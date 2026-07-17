<?php

/**
 * @defgroup oai_omp OMP OAI concerns
 */

/**
 * @file classes/oai/omp/PressOAI.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PressOAI
 *
 * @ingroup oai_omp
 *
 * @see OAIDAO
 *
 * @brief OMP-specific OAI interface.
 * Designed to support both a site-wide and press-specific OAI interface
 * (based on where the request is directed).
 */

namespace APP\oai\omp;

use APP\core\Application;
use APP\press\Press;
use PKP\db\DAO;
use PKP\db\DAORegistry;
use PKP\oai\OAI;
use PKP\oai\OAIRecord;
use PKP\oai\OAIRepository;
use PKP\oai\OAIResumptionToken;
use PKP\plugins\Hook;
use PKP\site\Site;
use PKP\site\VersionDAO;

class PressOAI extends OAI
{
    public ?Site $site;
    public ?Press $press;
    public ?int $pressId;
    public DAO|OAIDAO $dao;

    /**
     * @see OAI#OAI
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $request = Application::get()->getRequest();

        $this->site = $request->getSite();
        $this->press = $request->getPress();
        $this->pressId = isset($this->press) ? $this->press->getId() : null;
        $this->dao = DAORegistry::getDAO('OAIDAO');
        $this->dao->setOAI($this);
    }

    /**
     * Convert monograph ID to OAI identifier.
     */
    public function publicationFormatIdToIdentifier(int $publicationFormatId): string
    {
        return $this->getIdentifierPrefix() . $publicationFormatId;
    }

    /**
     * Convert OAI identifier to monograph ID.
     */
    public function identifierToPublicationFormatId(string $identifier): false|int
    {
        $prefix = $this->getIdentifierPrefix();
        if (strstr($identifier, $prefix)) {
            return (int) str_replace($prefix, '', $identifier);
        } else {
            return false;
        }
    }

    /**
     * Get press ID and series ID corresponding to a set specifier.
     */
    public function setSpecToSeriesId(string $setSpec, ?int $pressId = null): array
    {
        $tmpArray = explode(':', $setSpec);
        if (count($tmpArray) == 1) {
            [$pressSpec] = $tmpArray;
            $seriesSpec = null;
        } elseif (count($tmpArray) == 2) {
            [$pressSpec, $seriesSpec] = $tmpArray;
        } else {
            return [0, 0];
        }
        return $this->dao->getSetPressSeriesId($pressSpec, $seriesSpec, $this->pressId);
    }


    //
    // OAI interface functions
    //

    /**
     * @see OAI#repositoryInfo
     */
    public function repositoryInfo(): OAIRepository
    {
        $info = new OAIRepository();

        if (isset($this->press)) {
            $info->repositoryName = $this->press->getLocalizedName();
            $info->adminEmail = $this->press->getData('contactEmail');
        } else {
            $info->repositoryName = $this->site->getLocalizedTitle();
            $info->adminEmail = $this->site->getLocalizedContactEmail();
        }

        $info->sampleIdentifier = $this->publicationFormatIdToIdentifier(1);
        $info->earliestDatestamp = $this->dao->getEarliestDatestamp([$this->pressId]);

        $info->toolkitTitle = 'Open Monograph Press';
        /** @var VersionDAO $versionDao */
        $versionDao = DAORegistry::getDAO('VersionDAO');
        $currentVersion = $versionDao->getCurrentVersion();
        $info->toolkitVersion = $currentVersion->getVersionString(false);
        $info->toolkitURL = 'https://pkp.sfu.ca/omp/';

        return $info;
    }

    /**
     * @see OAI#validIdentifier
     */
    public function validIdentifier(string $identifier): bool
    {
        return $this->identifierToPublicationFormatId($identifier) !== false;
    }

    /**
     * @see OAI#identifierExists
     */
    public function identifierExists(string $identifier): bool
    {
        $recordExists = false;
        $publicationFormatId = $this->identifierToPublicationFormatId($identifier);
        if ($publicationFormatId) {
            $recordExists = $this->dao->recordExists($publicationFormatId, [$this->pressId]);
        }
        return $recordExists;
    }

    /**
     * @see OAI#record
     */
    public function record(string $identifier): OAIRecord|false
    {
        $publicationFormatId = $this->identifierToPublicationFormatId($identifier);
        if ($publicationFormatId) {
            $record = $this->dao->getRecord($publicationFormatId, [$this->pressId]);
        }
        if (!isset($record)) {
            $record = false;
        }
        return $record;
    }

    /**
     * @see OAI#records
     *
     * @hook PressOAI::records [[&$this, $from, $until, $set, $offset, $limit, &$total, &$records]]
     */
    public function records(
        string $metadataPrefix,
        ?int $from,
        ?int $until,
        ?string $set,
        int $offset,
        int $limit,
        int &$total
    ): ?array {
        $records = null;
        if (!Hook::call('PressOAI::records', [&$this, $from, $until, $set, $offset, $limit, &$total, &$records])) {
            $seriesId = null;
            if (isset($set)) {
                [$pressId, $seriesId] = $this->setSpecToSeriesId($set);
            } else {
                $pressId = $this->pressId;
            }
            $records = $this->dao->getRecords([$pressId, $seriesId], $from, $until, $set, $offset, $limit, $total);
        }
        return $records;
    }

    /**
     * @see OAI#identifiers
     *
     * @hook PressOAI::identifiers [[&$this, $from, $until, $set, $offset, $limit, &$total, &$records]]
     */
    public function identifiers($metadataPrefix, $from, $until, $set, $offset, $limit, &$total): ?array
    {
        $records = null;
        if (!Hook::call('PressOAI::identifiers', [&$this, $from, $until, $set, $offset, $limit, &$total, &$records])) {
            $seriesId = null;
            if (isset($set)) {
                [$pressId, $seriesId] = $this->setSpecToSeriesId($set);
            } else {
                $pressId = $this->pressId;
            }
            $records = $this->dao->getIdentifiers([$pressId, $seriesId], $from, $until, $set, $offset, $limit, $total);
        }
        return $records;
    }

    /**
     * @see OAI#sets
     *
     * @hook PressOAI::sets [[&$this, $offset, $limit, &$total, &$sets]]
     */
    public function sets(int $offset, int $limit, int &$total): ?array
    {
        $sets = null;
        if (!Hook::call('PressOAI::sets', [&$this, $offset, $limit, &$total, &$sets])) {
            $sets = $this->dao->getSets($this->pressId, $offset, $limit, $total);
        }
        return $sets;
    }

    /**
     * @see OAI#resumptionToken
     */
    public function resumptionToken(string $tokenId): false|OAIResumptionToken
    {
        $this->dao->clearTokens();
        $token = $this->dao->getToken($tokenId);
        if (!isset($token)) {
            $token = false;
        }
        return $token;
    }

    /**
     * @see OAI#saveResumptionToken
     */
    public function saveResumptionToken(int $offset, array $params): OAIResumptionToken
    {
        $token = new OAIResumptionToken(null, $offset, $params, time() + $this->config->tokenLifetime);
        $this->dao->insertToken($token);
        return $token;
    }


    //
    // Private helper methods
    //
    /**
     * Get the OAI identifier prefix.
     */
    public function getIdentifierPrefix(): string
    {
        return 'oai:' . $this->config->repositoryId . ':' . 'publicationFormat/';
    }
}
