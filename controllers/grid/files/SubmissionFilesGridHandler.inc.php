<?php

/**
 * @file controllers/grid/files/SubmissionFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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

// Define the grid capabilities.
define('FILE_GRID_ADD', 0x01);
define('FILE_GRID_DOWNLOAD_ALL', 0x02);
define('FILE_GRID_DELETE', 0x04);

class SubmissionFilesGridHandler extends GridHandler {
	/** @var GridDataProvider */
	var $_dataProvider;

	/** @var boolean */
	var $_canAdd;

	/** @var boolean */
	var $_canDownloadAll;

	/** @var boolean */
	var $_canDelete;


	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SubmissionFilesGridHandler(&$dataProvider, $capabilities) {
		$this->_dataProvider =& $dataProvider;
		$this->_canAdd = (boolean)($capabilities & FILE_GRID_ADD);
		$this->_canDownloadAll = (boolean)($capabilities & FILE_GRID_DOWNLOAD_ALL);
		$this->_canDelete = (boolean)($capabilities & FILE_GRID_DELETE);

		parent::GridHandler();
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the data provider.
	 * @return FilesGridDataProvider
	 */
	function &getDataProvider() {
		return $this->_dataProvider;
	}

	/**
	 * Get a grid request parameter
	 * from the data provider.
	 * @param $key string The name of the parameter to retrieve.
	 * @return mixed
	 */
	function getRequestArg($key) {
		$dataProvider =& $this->getDataProvider();
		$requestArgs =& $dataProvider->getRequestArgs();
		assert(isset($requestArgs[$key]));
		return $requestArgs[$key];
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

	/**
	 * Does this grid allow the addition of files
	 * or revisions?
	 * @return boolean
	 */
	function canAdd() {
		return $this->_canAdd;
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


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		$dataProvider =& $this->getDataProvider();
		$this->addPolicy($dataProvider->getAuthorizationPolicy(&$request, &$args, $roleAssignments));
		$success = parent::authorize($request, $args, $roleAssignments);
		if ($success === true) {
			$dataProvider->setAuthorizedContext($this->getAuthorizedContext());
		}
		return $success;
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load translations.
		Locale::requireComponents(
			array(
				LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION,
				LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_COMMON,
				LOCALE_COMPONENT_APPLICATION_COMMON
			)
		);

		// Populate the grid with data.
		$dataProvider =& $this->getDataProvider();
		$this->setGridDataElements($dataProvider->getRowData());

		// The file name column is common to all file grid types.
		$this->addColumn(new FileNameGridColumn());
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 */
	function &getRowInstance() {
		$row = new SubmissionFilesGridRow($this->canDelete());
		return $row;
	}

	/**
	 * @see GridHandler::fetchGrid()
	 */
	function fetchGrid($args, &$request, $fetchParams = array()) {
		// Add grid-level actions.
		$dataProvider =& $this->getDataProvider();
		if($this->canAdd()) {
			$this->addAction($dataProvider->getAddFileAction($request));
		}

		// Retrieve and add the the request parameters required to
		// specify the contents of this grid.
		$fetchParams = array_merge($fetchParams, $dataProvider->getRequestArgs());

		// Test whether the tar binary is available for the export to work, if so, add 'download all' grid action
		$tarBinary = Config::getVar('cli', 'tar');
		if ($this->canDownloadAll() && !empty($tarBinary) && file_exists($tarBinary) && $this->hasGridDataElements($request)) {
			import('controllers.grid.files.fileList.linkAction.DownloadAllLinkAction');
			$this->addAction(new DownloadAllLinkAction($request, $fetchParams));
		}

		// Fetch the grid.
		return parent::fetchGrid($args, $request, $fetchParams);
	}


	//
	// Public handler methods
	//
	/**
	 * Download all of the monograph files as one compressed file
	 * @param $args array
	 * @param $request Request
	 */
	function downloadAllFiles($args, &$request) {
		$monograph =& $this->getMonograph();
		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFilesArchive($monograph->getId(), $this->getGridDataElements($request));
	}
}