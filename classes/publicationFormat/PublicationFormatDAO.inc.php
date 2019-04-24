<?php
/**
 * @file classes/publicationFormat/PublicationFormatDAO.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @copydoc RepresentationDAO::getById()
	 */
	function getById($representationId, $submissionId = null, $contextId = null) {
		$params = array((int) $representationId);
		if ($submissionId) $params[] = (int) $submissionId;
		if ($contextId) $params[] = (int) $contextId;

		$result = $this->retrieve(
			'SELECT pf.*
			FROM	publication_formats pf
			' . ($contextId?' JOIN submissions s ON (s.submission_id=pf.submission_id)':'') . '
			WHERE	pf.publication_format_id=?' .
			($submissionId?' AND pf.submission_id = ?':'') .
			($contextId?' AND s.context_id = ?':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		return $returner;
	}

	/**
	 * Find publication format by querying publication format settings.
	 * @param $settingName string
	 * @param $settingValue mixed
	 * @param $submissionId int optional
	 * @param $pressId int optional
	 * @return array The publication formats identified by setting.
	 */
	function getBySetting($settingName, $settingValue, $submissionId = null, $pressId = null, $submissionVersion = null) {
		$params = array($settingName);


		$sql = 'SELECT	pf.*
			FROM	publication_formats pf
				INNER JOIN submissions s ON s.submission_id = pf.submission_id
				LEFT JOIN published_submissions ps ON pf.submission_id = ps.submission_id and (ps.published_submission_version = s.submission_version) and ps.is_current_submission_version = 1';
		if (is_null($settingValue)) {
			$sql .= 'LEFT JOIN publication_format_settings pfs ON pf.publication_format_id = pfs.publication_format_id AND pfs.setting_name = ?
				WHERE	(pfs.setting_value IS NULL OR pfs.setting_value = \'\')';
		} else {
			$params[] = (string) $settingValue;
			$sql .= 'INNER JOIN publication_format_settings pfs ON pf.publication_format_id = pfs.publication_format_id
				WHERE	pfs.setting_name = ? AND pfs.setting_value = ?';
		}

		if ($submissionId) {
			$params[] = (int) $submissionId;
			$sql .= ' AND pf.submission_id = ?';
		}

		if ($pressId) {
			$params[] = (int) $pressId;
			$sql .= ' AND s.context_id = ?';
		}

		if ($submissionVersion) {
			$params[] = (int) $submissionVersion;
			$sql .= ($submissionVersion !== null ? ' AND pf.submission_version = ? ' : '');
		}

		$sql .= ' ORDER BY s.context_id, pf.seq, pf.publication_format_id';
		$result = $this->retrieve($sql, $params);

		$publicationFormats = array();
		while (!$result->EOF) {
			$publicationFormats[] = $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}
		$result->Close();

		return $publicationFormats;
	}

	/**
	 * Retrieve publication format by public ID
	 * @param $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 * @param $pubId string
	 * @param $submissionId int optional
	 * @param $pressId int optional
	 * @return PublicationFormat|null
	 */
	function getByPubId($pubIdType, $pubId, $submissionId = null, $pressId = null, $submissionVersion = null) {
		$publicationFormat = null;
		if (!empty($pubId)) {
			$publicationFormats = $this->getBySetting('pub-id::'.$pubIdType, $pubId, $submissionId, $pressId, $submissionVersion);
			if (!empty($publicationFormats)) {
				assert(count($publicationFormats) == 1);
				$publicationFormat = $publicationFormats[0];
			}
		}
		return $publicationFormat;
	}

	/**
	 * Retrieve publication format by public ID or, failing that,
	 * internal ID; public ID takes precedence.
	 * @param $representationId string
	 * @param $submissionId int
	 * @return PublicationFormat|null
	 */
	function getByBestId($representationId, $submissionId, $submissionVersion = null) {
		$publicationFormat = null;
		if ($representationId != '') $publicationFormat = $this->getByPubId('publisher-id', $representationId, $submissionId, null, $submissionVersion);
		if (!isset($publicationFormat) && ctype_digit("$representationId")) $publicationFormat = $this->getById((int) $representationId, $submissionId);
		return $publicationFormat;
	}

	/**
	 * @copydoc RepresentationDAO::getBySubmissionId()
	 */
	function getBySubmissionId($submissionId, $contextId = null, $submissionVersion = null) {
		$params = array((int) $submissionId);
		if ($contextId) $params[] = (int) $contextId;

		if ($submissionVersion) {
			$params[] = (int) $submissionVersion;
    }

		return new DAOResultFactory(
			$this->retrieve(
				'SELECT pf.*
				FROM	publication_formats pf ' .
				($contextId?'INNER JOIN submissions s ON (pf.submission_id=s.submission_id) ':'') .
				'WHERE	pf.submission_id=? '
				. ($contextId?' AND s.context_id = ? ':'')
				. ($submissionVersion ? ' AND pf.submission_version = ? ' : ' AND pf.is_current_submission_version = 1 ')
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
		$params = array((int) $pressId);
		$result = $this->retrieve(
			'SELECT pf.*
			FROM	publication_formats pf
			JOIN	submissions s ON (s.submission_id = pf.submission_id)
			WHERE	s.context_id = ?
			AND pf.is_current_submission_version = 1 
			ORDER BY pf.seq',
			$params
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Retrieves a list of approved publication formats for a published submission
	 * @param int $submissionId
	 * @return DAOResultFactory (PublicationFormat)
	 */
	function getApprovedBySubmissionId($submissionId, $submissionVersion = null) {
		$params = array((int) $submissionId);

		if ($submissionVersion) {
			$params[] = (int) $submissionVersion;
    }

		$result = $this->retrieve(
			'SELECT *
			FROM	publication_formats
			WHERE	submission_id = ? AND is_approved=1' .
			($submissionVersion ? ' AND submission_version = ? ' : ' AND is_current_submission_version = 1')
			.'ORDER BY seq',
			$params
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Delete an publication format by ID.
	 * @param $representationId int
	 */
	function deleteById($representationId) {
		// remove settings, then the association itself.
		$this->update('DELETE FROM publication_format_settings WHERE publication_format_id = ?', (int) $representationId);
		return $this->update('DELETE FROM publication_formats WHERE publication_format_id = ?', (int) $representationId);
	}

	/**
	 * Update the settings for this object
	 * @param $publicationFormat object
	 */
	function updateLocaleFields($publicationFormat) {
		$this->updateDataObjectSettings(
			'publication_format_settings',
			$publicationFormat,
			array('publication_format_id' => $publicationFormat->getId())
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
		$publicationFormat->setSequence($row['seq']);
		$publicationFormat->setId($row['publication_format_id']);
		$publicationFormat->setSubmissionId($row['submission_id']);
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
		$publicationFormat->setIsAvailable($row['is_available']);
		$publicationFormat->setSubmissionVersion($row['submission_version']);
		$publicationFormat->setPrevVerAssocId($row['prev_ver_id']);
		$publicationFormat->setIsCurrentSubmissionVersion($row['is_current_submission_version']);

		$this->getDataObjectSettings(
			'publication_format_settings',
			'publication_format_id',
			$row['publication_format_id'],
			$publicationFormat
		);

		if ($callHooks) HookRegistry::call('PublicationFormatDAO::_fromRow', array(&$publicationFormat, &$row));

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
				(is_approved, entry_key, physical_format, submission_id, seq, file_size, front_matter, back_matter, height, height_unit_code, width, width_unit_code, thickness, thickness_unit_code, weight, weight_unit_code, product_composition_code, product_form_detail_code, country_manufacture_code, imprint, product_availability_code, technical_protection_code, returnable_indicator_code, remote_url, is_available, submission_version, prev_ver_id, is_current_submission_version)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				(int) $publicationFormat->getIsApproved(),
				$publicationFormat->getEntryKey(),
				(int) $publicationFormat->getPhysicalFormat(),
				(int) $publicationFormat->getMonographId(),
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
				(int) $publicationFormat->getIsAvailable(),
				$publicationFormat->getSubmissionVersion(),
				$publicationFormat->getPrevVerAssocId(),
				$publicationFormat->getIsCurrentSubmissionVersion(),
			)
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
				is_available = ?,
				submission_version = ?,
				prev_ver_id = ?,
				is_current_submission_version = ?
			WHERE	publication_format_id = ?',
			array(
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
				(int) $publicationFormat->getIsAvailable(),
				(int) $publicationFormat->getSubmissionVersion(),
				(int) $publicationFormat->getPrevVerAssocId(),
				(int) $publicationFormat->getIsCurrentSubmissionVersion(),
				(int) $publicationFormat->getId()
			)
		);

		$this->updateLocaleFields($publicationFormat);
	}

	/**
	 * Get a list of fields for which we store localized data
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('name');
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
			'SELECT COUNT(*)
			FROM publication_format_settings pft
			INNER JOIN publication_formats p ON pft.publication_format_id = p.publication_format_id
			INNER JOIN submissions s ON p.submission_id = s.submission_id
			WHERE pft.setting_name = ?
			AND pft.setting_value = ?
			AND p.publication_format_id <> ?
			AND s.context_id = ?
			AND p.is_current_submission_version = 1',
			array(
				'pub-id::'.$pubIdType,
				$pubId,
				(int) $excludePubObjectId,
				(int) $contextId
			)
		);
		$returner = $result->fields[0] ? true : false;
		$result->Close();
		return $returner;
	}

	/**
	 * @copydoc PKPPubIdPluginDAO::changePubId()
	 */
	function changePubId($pubObjectId, $pubIdType, $pubId) {
		$idFields = array(
				'publication_format_id', 'locale', 'setting_name'
		);
		$updateArray = array(
				'publication_format_id' => (int) $pubObjectId,
				'locale' => '',
				'setting_name' => 'pub-id::'.$pubIdType,
				'setting_type' => 'string',
				'setting_value' => (string)$pubId
		);
		$this->replace('publication_format_settings', $updateArray, $idFields);
	}

	/**
	 * @copydoc PKPPubIdPluginDAO::deletePubId()
	 */
	function deletePubId($pubObjectId, $pubIdType) {
		$settingName = 'pub-id::'.$pubIdType;
		$this->update(
			'DELETE FROM publication_format_settings WHERE setting_name = ? AND publication_format_id = ?',
			array(
				$settingName,
				(int)$pubObjectId
			)
		);
		$this->flushCache();
	}

	/**
	 * @copydoc PKPPubIdPluginDAO::deleteAllPubIds()
	 */
	function deleteAllPubIds($contextId, $pubIdType) {
		$settingName = 'pub-id::'.$pubIdType;

		$formats = $this->getByContextId($contextId);
		while ($format = $formats->next()) {
			$this->update(
				'DELETE FROM publication_format_settings WHERE setting_name = ? AND publication_format_id = ?',
				array(
					$settingName,
					(int)$format->getId()
				)
			);
		}
		$this->flushCache();
	}

	function newVersion($submissionId) {
		parent::newVersion($submissionId);

		$submissionDao = Application::getSubmissionDAO();
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /** @var $submissionFileDao SubmissionFileDAO */

		list($oldVersion, $newVersion) = $this->provideSubmissionVersionsForNewVersion($submissionId);

		$representationResults = $this->getBySubmissionId($submissionId, null, $oldVersion);
		$oldRepresentations = $representationResults->toArray();
		$newRepresentationResults = $this->getBySubmissionId($submissionId, null, $newVersion);
		$newRepresentations = $newRepresentationResults->toArray();

		foreach ($newRepresentations as $newRepresentation) {
			$newRepresentation->setIsAvailable(0);

			$this->updateObject($newRepresentation);
		}

		foreach ($oldRepresentations as $oldRepresentation) {
			$newRepresentationId = null;
			foreach ($newRepresentations as $newRepresentation) {
				if ($newRepresentation->getPrevVerAssocId() == $oldRepresentation->getId()) {
					$newRepresentationId = $newRepresentation->getId();
					break;
				}
			}

			$identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO'); /** @var $identificationCodeDao IdentificationCodeDAO */
			$oldIdentificationCodes = $identificationCodeDao->getByPublicationFormatId($oldRepresentation->getId());
			$oldIdentificationCodesArray = $oldIdentificationCodes->toAssociativeArray();
			foreach ($oldIdentificationCodesArray as $oldIdentificationCode) {
				/** @var $oldIdentificationCode IdentificationCode */
				$oldIdentificationCode->setPublicationFormatId($newRepresentationId);

				$identificationCodeDao->insertObject($oldIdentificationCode);
			}

			$marketDao = DAORegistry::getDAO('MarketDAO');
			$oldMarkets = $marketDao->getByPublicationFormatId($oldRepresentation->getId());
			$oldMarketsArray = $oldMarkets->toAssociativeArray();
			foreach ($oldMarketsArray as $oldMarket) {
				$oldMarket->setPublicationFormatId($newRepresentationId);

				$marketDao->insertObject($oldMarket);
			}

			$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO');
			$oldPublicationDates = $publicationDateDao->getByPublicationFormatId($oldRepresentation->getId());
			$oldPublicationDatesArray = $oldPublicationDates->toAssociativeArray();
			foreach ($oldPublicationDatesArray as $oldPublicationDate) {
				$oldPublicationDate->setPublicationFormatId($newRepresentationId);

				$publicationDateDao->insertObject($oldPublicationDate);
			}

			$salesRightsDao = DAORegistry::getDAO('SalesRightsDAO');
			$oldSalesRights = $salesRightsDao->getByPublicationFormatId($oldRepresentation->getId());
			$oldSalesRightsArray = $oldSalesRights->toAssociativeArray();
			foreach ($oldSalesRightsArray as $oldSalesRight) {
				$oldSalesRight->setPublicationFormatId($newRepresentationId);

				$salesRightsDao->insertObject($oldSalesRight);
			}
		}

	}

	function getMasterTableName() {
		return 'publication_formats';
	}
}


