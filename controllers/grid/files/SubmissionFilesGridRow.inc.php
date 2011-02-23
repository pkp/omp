<?php

/**
 * @file controllers/grid/files/SubmissionFilesGridRow.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridRow
 * @ingroup controllers_grid_files
 *
 * @brief Handle submission file grid row requests.
 */

// Import grid base classes.
import('lib.pkp.classes.controllers.grid.GridRow');

class SubmissionFilesGridRow extends GridRow {
	/** @var integer */
	var $_fileStage;


	/**
	 * Constructor
	 */
	function SubmissionFilesGridRow($fileStage) {
		$this->_fileStage = (int)$fileStage;
		parent::GridRow();
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the workflow stage file storage that this
	 * row operates on. One of the MONOGRAPH_FILE_*
	 * constants.
	 * @return integer
	 */
	function getFileStage() {
		return $this->_fileStage;
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
			$actionArgs = array('monographId' => $monographFile->getMonographId(),
					'fileStage' => $this->getFileStage(), 'fileId' => $monographFile->getFileId());

			// Delete file action.
			import('lib.pkp.classes.linkAction.request.ConfirmationModal');
			$this->addAction(
					new LinkAction(
							'deleteFile',
							new ConfirmationModal(
									__('common.confirmDelete'), null,
									$router->url($request, null, 'api.file.FileApiHandler',
											'deleteFile', null, $actionArgs)),
							__('grid.action.delete'),
							'delete'));

			// Information center action.
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
					new LinkAction('moreInfo',
							new AjaxModal(
									$router->url($request, null,
											'informationCenter.FileInformationCenterHandler',
											'viewInformationCenter', null, $actionArgs)),
							__('grid.action.moreInformation'),
							'more_info'));
		}
	}
}
