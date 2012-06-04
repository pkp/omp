<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridCellProvider
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Base class for a cell provider that can retrieve labels for publication formats
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

// Import monograph file class which contains the MONOGRAPH_FILE_* constants.
import('classes.monograph.MonographFile');

class PublicationFormatGridCellProvider extends DataObjectGridCellProvider {

	/** @var int */
	var $_monographId;

	/**
	 * Constructor
	 * @param $monographId int
	 */
	function PublicationFormatGridCellProvider($monographId) {
		parent::DataObjectGridCellProvider();
		$this->_monographId = $monographId;
	}


	//
	// Getters and setters.
	//
	/**
	 * Get monograph id.
	 * @return int
	 */
	function getMonographId() {
		return $this->_monographId;
	}


	//
	// Template methods from GridCellProvider
	//
	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'format':
				return array('label' => $element->getNameForONIXCode());
			case 'title':
				return array('label' => $element->getLocalizedTitle());
			case 'proofComplete':
				$monographFiles =& $this->getMonographFiles($element->getId());
				$proofComplete = false;
				// If we have at least one viewable file, we consider
				// proofs as approved.
				foreach ($monographFiles as $file) {
					if ($file->getViewable()) {
						$proofComplete = true;
						break;
					}
				}
				return array('isChecked' => $proofComplete);
			case 'isAvailable':
				return array('isChecked' => $element->getIsAvailable()?true:false);
			case 'price':
				$monographFiles =& $this->getMonographFiles($element->getId());
				$priceConfigured = false;
				// If we have at least one file with a configured price,
				// consider price as configured.
				foreach ($monographFiles as $file) {
					if (!is_null($file->getDirectSalesPrice())) {
						$priceConfigured = true;
						break;
					}
				}
				return array('isChecked' => $priceConfigured);
		}
	}


	function &getMonographFiles($publicationFormatId) {
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisionsByAssocId(
			ASSOC_TYPE_PUBLICATION_FORMAT, $publicationFormatId,
			$this->getMonographId(), MONOGRAPH_FILE_PROOF
		);

		return $monographFiles;
	}
}

?>
