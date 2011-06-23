<?php

/**
 * @file controllers/listbuilder/files/CopyeditingFilesListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingFilesListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for selecting files to add a user to for copyediting
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class CopyeditingFilesListbuilderHandler extends ListbuilderHandler {
	/**
	 * Constructor
	 */
	function CopyeditingFilesListbuilderHandler() {
		parent::ListbuilderHandler();

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
				array('fetch', 'fetchRow', 'fetchOptions'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_EDITING));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic configuration
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT); // Multiselect
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
	 * Load possible items to populate drop-down list with
	 */
	function getOptions() {
		import('classes.monograph.MonographFile');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), MONOGRAPH_FILE_COPYEDIT);
		$itemList = array();
		foreach ($monographFiles as $monographFile) {
			$itemList[$monographFile->getFileId()] = $monographFile->getFileLabel();
			unset($monographFile);
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
	function &getRowDataElement(&$request, $rowId) {
		// fallback on the parent if a rowId is found
		if ( !empty($rowId) ) {
			return parent::getRowDataElement($request, $rowId);
		}

		// Otherwise return from the newRowId
		// FIXME Bug #6199
		$fileId = (int) $request->getUserVar('newRowId');
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFile =& $submissionFileDao->getLatestRevision($fileId);
		return $submissionFile;
	}
}

?>
