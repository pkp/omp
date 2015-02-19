<?php

/**
 * @file controllers/grid/files/proof/ApprovedProofFilesGridRow.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	/**
	 * @copydoc GridRow::initialize()
	 */
	function initialize($request, $template = null) {
		parent::initialize($request, $template);

		// Is this a new row or an existing row?
		$fileId = $this->getId();
		assert(!empty($fileId));

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$proofFile =& $submissionFileDao->getLatestRevision($fileId);

		if ($proofFile->getViewable()) {
			// Actions
			$router = $request->getRouter();
			$this->addAction(
				new LinkAction(
					'editApprovedProof',
					new AjaxModal(
						$router->url($request, null, null, 'editApprovedProof', null, array(
							'fileId' => $fileId,
							'submissionId' => $request->getUserVar('submissionId'),
							'publicationFormatId' => $request->getUserVar('publicationFormatId'),
						)),
						__('editor.monograph.approvedProofs.edit'),
						'edit'
					),
					__('editor.monograph.approvedProofs.edit.linkTitle'),
					'edit'
				)
			);
		} else {
			$this->setNoActionMessage(__('grid.catalogEntry.availablePublicationFormat.proofNotApproved'));
		}
	}
}

?>
