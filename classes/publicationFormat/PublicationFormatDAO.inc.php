<?php
/**
 * @file classes/publicationFormat/PublicationFormatDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatDAO
 * @ingroup publicationFormat
 * @see PublicationFormat
 *
 * @brief Operations for retrieving and modifying PublicationFormat objects.
 */

import('classes.publicationFormat.PublicationFormat');
import('lib.pkp.classes.submission.RepresentationDAO');
import('lib.pkp.classes.plugins.PKPPubIdPluginDAO');

class PublicationFormatDAO extends RepresentationDAO implements PKPPubIdPluginDAO {
	/**
	 * @copydoc RepresentationDAO::getById()
	 */
	function getById($representationId, $publicationId = null, $contextId = null) {
		$params = [(int) $representationId];
		if ($publicationId) $params[] = (int) $publicationId;
		if ($contextId) $params[] = (int) $contextId;

		$result = $this->retrieve(
			'SELECT pf.*
			FROM	publication_formats pf
			' . ($contextId?'
				JOIN publications p ON (p.publication_id = pf.publicationId)
				JOIN submissions s ON (s.submission_id=p.submission_id)':'') . '
			WHERE	pf.publication_format_id=?' .
			($publicationId?' AND pf.publication_id = ?':'') .
			($contextId?' AND s.context_id = ?':''),
			$params
		);
		$row = $result->current();
		return $row ? $this->_fromRow((array) $row) : null;
	}

