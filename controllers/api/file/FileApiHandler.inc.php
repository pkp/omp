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
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR),
			array('downloadFile', 'downloadLibraryFile', 'viewFile', 'downloadAllFiles', 'recordDownload', 'enableLinkAction')
		);
	}


	//
	// Implement methods from PKPHandler
	//
	function authorize(&$request, &$args, $roleAssignments) {
		$monographFilesIds = $request->getUserVar('filesIdsAndRevisions');
		$libraryFileId = $request->getUserVar('libraryFileId');

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
		}else if (is_numeric($libraryFileId)) {
			import('classes.security.authorization.OmpPressAccessPolicy');
			$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		}else {
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
		$monographFileManager = new MonographFileManager($press->getId(), $monographFile->getMonographId());
		$monographFileManager->downloadFile($monographFile->getFileId(), $monographFile->getRevision());
	}

	/**
	 * Download a library file.
	 * @param $args array
	 * @param $request Request
	 */
	function downloadLibraryFile($args, &$request) {
		import('classes.file.LibraryFileManager');
		$press =& $request->getPress();
		$libraryFileManager = new LibraryFileManager($press->getId());
		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$libraryFile =& $libraryFileDao->getById($request->getUserVar('libraryFileId'));
		if ($libraryFile) {

			// If this file has a monograph ID, ensure that the current
			// user is assigned to that submission.
			if ($libraryFile->getMonographId()) {
				$user =& $request->getUser();
				$allowedAccess = false;
				$userStageAssignmentDao =& DAORegistry::getDAO('UserStageAssignmentDAO');
				$assignedUsers = $userStageAssignmentDao->getUsersBySubmissionAndStageId($libraryFile->getMonographId(), WORKFLOW_STAGE_ID_SUBMISSION);
				if (!$assignedUsers->wasEmpty()) {
					while ($assignedUser =& $assignedUsers->next()) {
						if ($assignedUser->getId()  == $user->getId()) {
							$allowedAccess = true;
							break;
						}
					}
				}
			} else {
				$allowedAccess = true; // this is a Press submission document, default to access policy.
			}

			if ($allowedAccess) {
				$filePath = $libraryFileManager->getBasePath() .  $libraryFile->getOriginalFileName();
				$libraryFileManager->downloadFile($filePath);
			} else {
				fatalError('Unauthorized access to library file.');
			}
		}
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
		$monographFileManager = new MonographFileManager($press->getId(), $monographFile->getMonographId());
		$monographFileManager->downloadFile($monographFile->getFileId(), $monographFile->getRevision(), true);
	}

	/**
	 * Download all passed files.
	 * @param $args array
	 * @param $request Request
	 */
	function downloadAllFiles($args, &$request) {
		// Retrieve the authorized objects.
		$monographFiles = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH_FILES);
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Find out the paths of all files in this grid.
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monograph->getPressId(), $monograph->getId());
		$filesDir = $monographFileManager->getBasePath();
		$filePaths = array();
		foreach ($monographFiles as $monographFile) {
			// Remove absolute path so the archive doesn't include it (otherwise all files are organized by absolute path)
			$filePaths[] = str_replace($filesDir, '', $monographFile->getFilePath());

			unset($monographFile);
		}

		import('lib.pkp.classes.file.FileArchive');
		$fileArchive = new FileArchive();
		$archivePath = $fileArchive->create($filePaths, $filesDir);

		if (file_exists($archivePath)) {
			$fileManager = new FileManager();
			if ($fileArchive->zipFunctional()) {
				$fileManager->downloadFile($archivePath, 'application/x-zip', false, 'files.zip');
			} else {
				$fileManager->downloadFile($archivePath, 'application/x-gtar', false, 'files.tar.gz');
			}
			$fileManager->deleteFile($archivePath);
		} else {
			fatalError('Creating archive with submission files failed!');
		}
	}

	/**
	 * Record file download and return js event to update grid rows.
	 * @param $args array
	 * @param $request Request
	 * @return string
	 */
	function recordDownload($args, &$request) {
		$monographFiles = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH_FILES);
		$fileId = null;

		foreach ($monographFiles as $monographFile) {
			import('classes.file.MonographFileManager');
			MonographFileManager::recordView($monographFile);
			$fileId = $monographFile->getFileId();
			unset($monographFile);
		}

		if (count($monographFiles) > 1) {
			$fileId = null;
		}

		return $this->enableLinkAction($args, $request);
	}

	/**
	 * Returns a data changd event to re-enable the link action.  Refactored out of
	 *  recordDownload since library files do not have downloads recorded and are in a
	 *  different context.
	 * @param $args aray
	 * @param $request Request
	 * @return string
	 */
	function enableLinkAction($args, &$request) {
		return DAO::getDataChangedEvent();
	}
}

?>
