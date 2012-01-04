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
			array('downloadFile', 'viewFile')
		);
	}


	//
	// Implement methods from PKPHandler
	//
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpMonographFileAccessPolicy');
		$this->addPolicy(new OmpMonographFileAccessPolicy($request, $args, $roleAssignments, MONOGRAPH_FILE_ACCESS_READ));

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
		MonographFileManager::downloadFile($monographFile->getMonographId(), $monographFile->getFileId(), $monographFile->getRevision());
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
		MonographFileManager::viewFile($monographFile->getMonographId(), $monographFile->getFileId(), $monographFile->getRevision());
	}
}

?>
