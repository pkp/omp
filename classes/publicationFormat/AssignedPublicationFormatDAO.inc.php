<?php
/**
 * @file classes/publicationFormat/AssignedPublicationFormatDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AssignedPublicationFormatDAO
 * @ingroup publicationFormat
 * @see AssignedPublicationFormat
 *
 * @brief Operations for retrieving and modifying assigned PublicationFormat objects.
 */

import('classes.publicationFormat.AssignedPublicationFormat');
import('classes.publicationFormat.PublicationFormatDAO');

class AssignedPublicationFormatDAO extends PublicationFormatDAO {
	/**
	 * Constructor
	 */
	function PublicationFormatDAO() {
		parent::PublicationFormatDAO();
	}

	/**
	 * Retrieve a publication format by type id.
	 * @param $assignedPublicationFormatId int
	 * @param $monographId optional int
	 * @return AssignedPublicationFormat
	 */
	function &getById($assignedPublicationFormatId, $monographId = null) {

		$params = array((int) $assignedPublicationFormatId);
		if ($monographId != null) {
			$params[] = (int) $monographId;
		}

		$result =& $this->retrieve(
			'SELECT	pf.*,
				pmpf.*
			FROM	publication_formats pf
			JOIN	published_monograph_publication_formats pmpf ON (pmpf.publication_format_id = pf.publication_format_id)
			WHERE	pmpf.assigned_publication_format_id = ?'
			. ($monographId != null ? ' AND pmpf.monograph_id = ?' : ''), $params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	function getCountByPublicationFormatId($publicationFormatId) {
		$result =& $this->retrieve(
			'SELECT	pmpf.*
			FROM	published_monograph_publication_formats pmpf
			WHERE	pmpf.publication_format_id = ?', array((int) $publicationFormatId)
		);

		$returner = $result->RecordCount();
		return $returner;
	}

	/**
	 * Retrieves a list of assigned publication formats for a published monograph
	 * @param int $monographId
	 * @return DAOResultFactory (AssignedPublicationFormat)
	 */
	function getFormatsByMonographId($monographId) {

		$result =& $this->retrieve(
			'SELECT	pf.*,
			pmpf.*
			FROM	publication_formats pf
			JOIN	published_monograph_publication_formats pmpf ON (pmpf.publication_format_id = pf.publication_format_id)
			WHERE	pmpf.monograph_id = ?',
			array((int) $monographId)
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Delete an assigned publication format by ID.
	 * @param $assignedPublicationFormatId int
	 */
	function deleteAssignedPublicationFormatById($assignedPublicationFormatId) {

		// remove settings, then the association itself.
		$this->update('DELETE FROM published_monograph_publication_format_settings WHERE assigned_publication_format_id = ?', array((int) $assignedPublicationFormatId));
		$result =& $this->update('DELETE FROM published_monograph_publication_formats WHERE assigned_publication_format_id =?', array((int) $assignedPublicationFormatId));
		return $result; // needed for DAO::getDataChangedEvent test
	}

	/**
	 * Update the settings for this object
	 * @param $assignedPublicationFormat object
	 */
	function updateLocaleFields(&$assignedPublicationFormat) {
		$this->updateDataObjectSettings(
			'published_monograph_publication_format_settings',
			$assignedPublicationFormat,
			array('assigned_publication_format_id' => $assignedPublicationFormat->getAssignedPublicationFormatId())
		);
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return AssignedPublicationFormat
	 */
	function newDataObject() {
		return new AssignedPublicationFormat();
	}

	/**
	 * Internal function to return an AssignedPublicationFormat object from a row.
	 * @param $row array
	 * @param $callHooks boolean
	 * @return AssignedPublicationFormat
	 */
	function &_fromRow(&$row, $callHooks = true) {
		// Get the Publication Format object, populated with its data
		$assignedPublicationFormat =& parent::_fromRow($row, $callHooks);

		// Add the additional Assigned Publication Format data
		$assignedPublicationFormat->setSeq($row['seq']);
		$assignedPublicationFormat->setAssignedPublicationFormatId($row['assigned_publication_format_id']);
		$assignedPublicationFormat->setMonographId($row['monograph_id']);
		$assignedPublicationFormat->setFileSize($row['file_size']);
		$assignedPublicationFormat->setFrontMatter($row['front_matter']);
		$assignedPublicationFormat->setBackMatter($row['back_matter']);
		$assignedPublicationFormat->setHeight($row['height']);
		$assignedPublicationFormat->setHeightUnitCode($row['height_unit_code']);
		$assignedPublicationFormat->setWidth($row['width']);
		$assignedPublicationFormat->setWidthUnitCode($row['width_unit_code']);
		$assignedPublicationFormat->setThickness($row['thickness']);
		$assignedPublicationFormat->setThicknessUnitCode($row['thickness_unit_code']);
		$assignedPublicationFormat->setWeight($row['weight']);
		$assignedPublicationFormat->setWeightUnitCode($row['weight_unit_code']);
		$assignedPublicationFormat->setProductCompositionCode($row['product_composition_code']);
		$assignedPublicationFormat->setProductFormDetailCode($row['product_form_detail_code']);
		$assignedPublicationFormat->setCountryManufactureCode($row['country_manufacture_code']);
		$assignedPublicationFormat->setImprint($row['imprint']);
		$assignedPublicationFormat->setProductAvailabilityCode($row['product_availability_code']);
		$assignedPublicationFormat->setTechnicalProtectionCode($row['technical_protection_code']);
		$assignedPublicationFormat->setReturnableIndicatorCode($row['returnable_indicator_code']);
		$assignedPublicationFormat->setIsAvailable($row['is_available']);

		$this->getDataObjectSettings('published_monograph_publication_format_settings', 'assigned_publication_format_id', $row['assigned_publication_format_id'], $assignedPublicationFormat);

		if ($callHooks) HookRegistry::call('AssignedPublicationFormatDAO::_fromRow', array(&$assignedPublicationFormat, &$row));
		return $assignedPublicationFormat;
	}

	/**
	 * Assign a publication format.
	 * @param $assignedPublicationFormat AssignedPublicationFormat
	 */
	function insertObject(&$assignedPublicationFormat) {
		$this->update(
			'INSERT INTO published_monograph_publication_formats
				(monograph_id, publication_format_id, seq, file_size, front_matter, back_matter, height, height_unit_code, width, width_unit_code, thickness, thickness_unit_code, weight, weight_unit_code, product_composition_code, product_form_detail_code, country_manufacture_code, imprint, product_availability_code, technical_protection_code, returnable_indicator_code, is_available)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				(int) $assignedPublicationFormat->getMonographId(),
				(int) $assignedPublicationFormat->getId(),
				(int) $assignedPublicationFormat->getSeq(),
				$assignedPublicationFormat->getFileSize(),
				$assignedPublicationFormat->getFrontMatter(),
				$assignedPublicationFormat->getBackMatter(),
				$assignedPublicationFormat->getHeight(),
				$assignedPublicationFormat->getHeightUnitCode(),
				$assignedPublicationFormat->getWidth(),
				$assignedPublicationFormat->getWidthUnitCode(),
				$assignedPublicationFormat->getThickness(),
				$assignedPublicationFormat->getThicknessUnitCode(),
				$assignedPublicationFormat->getWeight(),
				$assignedPublicationFormat->getWeightUnitCode(),
				$assignedPublicationFormat->getProductCompositionCode(),
				$assignedPublicationFormat->getProductFormDetailCode(),
				$assignedPublicationFormat->getCountryManufactureCode(),
				$assignedPublicationFormat->getImprint(),
				$assignedPublicationFormat->getProductAvailabilityCode(),
				$assignedPublicationFormat->getTechnicalProtectionCode(),
				$assignedPublicationFormat->getReturnableIndicatorCode(),
				(int) $assignedPublicationFormat->getIsAvailable()
			)
		);

		$assignedPublicationFormat->setAssignedPublicationFormatId($this->getInsertId('published_monograph_publication_formats', 'assigned_publication_format_id'));
		$this->updateLocaleFields($assignedPublicationFormat);
	}

	/**
	 * Update an existing publication format.
	 * @param $assignedPublicationFormat AssignedPublicationFormat
	 */
	function updateObject(&$assignedPublicationFormat) {
		$this->update(
			'UPDATE published_monograph_publication_formats
			SET	publication_format_id = ?,
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
				is_available = ?
			WHERE assigned_publication_format_id = ?',
			array(
				(int) $assignedPublicationFormat->getId(),
				(int) $assignedPublicationFormat->getSeq(),
				$assignedPublicationFormat->getFileSize(),
				$assignedPublicationFormat->getFrontMatter(),
				$assignedPublicationFormat->getBackMatter(),
				$assignedPublicationFormat->getHeight(),
				$assignedPublicationFormat->getHeightUnitCode(),
				$assignedPublicationFormat->getWidth(),
				$assignedPublicationFormat->getWidthUnitCode(),
				$assignedPublicationFormat->getThickness(),
				$assignedPublicationFormat->getThicknessUnitCode(),
				$assignedPublicationFormat->getWeight(),
				$assignedPublicationFormat->getWeightUnitCode(),
				$assignedPublicationFormat->getProductCompositionCode(),
				$assignedPublicationFormat->getProductFormDetailCode(),
				$assignedPublicationFormat->getCountryManufactureCode(),
				$assignedPublicationFormat->getImprint(),
				$assignedPublicationFormat->getProductAvailabilityCode(),
				$assignedPublicationFormat->getTechnicalProtectionCode(),
				$assignedPublicationFormat->getReturnableIndicatorCode(),
				(int) $assignedPublicationFormat->getIsAvailable(),
				(int) $assignedPublicationFormat->getAssignedPublicationFormatId()
			)
		);

		$this->updateLocaleFields($assignedPublicationFormat);
	}

	/**
	 * Delete an assigned publication format by id.
	 * @param $assignedPublicationFormatId int
	 */
	function deleteById($assignedPublicationFormatId) {

		return $this->update(
			'DELETE FROM published_monograph_publication_formats
			WHERE assigned_publication_format_id = ?',
			array((int) $assignedPublicationFormatId)
		);
	}

	/**
	 * Get a list of fields for which we store localized data
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title');
	}
}
?>
