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

	/** @var integer */
	var $_stageId;

	/** @var boolean */
	var $_canAdd;

	/** @var boolean */
	var $_canDownloadAll;

	/** @var boolean */
	var $_canDelete;


	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SubmissionFilesGridHandler(&$dataProvider, $stageId, $capabilities) {
		$this->_stageId = (int)$stageId;
		$this->_canAdd = (boolean)($capabilities & FILE_GRID_ADD);
		$this->_canDownloadAll = (boolean)($capabilities & FILE_GRID_DOWNLOAD_ALL);
		$this->_canDelete = (boolean)($capabilities & FILE_GRID_DELETE);

		parent::GridHandler($dataProvider);
	}


	//
	// Getters and Setters
	//
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

		// Add grid-level actions.
		$dataProvider =& $this->getDataProvider();
		if($this->canAdd()) {
			assert($dataProvider);
			$this->addAction($dataProvider->getAddFileAction($request));
		}

		// Test whether the tar binary is available for the export to work, if so, add 'download all' grid action
		$tarBinary = Config::getVar('cli', 'tar');
		if ($this->canDownloadAll() && !empty($tarBinary) && file_exists($tarBinary) && $this->hasGridDataElements($request)) {
			import('controllers.grid.files.fileList.linkAction.DownloadAllLinkAction');
			$this->addAction(new DownloadAllLinkAction($request, $this->getRequestArgs()));
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
		$row = new SubmissionFilesGridRow($this->canDelete());
		return $row;
	}


	//
	// Public handler methods
	//
	/**
	 * Download all of the monograph files as one compressed file.
	 * @param $args array
	 * @param $request Request
	 */
	function downloadAllFiles($args, &$request) {
		// Retrieve the monograph.
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();

		// Find out the paths of all files in this grid.
		import('classes.file.MonographFileManager');
		$filesDir = MonographFileManager::_getFilesDir($monographId);
		$filePaths = array();
		foreach ($this->getGridDataElements($request) as $submissionFileData) {
			$monographFile =& $submissionFileData['submissionFile']; /* @var $monographFile MonographFile */

			// Remove absolute path so the archive doesn't include it (otherwise all files are organized by absolute path)
			$filePath = str_replace($filesDir, '', $monographFile->getFilePath());

			// Add files to be archived to array
			$filePaths[] = escapeshellarg($filePath);

			unset($monographFile);
		}

		// Create a temporary file.
		$archivePath = tempnam('/tmp', 'sf-');

		// Create the archive and download the file.
		exec(
			Config::getVar('cli', 'tar') . ' -c -z ' .
			'-f ' . escapeshellarg($archivePath) . ' ' .
			'-C ' . escapeshellarg($filesDir) . ' ' .
			implode(' ', array_map('escapeshellarg', $filePaths))
		);

		if (file_exists($archivePath)) {
			FileManager::downloadFile($archivePath);
			FileManager::deleteFile($archivePath);
		} else {
			fatalError('Creating archive with submission files failed!');
		}
	}
}

?>
