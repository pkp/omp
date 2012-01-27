<?php

/**
 * @file classes/controllers/grid/files/SubmissionFilesGridHandlerImplementation.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridHandlerImplementation
 * @ingroup classes_controllers_grid_files
 *
 * @brief This class implements some of the common behaviours and data that a grid handler
 * can use to handle with submission files.
 */

// Import submission files grid specific classes.
import('controllers.grid.files.SubmissionFilesGridRow');
import('controllers.grid.files.FileNameGridColumn');

// Import monograph file class which contains the MONOGRAPH_FILE_* constants.
import('classes.monograph.MonographFile');

// Define the grid capabilities.
define('FILE_GRID_ADD',			0x00000001);
define('FILE_GRID_DOWNLOAD_ALL',	0x00000002);
define('FILE_GRID_DELETE',		0x00000004);
define('FILE_GRID_VIEW_NOTES',		0x00000008);

class SubmissionFilesGridHandlerImplementation {

	/** @var Handler */
	var $_gridHandler;

	/** @var integer */
	var $_stageId;

	/** @var boolean */
	var $_canAdd;

	/** @var boolean */
	var $_canViewNotes;

	/** @var boolean */
	var $_canDownloadAll;

	/** @var boolean */
	var $_canDelete;


	/**
	 * Constructor
	 * @param $gridHandler Handler The grid handler that will delegate
	 * some of its operations to this class.
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SubmissionFilesGridHandlerImplementation(&$gridHandler, $stageId, $capabilities) {
		$this->_gridHandler =& $gridHandler;

		// the StageId can be set later if necessary.
		if ($stageId) {
			$this->_stageId = (int)$stageId;
		}
		$this->setCanAdd($capabilities & FILE_GRID_ADD);
		$this->_canDownloadAll = (boolean)($capabilities & FILE_GRID_DOWNLOAD_ALL);
		$this->setCanDelete($capabilities & FILE_GRID_DELETE);
		$this->_canViewNotes = (boolean)($capabilities & FILE_GRID_VIEW_NOTES);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the grid handler that instantiated this class.
	 * @return Handler
	 */
	function &getGridHandler() {
		return $this->_gridHandler;
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
		$gridHandler =& $this->getGridHandler();
		// We assume proper authentication by the data provider.
		$monograph =& $gridHandler->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		assert(is_a($monograph, 'Monograph'));
		return $monograph;
	}

	/**
	 * Does this grid allow the addition of files or revisions?
	 * @return boolean
	 */
	function canAdd() {
		return $this->_canAdd;
	}

	/**
	 * Set whether or not the grid allows the addition of files or revisions.
	 * @param $canAdd boolean
	 */
	function setCanAdd($canAdd) {
		$this->_canAdd = (boolean) $canAdd;
	}

	/**
	 * Does this grid allow viewing of notes?
	 * @return boolean
	 */
	function canViewNotes() {
		return $this->_canViewNotes;
	}

	/**
	 * Can the user download all files as an archive?
	 * @return boolean
	 */
	function canDownloadAll() {
		return $this->_canDownloadAll;
	}

	/**
	 * Can the user delete files from this grid?
	 * @return boolean
	 */
	function canDelete() {
		return $this->_canDelete;
	}

	/**
	 * Set whether or not the user can delete files from this grid.
	 * @param $canDelete boolean
	 */
	function setCanDelete($canDelete) {
		$this->_canDelete = (boolean) $canDelete;
	}


	//
	// Public methods
	//
	/**
	 * Implementation of the GridHandler::authorize() method.
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		// Set the stage id from the request parameter if not set previously.
		if (!$this->getStageId()) {
			$stageId = (int) $request->getUserVar('stageId');
			// This will be validated with the authorization policy added by
			// the grid data provider.
			$this->_stageId = $stageId;
		}
		$gridHandler =& $this->getGridHandler();
		$dataProvider =& $gridHandler->getDataProvider();
		$dataProvider->setStageId($this->getStageId());
	}

	/**
	 * Implementation of the GridHandler::initialize() method.
	 */
	function initialize(&$request) {
		// Load translations.
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_OMP_EDITOR,
			LOCALE_COMPONENT_PKP_COMMON,
			LOCALE_COMPONENT_APPLICATION_COMMON
		);

		// Add grid actions
		$gridHandler =& $this->getGridHandler();
		$dataProvider =& $gridHandler->getDataProvider();
		if($this->canAdd()) {
			assert($dataProvider);
			$gridHandler->addAction($dataProvider->getAddFileAction($request));
		}

		// Test whether the tar binary is available for the export to work, if so, add 'download all' grid action
		$tarBinary = Config::getVar('cli', 'tar');
		if ($this->canDownloadAll() && !empty($tarBinary) && file_exists($tarBinary) && $gridHandler->hasGridDataElements($request)) {
			import('controllers.grid.files.fileList.linkAction.DownloadAllLinkAction');

			$monograph =& $this->getMonograph();
			$stageId = $this->getStageId();
			$linkParams = array('monographId' => $monograph->getId(), 'stageId' => $stageId);

			// Get the files to be downloaded.
			$files =& $gridHandler->getFilesToDownload($request);

			$gridHandler->addAction(new DownloadAllLinkAction($request, $linkParams, $files), GRID_ACTION_POSITION_BELOW);
		}

		// The file name column is common to all file grid types.
		$gridHandler->addColumn(new FileNameGridColumn($this->canViewNotes(), $this->getStageId()));

		// Set the no items row text
		$gridHandler->setEmptyRowText('grid.noFiles');
	}

	/**
	 * Implementation of the GridHandler::getRowInstance() method.
	 */
	function &getRowInstance() {
		$row = new SubmissionFilesGridRow($this->canDelete(), $this->canViewNotes(), $this->getStageId());
		return $row;
	}
}

?>
