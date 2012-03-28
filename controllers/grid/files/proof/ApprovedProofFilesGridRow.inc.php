<?php

/**
 * @file controllers/grid/files/proof/ApprovedProofFilesGridRow.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApprovedProofFilesGridRow
 * @ingroup controllers_grid_file_proof
 *
 * @brief Handle approved proof grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class ApprovedProofFilesGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function ApprovedProofFilesGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		// Is this a new row or an existing row?
		$fileId = $this->getId();
		assert(!empty($fileId));

		// Actions
		$router =& $request->getRouter();
		$this->addAction(
			new LinkAction(
				'editApprovedProof',
				new AjaxModal(
					$router->url($request, null, null, 'editApprovedProof', null, array(
						'fileId' => $fileId,
						'monographId' => $request->getUserVar('monographId'),
						'publicationFormatId' => $request->getUserVar('publicationFormatId'),
					)),
					__('editor.monograph.approvedProofs.edit'),
					'edit'
				),
				__('grid.action.edit'),
				'edit'
			)
		);
	}
}

?>