	/**
	 * Find publication format by querying publication format settings.
	 * @param $settingName string
	 * @param $settingValue mixed
	 * @param $publicationId int optional
	 * @param $pressId int optional
	 * @return array The publication formats identified by setting.
	 */
	function getBySetting($settingName, $settingValue, $publicationId = null, $pressId = null) {
		$params = [$settingName];

		$sql = 'SELECT	pf.*
			FROM	publication_formats pf ';
		if ($pressId) {
			$sql .= 'INNER JOIN publications p ON p.publication_id = pf.publication_id
			INNER JOIN submissions s ON s.submission_id = p.submission_id ';
		}
		if (is_null($settingValue)) {
			$sql .= 'LEFT JOIN publication_format_settings pfs ON pf.publication_format_id = pfs.publication_format_id AND pfs.setting_name = ?
				WHERE	(pfs.setting_value IS NULL OR pfs.setting_value = \'\')';
		} else {
			$params[] = (string) $settingValue;
			$sql .= 'INNER JOIN publication_format_settings pfs ON pf.publication_format_id = pfs.publication_format_id
				WHERE	pfs.setting_name = ? AND pfs.setting_value = ?';
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
	 * @param $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 * @param $pubId string
	 * @param $publicationId int optional
	 * @param $pressId int optional
	 * @return PublicationFormat|null
	 */
	function getByPubId($pubIdType, $pubId, $publicationId = null, $pressId = null) {
		if (empty($pubId)) return null;
		$publicationFormats = $this->getBySetting('pub-id::'.$pubIdType, $pubId, $publicationId, $pressId);
		return array_shift($publicationFormats);
	}

	/**
	 * Retrieve publication format by public ID or, failing that,
	 * internal ID; public ID takes precedence.
	 * @param $representationId string
	 * @param $publicationId int
	 * @return PublicationFormat|null
	 */
	function getByBestId($representationId, $publicationId) {
		$result = $this->retrieve(
			'SELECT pf.*
			FROM	publication_formats pf
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
	 */
	function getByPublicationId($publicationId, $contextId = null) {
		$params = [(int) $publicationId];
		if ($contextId) $params[] = (int) $contextId;

		return new DAOResultFactory(
			$this->retrieve(
				'SELECT pf.*
				FROM	publication_formats pf ' .
				($contextId ?
					'INNER JOIN publications p ON (pf.publication_id=p.publication_id)
					 INNER JOIN submissions s ON (s.submission_id = p.submission_id) '
					: '') .
				'WHERE pf.publication_id=? '
				. ($contextId?' AND s.context_id = ? ':'')
				. 'ORDER BY pf.seq',
				$params
			),
			$this,
			'_fromRow'
		);
	}

	/**
	 * Retrieves a list of publication formats for a press
	 * @param int pressId
	 * @return DAOResultFactory (PublicationFormat)
	 */
	function getByContextId($pressId) {
		return new DAOResultFactory(
			$this->retrieve(
				'SELECT pf.*
				FROM	publication_formats pf
				JOIN	publications p ON (p.publication_id = pf.publication_id)
				JOIN	submissions s ON (s.submission_id = p.submission_id)
				WHERE	s.context_id = ?
				ORDER BY pf.seq',
				[(int) $pressId]
			),
			$this,
			'_fromRow'
		);
	}

	/**
	 * Retrieves a list of approved publication formats for a publication
	 * @param int $publicationId
	 * @return DAOResultFactory (PublicationFormat)
	 */
	function getApprovedByPublicationId($publicationId) {
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
	 * @param $representationId int
	 */
	function deleteById($representationId) {
		$this->update('DELETE FROM publication_format_settings WHERE publication_format_id = ?', [(int) $representationId]);
		$this->update('DELETE FROM publication_formats WHERE publication_format_id = ?', [(int) $representationId]);
	}

	/**
	 * Update the settings for this object
	 * @param $publicationFormat object
	 */
	function updateLocaleFields($publicationFormat) {
		$this->updateDataObjectSettings(
			'publication_format_settings',
			$publicationFormat,
			['publication_format_id' => $publicationFormat->getId()]
		);
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return PublicationFormat
	 */
	function newDataObject() {
		return new PublicationFormat();
	}

	/**
	 * Internal function to return an PublicationFormat object from a row.
	 * @param $row array
	 * @param $callHooks boolean
	 * @return PublicationFormat
	 */
	function _fromRow($row, $params = array(), $callHooks = true) {
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
		$publicationFormat->setRemoteURL($row['remote_url']);
		$publicationFormat->setData('urlPath', $row['url_path']);
		$publicationFormat->setIsAvailable($row['is_available']);

		$this->getDataObjectSettings(
			'publication_format_settings',
			'publication_format_id',
			$row['publication_format_id'],
			$publicationFormat
		);

		if ($callHooks) HookRegistry::call('PublicationFormatDAO::_fromRow', [&$publicationFormat, &$row]);

		return $publicationFormat;
	}

	/**
	 * Insert a publication format.
	 * @param $publicationFormat PublicationFormat
	 * @return int the publication format id.
	 */
	function insertObject($publicationFormat) {
		$this->update(
			'INSERT INTO publication_formats
				(is_approved, entry_key, physical_format, publication_id, seq, file_size, front_matter, back_matter, height, height_unit_code, width, width_unit_code, thickness, thickness_unit_code, weight, weight_unit_code, product_composition_code, product_form_detail_code, country_manufacture_code, imprint, product_availability_code, technical_protection_code, returnable_indicator_code, remote_url, url_path, is_available)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
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
				$publicationFormat->getRemoteURL(),
				$publicationFormat->getData('urlPath'),
				(int) $publicationFormat->getIsAvailable(),
			]
		);

		$publicationFormat->setId($this->_getInsertId('publication_formats', 'publication_format_id'));
		$this->updateLocaleFields($publicationFormat);

		return $publicationFormat->getId();
	}

	/**
	 * Update an existing publication format.
	 * @param $publicationFormat PublicationFormat
	 */
	function updateObject($publicationFormat) {
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
				is_available = ?
			WHERE	publication_format_id = ?',
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
				$publicationFormat->getRemoteURL(),
				$publicationFormat->getData('urlPath'),
				(int) $publicationFormat->getIsAvailable(),
				(int) $publicationFormat->getId()
			]
		);

		$this->updateLocaleFields($publicationFormat);
	}

	/**
	 * Get a list of fields for which we store localized data
	 * @return array
	 */
	function getLocaleFieldNames() {
		return ['name'];
	}

	/**
	 * @see DAO::getAdditionalFieldNames()
	 */
	function getAdditionalFieldNames() {
		$additionalFields = parent::getAdditionalFieldNames();
		$additionalFields[] = 'pub-id::publisher-id';
		return $additionalFields;
	}

	/**
	 * @copydoc PKPPubIdPluginDAO::pubIdExists()
	 */
	function pubIdExists($pubIdType, $pubId, $excludePubObjectId, $contextId) {
		$result = $this->retrieve(
			'SELECT COUNT(*) AS row_count
			FROM publication_format_settings pft
			INNER JOIN publication_formats pf ON pft.publication_format_id = pf.publication_format_id
			INNER JOIN publications p ON p.publication_id = pf.publication_id
			INNER JOIN submissions s ON p.submission_id = s.submission_id
			WHERE pft.setting_name = ?
			AND pft.setting_value = ?
			AND pf.publication_format_id <> ?
			AND s.context_id = ?',
			[
				'pub-id::' . $pubIdType,
				$pubId,
				(int) $excludePubObjectId,
				(int) $contextId
			]
		);
		$row = $result->current();
		return $row && $row->row_count;
	}

	/**
	 * @copydoc PKPPubIdPluginDAO::changePubId()
	 */
	function changePubId($pubObjectId, $pubIdType, $pubId) {
		$this->replace(
			'publication_format_settings',
			[
				'publication_format_id' => (int) $pubObjectId,
				'locale' => '',
				'setting_name' => 'pub-id::'.$pubIdType,
				'setting_type' => 'string',
				'setting_value' => (string)$pubId
			],
			['publication_format_id', 'locale', 'setting_name']
		);
	}

	/**
	 * @copydoc PKPPubIdPluginDAO::deletePubId()
	 */
	function deletePubId($pubObjectId, $pubIdType) {
		$this->update(
			'DELETE FROM publication_format_settings WHERE setting_name = ? AND publication_format_id = ?',
			['pub-id::' . $pubIdType, (int)$pubObjectId]
		);
		$this->flushCache();
	}

	/**
	 * @copydoc PKPPubIdPluginDAO::deleteAllPubIds()
	 */
	function deleteAllPubIds($contextId, $pubIdType) {
		$formats = $this->getByContextId($contextId);
		while ($format = $formats->next()) {
			$this->update(
				'DELETE FROM publication_format_settings WHERE setting_name = ? AND publication_format_id = ?',
				['pub-id::' . $pubIdType, (int)$format->getId()]
			);
		}
		$this->flushCache();
	}
}


