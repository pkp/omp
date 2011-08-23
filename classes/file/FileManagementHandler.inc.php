<?php
/**
 * @file classes/file/FileManagementHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileManagementHandler
 * @ingroup classes_file
 *
 * @brief An abstract class that handles common functionality
 *  for controllers that manage files.
 */

// Import the base Handler.
import('classes.handler.Handler');

class FileManagementHandler extends Handler {
	/**
	 * Constructor
	 */
	function FileManagementHandler() {
		parent::Handler();
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		// Allow both reviewers (if in review) and press roles.
		import('classes.security.authorization.OmpReviewStageAccessPolicy');
		$this->addPolicy(new OmpReviewStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $request->getUserVar('stageId')));

		return parent::authorize($request, $args, $roleAssignments);
	}


	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		parent::initialize($request, $args);
	}


	//
	// Getters and Setters
	//
	/**
	 * The monograph to which we upload files.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}


	/**
	 * Get the authorized workflow stage.
	 * @return integer One of the WORKFLOW_STAGE_ID_* constants.
	 */
	function getStageId() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
	}
}

?>
