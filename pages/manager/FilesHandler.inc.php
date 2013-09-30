<?php

/**
 * @file pages/manager/FilesHandler.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FilesHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for files browser functions.
 */


import('pages.manager.ManagerHandler');

class FilesHandler extends ManagerHandler {
	/**
	 * Constructor
	 */
	function FilesHandler() {
		parent::ManagerHandler();
		$this->addRoleAssignment(ROLE_ID_MANAGER,
				array('fileDelete', 'fileMakeDir', 'files', 'fileUpload'));
	}

	/**
	 * Display the files associated with a press.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function files($args, $request) {
		$this->setupTemplate($request, true);

		import('lib.pkp.classes.file.PrivateFileManager');
		$privateFileManager = new PrivateFileManager();

		$this->_parseDirArg($args, $currentDir, $parentDir);
		$currentPath = $this->_getRealFilesDir($request, $currentDir);

		if (@is_file($currentPath)) {
			$privateFileManager->downloadFile($currentPath, null, (boolean) $request->getUserVar('download'));
		} else {
			$files = array();
			if ($dh = @opendir($currentPath)) {
				while (($file = readdir($dh)) !== false) {
					if ($file != '.' && $file != '..') {
						$filePath = $currentPath . '/'. $file;
						$isDir = is_dir($filePath);
						$info = array(
							'name' => $file,
							'isDir' => $isDir,
							'mimetype' => $isDir ? '' : $this->fileMimeType($filePath),
							'mtime' => filemtime($filePath),
							'size' => $isDir ? '' : $fileManager->getNiceFileSize(filesize($filePath)),
						);
						$files[$file] = $info;
					}
				}
				closedir($dh);
			}
			ksort($files);
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('files', $files);
			$templateMgr->assign('currentDir', $currentDir);
			$templateMgr->assign('parentDir', $parentDir);
			$templateMgr->display('manager/files/index.tpl');
		}
	}

	/**
	 * Upload a new file.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fileUpload($args, $request) {
		$this->_parseDirArg($args, $currentDir, $parentDir);
		$currentPath = $this->_getRealFilesDir($request, $currentDir);

		import('lib.pkp.classes.file.PrivateFileManager');
		$privateFileManager = new PrivateFileManager();
		if ($privateFileManager->uploadedFileExists('file')) {
			$destPath = $currentPath . '/' . $this->cleanFileName($privateFileManager->getUploadedFileName('file'));
			@$privateFileManager->uploadFile('file', $destPath);
		}

		$request->redirect(null, null, 'files', explode('/', $currentDir));

	}

	/**
	 * Create a new directory
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fileMakeDir($args, $request) {
		$this->_parseDirArg($args, $currentDir, $parentDir);

		if ($dirName = $request->getUserVar('dirName')) {
			$currentPath = $this->_getRealFilesDir($request, $currentDir);
			$newDir = $currentPath . '/' . $this->cleanFileName($dirName);

			import('lib.pkp.classes.file.PrivateFileManager');
			$privateFileManager = new PrivateFileManager();
			@$privateFileManager->mkdir($newDir);
		}

		$request->redirect(null, null, 'files', explode('/', $currentDir));
	}

	/**
	 * Delete a file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fileDelete($args, $request) {
		$this->_parseDirArg($args, $currentDir, $parentDir);
		$currentPath = $this->_getRealFilesDir($request, $currentDir);

		import('lib.pkp.classes.file.PrivateFileManager');
		$privateFileManager = new PrivateFileManager();

		if (@is_file($currentPath)) {
			$privateFileManager->deleteFile($currentPath);
		} else {
			// TODO Use recursive delete (rmtree) instead?
			@$privateFileManager->rmdir($currentPath);
		}

		$request->redirect(null, null, 'files', explode('/', $parentDir));
	}


	//
	// Helper functions
	// FIXME Move some of these functions into common class (FileManager?)
	//

	function _parseDirArg($args, &$currentDir, &$parentDir) {
		$pathArray = array_filter($args, array('FilesHandler', 'fileNameFilter'));
		$currentDir = join($pathArray, '/');
		array_pop($pathArray);
		$parentDir = join($pathArray, '/');
	}

	/**
	 * Get the real files directory for a specified directory.
	 * @param $request PKPRequest
	 * @param $currentDir string
	 * @return string
	 */
	function _getRealFilesDir($request, $currentDir) {
		$press = $request->getPress();
		import('lib.pkp.classes.file.ContextFileManager');
		$pressFileManager = new PrivateFileManager($press->getId());

		return $privateFileManager->getBasePath() . $currentDir;
	}

	function fileNameFilter($var) {
		return (!empty($var) && $var != '..' && $var != '.');
	}

	function cleanFileName($var) {
		$var = String::regexp_replace('/[^\w\-\.]/', '', $var);
		if (!$this->fileNameFilter($var)) {
			$var = time() . '';
		}
		return $var;
	}

	function fileMimeType($filePath) {
		return String::mime_content_type($filePath);
	}

}
?>
