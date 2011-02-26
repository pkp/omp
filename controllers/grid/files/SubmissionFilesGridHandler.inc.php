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

class SubmissionFilesGridHandler extends GridHandler {
	/** @var GridDataProvider */
	var $_dataProvider;

	/** @var boolean */
	var $_canAdd;

	/** @var boolean */
	var $_canDownloadAll;


	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $canAdd boolean whether the grid will contain
	 *  an "add file" button.
	 * @param $canDownloadAll boolean whether the user can download
	 *  all files in the grid as a compressed file
	 */
	function SubmissionFilesGridHandler(&$dataProvider, $canAdd = true, $canDownloadAll = false) {
		$this->_dataProvider =& $dataProvider;
		$this->_canAdd = (boolean)$canAdd;
		$this->_canDownloadAll = (boolean)$canDownloadAll;

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
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION,
				LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON));

		// Populate the grid with data.
		$dataProvider =& $this->getDataProvider();
		$this->setData($dataProvider->getRowData());

		// Add grid-level actions.
		if($this->canAdd()) {
			$this->addAction($dataProvider->getAddFileAction($request));
		}

		// Test whether the tar binary is available for the export to work, if so, add 'download all' grid action
		$tarBinary = Config::getVar('cli', 'tar');
		if ($this->canDownloadAll() && !empty($tarBinary) && file_exists($tarBinary) && $this->hasData()) {
			$this->addAction($dataProvider->getDownloadAllAction($request));
		}

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
		$row = new SubmissionFilesGridRow();
		return $row;
	}

	/**
	 * @see GridHandler::fetchGrid()
	 */
	function fetchGrid($args, &$request) {
		// Retrieve and add the the request parameters required to
		// specify the contents of this grid.
		$dataProvider =& $this->getDataProvider();
		return parent::fetchGrid($args, $request, $dataProvider->getRequestArgs());
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
		MonographFileManager::downloadFilesArchive($monograph->getId(), $this->getData());
	}
}