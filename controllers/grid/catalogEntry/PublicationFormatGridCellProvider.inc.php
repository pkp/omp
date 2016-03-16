<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridCellProvider
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Base class for a cell provider that can retrieve labels for publication formats
 */

import('lib.pkp.controllers.grid.representations.RepresentationsGridCellProvider');

class PublicationFormatGridCellProvider extends RepresentationsGridCellProvider {

	/**
	 * Constructor
	 * @param $submissionId int Submission ID
	 */
	function PublicationFormatGridCellProvider($submissionId) {
		parent::RepresentationsGridCellProvider($submissionId);
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
	function getTemplateVarsFromRowColumn($row, $column) {
		$data = $row->getData();
		if (is_a($data, 'Representation')) switch ($column->getId()) {
			case 'name':
				$remoteURL = $data->getRemoteURL();
				if ($remoteURL) {
					return array('label' => '<a href="'.htmlspecialchars($remoteURL).'" target="_blank">'.htmlspecialchars($data->getLocalizedName()).'</a>' . '<span class="onix_code">' . $data->getNameForONIXCode() . '</span>');
				}
				return array('label' => htmlspecialchars($data->getLocalizedName()) . '<span class="onix_code">' . $data->getNameForONIXCode() . '</span>');
			case 'isAvailable':
				return array('status' => $data->getIsAvailable()?'completed':'new');
		} else {
			assert(is_array($data) && isset($data['submissionFile']));
			$proofFile = $data['submissionFile'];
			switch ($column->getId()) {
				case 'isAvailable':
					return array('status' => ($proofFile->getSalesType() != null && $proofFile->getDirectSalesPrice() != null)?'completed':'new');
			}
		}
		return parent::getTemplateVarsFromRowColumn($row, $column);
	}

	/**
	 * @see GridCellProvider::getCellActions()
	 */
	function getCellActions($request, $row, $column) {
		$data = $row->getData();
		$router = $request->getRouter();
		if (is_a($data, 'Representation')) {
			switch ($column->getId()) {
				case 'isAvailable':
					return array(new LinkAction(
						'availableRepresentation',
						new RemoteActionConfirmationModal(
							__($data->getIsAvailable()?'grid.catalogEntry.availableRepresentation.removeMessage':'grid.catalogEntry.availableRepresentation.message'),
							__('grid.catalogEntry.availableRepresentation.title'),
							$router->url(
								$request, null, null, 'setAvailable', null,
								array(
									'representationId' => $data->getId(),
									'newAvailableState' => $data->getIsAvailable()?0:1,
									'submissionId' => $data->getSubmissionId(),
								)
							),
							'modal_approve'
						),
						$data->getIsAvailable()?__('grid.catalogEntry.isAvailable'):__('grid.catalogEntry.isNotAvailable'),
						$data->getIsAvailable()?'complete':'incomplete',
						__('grid.action.formatAvailable')
					));
			}
		} else {
			assert(is_array($data) && isset($data['submissionFile']));
			$submissionFile = $data['submissionFile'];
			switch ($column->getId()) {
				case 'isAvailable':
					$salesType = preg_replace('/[^\da-z]/i', '', $submissionFile->getSalesType());
					$salesTypeString = 'editor.monograph.approvedProofs.edit.linkTitle';
					if ($salesType == 'openAccess') {
						$salesTypeString = 'payment.directSales.openAccess';
					} elseif ($salesType == 'directSales') {
						$salesTypeString = 'payment.directSales.directSales';
					} elseif ($salesType == 'notAvailable') {
						$salesTypeString = 'payment.directSales.notAvailable';
					}
					return array(new LinkAction(
						'editApprovedProof',
						new AjaxModal(
							$router->url($request, null, null, 'editApprovedProof', null, array(
								'fileId' => $submissionFile->getFileId() . '-' . $submissionFile->getRevision(),
								'submissionId' => $submissionFile->getSubmissionId(),
								'representationId' => $submissionFile->getAssocId(),
							)),
							__('editor.monograph.approvedProofs.edit'),
							'edit'
						),
						__($salesTypeString),
						$salesType
					));
			}
		}
		return parent::getCellActions($request, $row, $column);
	}
}

?>
