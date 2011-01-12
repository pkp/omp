<?php

/**
 * @file controllers/grid/files/submissionFiles/SubmissionFilesGridRow.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridRow
 * @ingroup controllers_grid_files_submissionFiles
 *
 * @brief Handle submission file grid row requests.
 */

// Import grid base classes.
import('lib.pkp.classes.controllers.grid.GridRow');

// Import UI base classes.
import('lib.pkp.classes.linkAction.request.ConfirmationModal');
import('lib.pkp.classes.linkAction.request.AjaxModal');

class SubmissionFilesGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function SubmissionFilesGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods from GridRow
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $template = 'controllers/grid/gridRowWithActions.tpl') {
		parent::initialize($request, $template);

		// Retrieve the monograph file.
		$monographFile =& $this->getData(); /* @var $monographFile MonographFile */
		if (is_a($monographFile, 'MonographFile')) {
			// Actions
			$router =& $request->getRouter();
			// FIXME: Consolidate action params, see #6338.
			$this->addAction(
				new LinkAction(
					'deleteFile',
					new ConfirmationModal(
						'common.confirmDelete',
						null,
						$router->url($request, null, null, 'deleteFile', null,
								array('monographId' => $monographFile->getMonographId(), 'fileId' => $monographFile->getFileId()))
					),
					'grid.action.delete',
					'delete'
				));
			$this->addAction(
				new LinkAction(
					'moreInfo',
					new AjaxModal($router->url($request, null,
						'informationCenter.FileInformationCenterHandler', 'viewInformationCenter', null,
						array('monographId' => $monographFile->getMonographId(), 'fileId' => $monographFile->getFileId()
					)),
					'grid.action.moreInformation',
					'more_info'
				)
			);
		}
	}
}
