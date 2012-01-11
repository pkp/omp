<?php
/**
 * @defgroup controllers_api_file
 */

/**
 * @file controllers/api/file/FileApiHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileApiHandler
 * @ingroup controllers_api_file
 *
 * @brief Class defining an AJAX API for supplying file information.
 */

// Import the base handler.
import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class FileApiHandler extends Handler {

	/**
	 * Constructor.
	 */
	function FileApiHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR),
			array('downloadFile', 'viewFile', 'downloadAllFiles')
		);
	}


	//
	// Implement methods from PKPHandler
	//
	function authorize(&$request, $args, $roleAssignments) {
		$monographFilesIds = $request->getUserVar('linkActionPostData');
		import('classes.security.authorization.OmpMonographFileAccessPolicy');

		if (is_string($monographFilesIds)) {
			$monographFilesIdsArray = explode(';', $monographFilesIds);
			array_pop($monographFilesIdsArray);
		}
		if (!empty($monographFilesIdsArray)) {
			$multipleMonographFileAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			foreach ($monographFilesIdsArray as $fileIdAndRevision) {
				$multipleMonographFileAccessPolicy->addPolicy(new OmpMonographFileAccessPolicy($request, $args, $roleAssignments, MONOGRAPH_FILE_ACCESS_READ, $fileIdAndRevision));
			}
			$this->addPolicy($multipleMonographFileAccessPolicy);
		} else {
			$this->addPolicy(new OmpMonographFileAccessPolicy($request, $args, $roleAssignments, MONOGRAPH_FILE_ACCESS_READ));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}

	//
	// Public handler methods
	//
	/**
	 * Download a file.
	 * @param $args array
	 * @param $request Request
	 */
	function downloadFile($args, &$request) {
		$monographFile =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH_FILE);
		assert($monographFile); // Should have been validated already
		import('classes.file.MonographFileManager');
		$press =& $request->getPress();
		$monographFileManager = new MonographFileManager($press->getId());
		$monographFileManager->downloadFile($monographFile->getMonographId(), $monographFile->getFileId(), $monographFile->getRevision());
	}

	/**
	 * View a file.
	 * @param $args array
	 * @param $request Request
	 */
	function viewFile($args, &$request) {
		$monographFile =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH_FILE);
		assert($monographFile); // Should have been validated already
		import('classes.file.MonographFileManager');
		$press =& $request->getPress();
		$monographFileManager = new MonographFileManager($press->getId());
		$monographFileManager->viewFile($monographFile->getMonographId(), $monographFile->getFileId(), $monographFile->getRevision());
	}

	/**
	* Download all passed files.
	* @param $args array
	* @param $request Request
	*/
	function downloadAllFiles($args, &$request) {
		// Retrieve the monograph.
		$monographFiles = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH_FILES);

		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$monographId = $monograph->getId();

		// Find out the paths of all files in this grid.
		import('classes.file.MonographFileManager');
		$filesDir = $monograph->getFilePath();
		$filePaths = array();
		foreach ($monographFiles as $monographFile) {
			// Remove absolute path so the archive doesn't include it (otherwise all files are organized by absolute path)
			$filePaths[] = str_replace($filesDir, '', $monographFile->getFilePath());

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
			$fileManager = new FileManager();
			$fileManager->downloadFile($archivePath, 'application/x-gtar', false, 'files.tar.gz');
			$fileManager->deleteFile($archivePath);
		} else {
			fatalError('Creating archive with submission files failed!');
		}
	}
}

?>
