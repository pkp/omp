<?php
/**
 * @defgroup controllers_api_file
 */

/**
 * @file controllers/api/file/FileApiHandler.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileApiHandler
 * @ingroup controllers_api_file
 *
 * @brief Class defining an AJAX API for supplying file information.
 */

// Import the base handler.
import('lib.pkp.controllers.api.file.PKPFileApiHandler');
import('classes.file.MonographFileManager');
import('classes.security.authorization.OmpMonographFileAccessPolicy');

class FileApiHandler extends PKPFileApiHandler {

	/**
	 * Constructor.
	 */
	function FileApiHandler() {
		parent::PKPFileApiHandler();
	}

	/**
	 * return the application specific file manager.
	 * @param $contextId int the context for this manager.
	 * @param $submissionId int the submission id.
	 * @return MonographFileManager
	 */
	function _getFileManager($contextId, $submissionId) {
		return new MonographFileManager($contextId, $submissionId);
	}

	/**
	 * return the application specific file access policy.
	 * @param $request PKPRequest
	 * @param $args
	 * @param $roleAssignments array
	 * @param $fileIdAndRevision array optional
	 * @return OmpSubmissionAccessPolicy
	 */
	function _getAccessPolicy($request, $args, $roleAssignments, $fileIdAndRevision = null) {
		return new OmpMonographFileAccessPolicy($request, $args, $roleAssignments, SUBMISSION_FILE_ACCESS_READ);
	}

	/**
	 * record a file view.
	 * Must be overridden in subclases.
	 * @param $submissionFile MonographFile the file to record.
	 */
	function recordView($submissionFile) {
		MonographFileManager::recordView($submissionFile);
	}
}

?>
