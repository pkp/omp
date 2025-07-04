<?php

/**
 * @file classes/publicationFormat/PublicationFormatDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatDAO
 *
 * @ingroup publicationFormat
 *
 * @see PublicationFormat
 *
 * @brief Operations for retrieving and modifying PublicationFormat objects.
 */

namespace APP\publicationFormat;

use APP\facades\Repo;
use Illuminate\Support\Facades\DB;
use PKP\db\DAO;
use PKP\db\DAOResultFactory;
use PKP\plugins\Hook;
use PKP\submission\Representation;
use PKP\submission\RepresentationDAOInterface;

class PublicationFormatDAO extends DAO implements RepresentationDAOInterface
{
    /**
     * @copydoc RepresentationDAO::getById()
     */
    public function getById(int $representationId, ?int $publicationId = null, ?int $contextId = null): PublicationFormat
    {
        $params = [(int) $representationId];
        if ($publicationId) {
            $params[] = (int) $publicationId;
        }
        if ($contextId) {
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve(
            'SELECT pf.*
            FROM publication_formats pf
            ' . ($contextId ? '
                JOIN publications p ON (p.publication_id = pf.publicationId)
                JOIN submissions s ON (s.submission_id=p.submission_id)' : '') . '
            WHERE pf.publication_format_id=?' .
            ($publicationId ? ' AND pf.publication_id = ?' : '') .
            ($contextId ? ' AND s.context_id = ?' : ''),
            $params
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Find publication format by querying publication format settings.
     *
     * @param string $settingName
     * @param int $publicationId optional
     *
     * @return array The publication formats identified by setting.
     */
    public function getBySetting($settingName, $settingValue, $publicationId = null, ?int $pressId = null)
    {
        $params = [$settingName];

        $sql = 'SELECT	pf.*
            FROM publication_formats pf ';
        if ($pressId) {
            $sql .= 'INNER JOIN publications p ON p.publication_id = pf.publication_id
            INNER JOIN submissions s ON s.submission_id = p.submission_id ';
        }
        if (is_null($settingValue)) {
            $sql .= 'LEFT JOIN publication_format_settings pfs ON pf.publication_format_id = pfs.publication_format_id AND pfs.setting_name = ?
                WHERE (pfs.setting_value IS NULL OR pfs.setting_value = \'\')';
        } else {
            $params[] = (string) $settingValue;
            $sql .= 'INNER JOIN publication_format_settings pfs ON pf.publication_format_id = pfs.publication_format_id
                WHERE pfs.setting_name = ? AND pfs.setting_value = ?';
        }

        if ($publicationId) {
            $params[] = (int) $publicationId;
            $sql .= ' AND pf.publication_id = ?';
        }

        if ($pressId) {
            $params[] = (int) $pressId;
            $sql .= ' AND s.context_id = ?';
        }

        $orderByContextId = $pressId ? 's.context_id, ' : '';
        $sql .= ' ORDER BY ' . $orderByContextId . 'pf.seq, pf.publication_format_id';
        $result = $this->retrieve($sql, $params);

        $publicationFormats = [];
        foreach ($result as $row) {
            $publicationFormats[] = $this->_fromRow((array) $row);
        }
        return $publicationFormats;
    }

    /**
     * Retrieve publication format by public ID
     *
     * @param string $pubIdType One of the NLM pub-id-type values or
     * 'other::something' if not part of the official NLM list
     * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
     * @param string $pubId
     * @param int $publicationId optional
     *
     * @return PublicationFormat|null
     */
    public function getByPubId($pubIdType, $pubId, $publicationId = null, ?int $pressId = null)
    {
        if (empty($pubId)) {
            return null;
        }
        $publicationFormats = $this->getBySetting('pub-id::' . $pubIdType, $pubId, $publicationId, $pressId);
        return array_shift($publicationFormats);
    }

    /**
     * Retrieve all publication formats that include a given DOI ID
     *
     * @return DAOResultFactory<PublicationFormat>
     */
    public function getByDoiId(int $doiId): DAOResultFactory
    {
        return new DAOResultFactory(
            $this->retrieve(
                'SELECT pf.*
                FROM publication_formats pf
                WHERE pf.doi_id = ?',
                [$doiId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Retrieve publication format by public ID or, failing that,
     * internal ID; public ID takes precedence.
     *
     * @param string $representationId
     * @param int $publicationId
     *
     * @return PublicationFormat|null
     */
    public function getByBestId($representationId, $publicationId)
    {
        $result = $this->retrieve(
            'SELECT pf.*
            FROM publication_formats pf
            WHERE pf.url_path = ?
                AND pf.publication_id = ?',
            [
                $representationId,
                $publicationId,
            ]
        );
        if ($row = $result->current()) {
            return $this->_fromRow((array) $row);
        } elseif (is_int($representationId) || ctype_digit($representationId)) {
            return $this->getById($representationId);
        }
        return null;
    }

    /**
     * @copydoc RepresentationDAO::getByPublicationId()
     *
     * @return DAOResultFactory<PublicationFormat>
     */
    public function getByPublicationId($publicationId, ?int $contextId = null): array
    {
        $params = [(int) $publicationId];
        if ($contextId) {
            $params[] = (int) $contextId;
        }

        $result = new DAOResultFactory(
            $this->retrieve(
                'SELECT pf.*
                FROM publication_formats pf ' .
                ($contextId ?
                    'INNER JOIN publications p ON (pf.publication_id=p.publication_id)
                    INNER JOIN submissions s ON (s.submission_id = p.submission_id) '
                    : '') .
                'WHERE pf.publication_id=? '
                . ($contextId ? ' AND s.context_id = ? ' : '')
                . 'ORDER BY pf.seq',
                $params
            ),
            $this,
            '_fromRow'
        );

        return $result->toAssociativeArray();
    }

    /**
     * Retrieves a list of publication formats for a press
     *
     * @return DAOResultFactory<PublicationFormat>
     */
    public function getByContextId(int $pressId)
    {
        return new DAOResultFactory(
            $this->retrieve(
                'SELECT pf.*
                FROM publication_formats pf
                JOIN publications p ON (p.publication_id = pf.publication_id)
                JOIN submissions s ON (s.submission_id = p.submission_id)
                WHERE s.context_id = ?
                ORDER BY pf.seq',
                [$pressId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Retrieves a list of approved publication formats for a publication
     *
     * @param int $publicationId
     *
     * @return DAOResultFactory<PublicationFormat>
     */
    public function getApprovedByPublicationId($publicationId)
    {
        return new DAOResultFactory(
            $this->retrieve(
                'SELECT *
				FROM	publication_formats
				WHERE	publication_id = ? AND is_approved=1
				ORDER BY seq',
                [(int) $publicationId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Delete an publication format by ID.
     */
    public function deleteById(int $representationId): int
    {
        return DB::table('publication_formats')
            ->where('publication_format_id', '=', $representationId)
            ->delete();
    }

    /**
     * Update the settings for this object
     *
     * @param object $publicationFormat
     */
    public function updateLocaleFields($publicationFormat)
    {
        $this->updateDataObjectSettings(
            'publication_format_settings',
            $publicationFormat,
            ['publication_format_id' => $publicationFormat->getId()]
        );
    }

    /**
     * Construct a new data object corresponding to this DAO.
     *
     */
    public function newDataObject(): PublicationFormat
    {
        return new PublicationFormat();
    }

    /**
     * Internal function to return an PublicationFormat object from a row.
     *
     * @param array $row
     * @param bool $callHooks
     *
     * @return PublicationFormat
     *
     * @hook PublicationFormatDAO::_fromRow [[&$publicationFormat, &$row]]
     */
    public function _fromRow($row, $params = [], $callHooks = true)
    {
        $publicationFormat = $this->newDataObject();

        // Add the additional Publication Format data
        $publicationFormat->setIsApproved($row['is_approved']);
        $publicationFormat->setEntryKey($row['entry_key']);
        $publicationFormat->setPhysicalFormat($row['physical_format']);
        $publicationFormat->setSequence((int) $row['seq']);
        $publicationFormat->setId((int) $row['publication_format_id']);
        $publicationFormat->setData('publicationId', (int) $row['publication_id']);
        $publicationFormat->setFileSize($row['file_size']);
        $publicationFormat->setFrontMatter($row['front_matter']);
        $publicationFormat->setBackMatter($row['back_matter']);
        $publicationFormat->setHeight($row['height']);
        $publicationFormat->setHeightUnitCode($row['height_unit_code']);
        $publicationFormat->setWidth($row['width']);
        $publicationFormat->setWidthUnitCode($row['width_unit_code']);
        $publicationFormat->setThickness($row['thickness']);
        $publicationFormat->setThicknessUnitCode($row['thickness_unit_code']);
        $publicationFormat->setWeight($row['weight']);
        $publicationFormat->setWeightUnitCode($row['weight_unit_code']);
        $publicationFormat->setProductCompositionCode($row['product_composition_code']);
        $publicationFormat->setProductFormDetailCode($row['product_form_detail_code']);
        $publicationFormat->setCountryManufactureCode($row['country_manufacture_code']);
        $publicationFormat->setImprint($row['imprint']);
        $publicationFormat->setProductAvailabilityCode($row['product_availability_code']);
        $publicationFormat->setTechnicalProtectionCode($row['technical_protection_code']);
        $publicationFormat->setReturnableIndicatorCode($row['returnable_indicator_code']);
        $publicationFormat->setData('urlRemote', $row['remote_url']);
        $publicationFormat->setData('urlPath', $row['url_path']);
        $publicationFormat->setIsAvailable($row['is_available']);
        $publicationFormat->setData('doiId', $row['doi_id']);

        if (!empty($publicationFormat->getData('doiId'))) {
            $publicationFormat->setData('doiObject', Repo::doi()->get($publicationFormat->getData('doiId')));
        } else {
            $publicationFormat->setData('doiObject', null);
        }

        $this->getDataObjectSettings(
            'publication_format_settings',
            'publication_format_id',
            $row['publication_format_id'],
            $publicationFormat
        );

        if ($callHooks) {
            Hook::call('PublicationFormatDAO::_fromRow', [&$publicationFormat, &$row]);
        }

        return $publicationFormat;
    }

    /**
     * Insert a publication format.
     *
     * @param PublicationFormat $publicationFormat
     *
     * @return int the publication format id.
     */
    public function insertObject($publicationFormat)
    {
        $this->update(
            'INSERT INTO publication_formats
                (is_approved, entry_key, physical_format, publication_id, seq, file_size, front_matter, back_matter, height, height_unit_code, width, width_unit_code, thickness, thickness_unit_code, weight, weight_unit_code, product_composition_code, product_form_detail_code, country_manufacture_code, imprint, product_availability_code, technical_protection_code, returnable_indicator_code, remote_url, url_path, is_available, doi_id)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $publicationFormat->getIsApproved(),
                $publicationFormat->getEntryKey(),
                (int) $publicationFormat->getPhysicalFormat(),
                (int) $publicationFormat->getData('publicationId'),
                (int) $publicationFormat->getSequence(),
                $publicationFormat->getFileSize(),
                $publicationFormat->getFrontMatter(),
                $publicationFormat->getBackMatter(),
                $publicationFormat->getHeight(),
                $publicationFormat->getHeightUnitCode(),
                $publicationFormat->getWidth(),
                $publicationFormat->getWidthUnitCode(),
                $publicationFormat->getThickness(),
                $publicationFormat->getThicknessUnitCode(),
                $publicationFormat->getWeight(),
                $publicationFormat->getWeightUnitCode(),
                $publicationFormat->getProductCompositionCode(),
                $publicationFormat->getProductFormDetailCode(),
                $publicationFormat->getCountryManufactureCode(),
                $publicationFormat->getImprint(),
                $publicationFormat->getProductAvailabilityCode(),
                $publicationFormat->getTechnicalProtectionCode(),
                $publicationFormat->getReturnableIndicatorCode(),
                $publicationFormat->getData('urlRemote'),
                $publicationFormat->getData('urlPath'),
                (int) $publicationFormat->getIsAvailable(),
                $publicationFormat->getData('doiId') === null ? null : (int) $publicationFormat->getData('doiId')
            ]
        );

        $publicationFormat->setId($this->getInsertId());
        $this->updateLocaleFields($publicationFormat);

        return $publicationFormat->getId();
    }

    /**
     * Update an existing publication format.
     *
     * @param PublicationFormat $publicationFormat
     */
    public function updateObject(Representation $publicationFormat): void
    {
        $this->update(
            'UPDATE publication_formats
            SET	is_approved = ?,
                entry_key = ?,
                physical_format = ?,
                seq = ?,
                file_size = ?,
                front_matter = ?,
                back_matter = ?,
                height = ?,
                height_unit_code = ?,
                width = ?,
                width_unit_code = ?,
                thickness = ?,
                thickness_unit_code = ?,
                weight = ?,
                weight_unit_code = ?,
                product_composition_code = ?,
                product_form_detail_code = ?,
                country_manufacture_code = ?,
                imprint = ?,
                product_availability_code = ?,
                technical_protection_code = ?,
                returnable_indicator_code = ?,
                remote_url = ?,
                url_path = ?,
                is_available = ?,
                doi_id = ?
            WHERE publication_format_id = ?',
            [
                (int) $publicationFormat->getIsApproved(),
                $publicationFormat->getEntryKey(),
                (int) $publicationFormat->getPhysicalFormat(),
                (int) $publicationFormat->getSequence(),
                $publicationFormat->getFileSize(),
                $publicationFormat->getFrontMatter(),
                $publicationFormat->getBackMatter(),
                $publicationFormat->getHeight(),
                $publicationFormat->getHeightUnitCode(),
                $publicationFormat->getWidth(),
                $publicationFormat->getWidthUnitCode(),
                $publicationFormat->getThickness(),
                $publicationFormat->getThicknessUnitCode(),
                $publicationFormat->getWeight(),
                $publicationFormat->getWeightUnitCode(),
                $publicationFormat->getProductCompositionCode(),
                $publicationFormat->getProductFormDetailCode(),
                $publicationFormat->getCountryManufactureCode(),
                $publicationFormat->getImprint(),
                $publicationFormat->getProductAvailabilityCode(),
                $publicationFormat->getTechnicalProtectionCode(),
                $publicationFormat->getReturnableIndicatorCode(),
                $publicationFormat->getData('urlRemote'),
                $publicationFormat->getData('urlPath'),
                (int) $publicationFormat->getIsAvailable(),
                $publicationFormat->getData('doiId'),
                (int) $publicationFormat->getId()
            ]
        );

        $this->updateLocaleFields($publicationFormat);
    }

    /**
     * Get a list of fields for which we store localized data
     */
    public function getLocaleFieldNames(): array
    {
        return ['name'];
    }

    /**
     * @see DAO::getAdditionalFieldNames()
     */
    public function getAdditionalFieldNames(): array
    {
        $additionalFields = parent::getAdditionalFieldNames();
        $additionalFields[] = 'pub-id::publisher-id';
        return $additionalFields;
    }

    /**
     * @copydoc PKPPubIdPluginDAO::pubIdExists()
     */
    public function pubIdExists(string $pubIdType, string $pubId, int $excludePubObjectId, int $contextId): bool
    {
        return DB::table('publication_format_settings AS pft')
            ->join('publication_formats AS pf', 'pft.publication_format_id', '=', 'pf.publication_format_id')
            ->join('publications AS p', 'p.publication_id', '=', 'pf.publication_id')
            ->join('submissions AS s', 's.submission_id', '=', 'p.submission_id')
            ->where('pft.setting_name', '=', "pub-id::{$pubIdType}")
            ->where('pft.setting_value', '=', $pubId)
            ->where('pf.publication_format_id', '<>', $excludePubObjectId)
            ->where('s.context_id', '=', $contextId)
            ->count() > 0;
    }

    /**
     * @copydoc PKPPubIdPluginDAO::changePubId()
     */
    public function changePubId($pubObjectId, $pubIdType, $pubId)
    {
        DB::table('publication_format_settings')->updateOrInsert(
            ['publication_format_id' => (int) $pubObjectId, 'locale' => '', 'setting_name' => 'pub-id::' . $pubIdType],
            ['setting_type' => 'string', 'setting_value' => (string)$pubId]
        );
    }

    /**
     * @copydoc PKPPubIdPluginDAO::deletePubId()
     */
    public function deletePubId(int $pubObjectId, string $pubIdType): int
    {
        return DB::table('publication_format_settings')
            ->where('setting_name', '=', "pub-id::{$pubIdType}")
            ->where('publication_format_id', '=', $pubObjectId)
            ->delete();
    }

    /**
     * @copydoc PKPPubIdPluginDAO::deleteAllPubIds()
     */
    public function deleteAllPubIds(int $contextId, string $pubIdType): int
    {
        $formats = $this->getByContextId($contextId);
        $affectedRows = 0;
        while ($format = $formats->next()) {
            $affectedRows += $this->deletePubId($format->getId(), $pubIdType);
        }
        return $affectedRows;
    }
}
