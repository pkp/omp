<?php

/**
 * @file controllers/grid/files/proof/ProofFilesGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofFilesGridCellProvider
 * @ingroup controllers_grid_files_proof
 *
 * @brief Cell provider to retrieve the proof files grid data
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class ProofFilesGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function ProofFilesGridCellProvider() {
		parent::DataObjectGridCellProvider();
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
		$proofFile = $data['submissionFile'];
		switch ($column->getId()) {
			case 'name':
				return array('label' => $proofFile->getLocalizedName());
			case 'approved':
				return array('status' => $proofFile->getViewable()?'completed':'new');
		}
		return parent::getTemplateVarsFromRowColumn($row, $column);
	}

	/**
	 * @see GridCellProvider::getCellActions()
	 */
	function getCellActions($request, $row, $column) {
		$data = $row->getData();
		$proofFile = $data['submissionFile'];
		switch ($column->getId()) {
			case 'approved':
				$router = $request->getRouter();
				import('lib.pkp.classes.linkAction.request.AjaxAction');
				return array(new LinkAction(
					'details',
					new AjaxAction($router->url(
						$request, null, null, 'setApproval',
						null,
						array(
							'submissionId' => $request->getUserVar('submissionId'),
							'representationId' => $request->getUserVar('representationId'),
							'fileId' => $proofFile->getFileId(),
							'revision' => $proofFile->getRevision(),
							'approval' => !$proofFile->getViewable(),
						)
					)),
					$proofFile->getViewable()?__('grid.action.disapprove'):__('grid.action.approve')
				));
				break;
		}
		return parent::getCellActions($request, $row, $column);
	}
}

?>
