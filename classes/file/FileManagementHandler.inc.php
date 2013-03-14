<?php
/**
 * @file classes/file/FileManagementHandler.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileManagementHandler
 * @ingroup classes_file
 *
 * @brief An abstract class that handles common functionality
 *  for controllers that manage files.
 */

// Import the base Handler.
import('lib.pkp.classes.file.PKPFileManagementHandler');

class FileManagementHandler extends PKPFileManagementHandler {
	/**
	 * Constructor
	 */
	function FileManagementHandler() {
		parent::PKPFileManagementHandler();
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		// Allow both reviewers (if in review) and press roles.
		import('classes.security.authorization.OmpReviewStageAccessPolicy');
		$this->addPolicy(new OmpReviewStageAccessPolicy($request, $args, $roleAssignments, 'submissionId', $request->getUserVar('stageId')), true);

		return parent::authorize($request, $args, $roleAssignments);
	}
}

?>
