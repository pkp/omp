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

	/** @var boolean */
	var $_inCatalogEntryModal;

	/**
	 * Constructor
	 * @param $monographId int
	 * @param $inCatalogEntryModal boolean Tells if grid is loaded inside
	 * catalog entry modal.
	 */
	function PublicationFormatGridCellProvider($monographId, $inCatalogEntryModal) {
		parent::DataObjectGridCellProvider();
		$this->_monographId = $monographId;
		$this->_inCatalogEntryModal = $inCatalogEntryModal;
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

	/**
	 * Get a flag that tells if grid is loaded
	 * inside the catalog entry modal.
	 * @return boolean
	 */
	function getInCatalogEntryModal() {
		return $this->_inCatalogEntryModal;
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
		$publicationFormat =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($publicationFormat, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'format':
				return array('label' => $publicationFormat->getNameForONIXCode());
			case 'title':
				return array('label' => $publicationFormat->getLocalizedTitle());
			case 'proofComplete':
			case 'isAvailable':
			case 'price':
				return array('status' => $this->getCellState($row, $column));
		}
	}

	/**
	 * Gathers the state of a given cell given a $row/$column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return string
	 */
	function getCellState(&$row, &$column) {
		$publicationFormat =& $row->getData();
		switch ($column->getId()) {
			case 'proofComplete':
				$monographFiles =& $this->getMonographFiles($publicationFormat->getId());
				$proofComplete = false;
				// If we have at least one viewable file, we consider
				// proofs as approved.
				foreach ($monographFiles as $file) {
					if ($file->getViewable()) {
						$proofComplete = true;
						break;
					}
				}
				return $proofComplete?'completed':'new';
			case 'isAvailable':
				$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
				$publishedMonograph =& $publishedMonographDao->getById($publicationFormat->getMonographId());
				return ($publicationFormat->getIsAvailable() && $publishedMonograph)?'completed':'new';
			case 'price':
				$monographFiles =& $this->getMonographFiles($publicationFormat->getId());
				$priceConfigured = false;
				// If we have at least one file with a configured price,
				// consider price as configured.
				foreach ($monographFiles as $file) {
					if (!is_null($file->getDirectSalesPrice())) {
						$priceConfigured = true;
						break;
					}
				}
				return $priceConfigured?'completed':'new';
		}
	}

	/**
	 * @see GridCellProvider::getCellActions()
	 */
	function getCellActions($request, $row, $column) {
		$publicationFormat =& $row->getData();
		$actionArgs = array(
			'monographId' => $publicationFormat->getMonographId(),
			'publicationFormatId' => $publicationFormat->getId()
		);
		$cellState = $this->getCellState($row, $column);
		$action = null;
		$monographId = $publicationFormat->getMonographId();
		$publicationFormatId = $publicationFormat->getId();
		switch ($column->getId()) {
			case 'proofComplete':
				import('controllers.api.proof.linkAction.ApproveProofsLinkAction');
				$action = new ApproveProofsLinkAction($request, $monographId, $publicationFormatId, $cellState);
				break;
			case 'isAvailable':
			case 'price':
				if ($this->getInCatalogEntryModal()) {
					import('lib.pkp.classes.linkAction.request.NullAction');
					$action = new LinkAction('publicationFormatTab', new NullAction(), __('monograph.publicationFormat.openTab'), $cellState);
				} else {
					import('controllers.modals.submissionMetadata.linkAction.CatalogEntryLinkAction');
					$action = new CatalogEntryLinkAction($request, $monographId, WORKFLOW_STAGE_ID_PRODUCTION, $publicationFormatId, $cellState);
				}
				break;
		}
		if ($action) {
			return array($action);
		}
	}

	/**
	 * Get the monograph files associated with the passed
	 * publication format id.
	 * @param $publicationFormatId int
	 * @return array
	 */
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
