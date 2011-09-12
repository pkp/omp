<?php

/**
 * @file controllers/listbuilder/files/FilesListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FilesListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Base class for selecting files to add a user to.
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class FilesListbuilderHandler extends ListbuilderHandler {

	/** File stage **/
	var $_fileStage;

	/**
	 * Constructor
	 */
	function FilesListbuilderHandler($fileStage) {
		parent::ListbuilderHandler();

		$this->_fileStage = $fileStage;

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			array('fetch', 'fetchRow', 'fetchOptions')
		);
	}

	/**
	 * Get file stage.
	 * @return int
	 */
	function getFileStage() {
		return $this->_fileStage;
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments, $stageId) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic configuration
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('files');

		// Add the file column
		$itemColumn = new ListbuilderGridColumn($this, 'name', 'common.name');
		import('controllers.listbuilder.files.FileListbuilderGridCellProvider');
		$itemColumn->setCellProvider(new FileListbuilderGridCellProvider());
		$this->addColumn($itemColumn);
	}


	//
	// Public methods
	//

	/**
	 * Load possible items to populate drop-down list with.
	 * @param $monographFiles Array Submission files of this monograph.
	 * @return Array
	 */
	function getOptions($monographFiles) {
		$itemList = array();
		foreach ($monographFiles as $monographFile) {
			$itemList[$monographFile->getFileId()] = $monographFile->getFileLabel();
		}
		return array($itemList);
	}


	//
	// Overridden template methods
	//
	/**
	 * @see GridHandler::getRequestArgs
	 */
	function getRequestArgs() {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$args = parent::getRequestArgs();
		$args['monographId'] = $monograph->getId();
		return $args;
	}

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		parent::setupTemplate();

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION));
	}

	/**
	 * @see GridHandler::getRowDataElement
	 * Get the data element that corresponds to the current request
	 * Allow for a blank $rowId for when creating a not-yet-persisted row
	 */
	function getRowDataElement(&$request, $rowId) {
		// fallback on the parent if a rowId is found
		if ( !empty($rowId) ) {
			return parent::getRowDataElement($request, $rowId);
		}

		// Otherwise return from the newRowId
		$newRowId = $this->getNewRowId($request);
		$fileId = (int) $newRowId['name'];
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		import('classes.monograph.MonographFile'); // Bring in const
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), $this->getFileStage());
		foreach ($monographFiles as $monographFile) {
			if ($monographFile->getFileId() == $fileId) {
				return $monographFile;
			}
		}
		return null;
	}
}

?>
