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
				WHERE	pmpf.monograph_id = ?', array((int) $monographId)
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

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
	function updateLocaleFields(&$publicationFormat) {
		$this->updateDataObjectSettings('published_monograph_publication_format_settings', $publicationFormat, array(
			'assigned_publication_format_id' => $publicationFormat->getAssignedPublicationFormatId()
		));
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
		$publicationFormat =& parent::_fromRow($row, $callHooks);

		// Add the additional Assigned Publication Format data
		$publicationFormat->setSeq($row['seq']);
		$publicationFormat->setAssignedPublicationFormatId($row['assigned_publication_format_id']);
		$publicationFormat->setMonographId($row['monograph_id']);

		$this->getDataObjectSettings('published_monograph_publication_format_settings', 'assigned_publication_format_id', $row['assigned_publication_format_id'], $publicationFormat);

		if ($callHooks) HookRegistry::call('AssignedPublicationFormatDAO::_fromRow', array(&$publicationFormat, &$row));
		return $publicationFormat;
	}

	/**
	 * Assign a publication format.
	 * @param $assignedPublicationFormat AssignedPublicationFormat
	 */
	function insertObject(&$assignedPublicationFormat) {
		$this->update(
			'INSERT INTO published_monograph_publication_formats
				(monograph_id, publication_format_id, seq)
			VALUES
				(?, ?, ?)',
			array(
				(int) $assignedPublicationFormat->getMonographId(),
				(int) $assignedPublicationFormat->getId(),
				(int) $assignedPublicationFormat->getSeq()
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
				SET publication_format_id = ?, seq = ?
				WHERE assigned_publication_format_id = ?',
				array(
						(int) $assignedPublicationFormat->getId(),
						(int) $assignedPublicationFormat->getSeq(),
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

	/**
	 * Get a list of fields for which we store (non-localized) data
	 * @return array
	 */
	function getAdditionalFieldNames() {
		return array(
					'fileSize', // no companion unit code, template asks for Mb.
					'frontMatter',
					'backMatter',
					'height',
					'heightUnitCode',
					'width',
					'widthUnitCode',
					'thickness',
					'thicknessUnitCode',
					'weight',
					'weightUnitCode',
					'productCompositionCode',
					'productFormCode',
					'productFormDetailCode',
					'price',
					'priceTypeCode',
					'currencyCode',
					'taxRateCode',
					'taxTypeCode',
					'countriesIncludedCode',
					'countryManufactureCode',
					'imprint'
				);
	}
}
?>