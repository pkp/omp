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

	/** @var Monograph */
	var $_monograph;

	/** @var integer */
	var $_fileStage;


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
		// FIXME: Requires file level authorization policy.
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$stageId = (int)$request->getUserVar('stageId');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		parent::initialize($request, $args);

		// Configure the wizard with the authorized monograph and file stage.
		$this->_monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$fileStage = (int)$request->getUserVar('fileStage');
		assert(is_numeric($fileStage) && $fileStage > 0);
		$this->_fileStage = $fileStage;
	}


	//
	// Getters and Setters
	//
	/**
	 * The monograph to which we upload files.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the authorized workflow stage.
	 * @return integer One of the WORKFLOW_STAGE_ID_* constants.
	 */
	function getStageId() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
	}

	/**
	 * Get the workflow stage file storage that
	 * we upload files to. One of the MONOGRAPH_FILE_*
	 * constants.
	 * @return integer
	 */
	function getFileStage() {
		return $this->_fileStage;
	}
}
?>
