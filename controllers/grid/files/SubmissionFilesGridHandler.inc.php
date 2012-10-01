<?php

/**
 * @file controllers/grid/files/SubmissionFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridHandler
 * @ingroup controllers_grid_files
 *
 * @brief Handle submission file grid requests.
 */

// Import UI base classes.
import('lib.pkp.classes.controllers.grid.GridHandler');

// Import submission files grid specific classes.
import('controllers.grid.files.SubmissionFilesGridRow');
import('controllers.grid.files.FileNameGridColumn');

// Import monograph file class which contains the MONOGRAPH_FILE_* constants.
import('classes.monograph.MonographFile');

// Import the class that defines file grids capabilities.
import('classes.controllers.grid.files.FilesGridCapabilities');

class SubmissionFilesGridHandler extends GridHandler {

	/** @var FilesGridCapabilities */
	var $_capabilities;

	/** @var integer */
	var $_stageId;

	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SubmissionFilesGridHandler(&$dataProvider, $stageId, $capabilities) {
		parent::GridHandler($dataProvider);

		if ($stageId) {
			$this->_stageId = (int)$stageId;
		}
		$this->_capabilities = new FilesGridCapabilities($capabilities);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get grid capabilities object.
	 * @return FilesGridCapabilities
	 */
	function &getCapabilities() {
		return $this->_capabilities;
	}

	/**
	 * Get the workflow stage id.
	 * @return integer
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function &getMonograph() {
		// We assume proper authentication by the data provider.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		assert(is_a($monograph, 'Monograph'));
		return $monograph;
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		// Set the stage id from the request parameter if not set previously.
		if (!$this->getStageId()) {
			$stageId = (int) $request->getUserVar('stageId');
			// This will be validated with the authorization policy added by
			// the grid data provider.
			$this->_stageId = $stageId;
		}

		$dataProvider =& $this->getDataProvider();
		$dataProvider->setStageId($this->getStageId());

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load translations.
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_OMP_EDITOR,
			LOCALE_COMPONENT_PKP_COMMON,
			LOCALE_COMPONENT_APPLICATION_COMMON
		);

		// Add grid actions
		$capabilities = $this->getCapabilities();
		$dataProvider =& $this->getDataProvider();
		if($capabilities->canAdd()) {
			assert($dataProvider);
			$this->addAction($dataProvider->getAddFileAction($request));
		}

		// Test whether the tar binary is available for the export to work, if so, add 'download all' grid action
		if ($capabilities->canDownloadAll() && $this->hasGridDataElements($request)) {
			$monograph =& $this->getMonograph();
			$stageId = $this->getStageId();
			$linkParams = array('monographId' => $monograph->getId(), 'stageId' => $stageId);
			$files =& $this->getFilesToDownload($request);

			$this->addAction($capabilities->getDownloadAllAction($request, $files, $linkParams), GRID_ACTION_POSITION_BELOW);
		}

		// The file name column is common to all file grid types.
		$this->addColumn(new FileNameGridColumn($capabilities->canViewNotes(), $this->getStageId()));

		// Set the no items row text
		$this->setEmptyRowText('grid.noFiles');
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 */
	function &getRowInstance() {
		$capabilities = $this->getCapabilities();
		$row = new SubmissionFilesGridRow($capabilities->canDelete(), $capabilities->canViewNotes(), $this->getStageId());
		return $row;
	}


	//
	// Protected methods.
	//
	function getFilesToDownload(&$request) {
		return $this->getGridDataElements($request);
	}
}

?>
