<?php
/**
 * @file classes/publicationFormat/PublicationFormatDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatDAO
 * @ingroup publicationFormat
 * @see PublicationFormat
 *
 * @brief Operations for retrieving and modifying PublicationFormat objects.
 */

import('classes.publicationFormat.PublicationFormat');

class PublicationFormatDAO extends DAO {
	/**
	 * Constructor
	 */
	function PublicationFormatDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a publication format by type id.
	 * @param $publicationFormatId int
	 * @param $monographId optional int
	 * @return PublicationFormat
	 */
	function &getById($publicationFormatId, $monographId = null) {

		$params = array((int) $publicationFormatId);
		if ($monographId != null) {
			$params[] = (int) $monographId;
		}

		$result =& $this->retrieve(
			'SELECT *
			FROM	publication_formats
			WHERE	publication_format_id = ?'
			. ($monographId != null ? ' AND monograph_id = ?' : ''),
			$params
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
			'SELECT	*
			FROM	publication_formats
			WHERE	publication_format_id = ?',
			(int) $publicationFormatId
		);

		$returner = $result->RecordCount();
		return $returner;
	}

	/**
	 * Retrieves a list of publication formats for a published monograph
	 * @param int $monographId
	 * @return DAOResultFactory (PublicationFormat)
	 */
	function getByMonographId($monographId) {
		$result =& $this->retrieve(
			'SELECT *
			FROM	publication_formats
			WHERE	monograph_id = ?',
			(int) $monographId
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Delete an publication format by ID.
	 * @param $publicationFormatId int
	 */
	function deleteById($publicationFormatId) {
		// remove settings, then the association itself.
		$this->update('DELETE FROM publication_format_settings WHERE publication_format_id = ?', (int) $publicationFormatId);
		$result =& $this->update('DELETE FROM publication_formats WHERE publication_format_id = ?', (int) $publicationFormatId);
		return $result; // needed for DAO::getDataChangedEvent test
	}

	/**
	 * Update the settings for this object
	 * @param $publicationFormat object
	 */
	function updateLocaleFields(&$publicationFormat) {
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
	function &_fromRow(&$row, $callHooks = true) {
		$publicationFormat = $this->newDataObject();

		// Add the additional Publication Format data
		$publicationFormat->setEntryKey($row['entry_key']);
		$publicationFormat->setPhysicalFormat($row['physical_format']);
		$publicationFormat->setSeq($row['seq']);
		$publicationFormat->setId($row['publication_format_id']);
		$publicationFormat->setMonographId($row['monograph_id']);
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
		$publicationFormat->setIsAvailable($row['is_available']);
		$publicationFormat->setDirectSalesPrice($row['direct_sales_price']);

		$this->getDataObjectSettings('publication_format_settings', 'publication_format_id', $row['publication_format_id'], $publicationFormat);

		if ($callHooks) HookRegistry::call('PublicationFormatDAO::_fromRow', array(&$publicationFormat, &$row));
		return $publicationFormat;
	}

	/**
	 * Insert a publication format.
	 * @param $publicationFormat PublicationFormat
	 */
	function insertObject(&$publicationFormat) {
		$this->update(
			'INSERT INTO publication_formats
				(entry_key, physical_format, monograph_id, seq, file_size, front_matter, back_matter, height, height_unit_code, width, width_unit_code, thickness, thickness_unit_code, weight, weight_unit_code, product_composition_code, product_form_detail_code, country_manufacture_code, imprint, product_availability_code, technical_protection_code, returnable_indicator_code, is_available, direct_sales_price)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				$publicationFormat->getEntryKey(),
				(int) $publicationFormat->getPhysicalFormat(),
				(int) $publicationFormat->getMonographId(),
				(int) $publicationFormat->getSeq(),
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
				(int) $publicationFormat->getIsAvailable(),
				$publicationFormat->getDirectSalesPrice(),
			)
		);

		$publicationFormat->setId($this->getInsertId('publication_formats', 'publication_format_id'));
		$this->updateLocaleFields($publicationFormat);
	}

	/**
	 * Update an existing publication format.
	 * @param $publicationFormat PublicationFormat
	 */
	function updateObject(&$publicationFormat) {
		$this->update(
			'UPDATE publication_formats
			SET	entry_key = ?,
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
				is_available = ?,
				direct_sales_price = ?
			WHERE publication_format_id = ?',
			array(
				$publicationFormat->getEntryKey(),
				$publicationFormat->getPhysicalFormat(),
				(int) $publicationFormat->getSeq(),
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
				(int) $publicationFormat->getIsAvailable(),
				$publicationFormat->getDirectSalesPrice(),
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
		return array('title');
	}
}

?>
