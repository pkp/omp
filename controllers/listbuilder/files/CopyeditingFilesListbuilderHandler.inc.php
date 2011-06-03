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
				array('fetch', 'addItem', 'deleteItems', 'fetchRow', 'fetchOptions'));
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

		$this->loadList($request);

		$this->addColumn(new ListbuilderGridColumn($this, 'item', 'common.name'));
	}


	//
	// Public methods
	//

	
	/**
	 * Initialize the grid with the currently selected set of files.
	 */
	function loadList(&$request) {
		$data = array();
		$this->setGridDataElements($data);
	}

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
			$itemList[$monographFile->getFileId()] = $monographFile->getLocalizedName();
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
	 * Create a new data element from a request. This is used to format
	 * new rows prior to their insertion.
	 * @param $request PKPRequest
	 * @param $elementId int
	 * @return object
	 */
	function &getDataElementFromRequest(&$request, &$elementId) {
		import('lib.pkp.classes.controllers.listbuilder.ListbuilderMap');
		$options = $this->getOptions(true);

		$i = $request->getUserVar('item');
		if ($i == '') $i = null;
		else $i = (int) $i;
		assert($i === null || isset($options[0][$i]));

		$newItem = array(
			'item' => new ListbuilderMap($i, $i?$options[0][$i]:null)
		);

		$elementId = $request->getUserVar('rowId');
		return $newItem;
	}


	/**
	 * Handle adding an item to the list
	 * NB: This and deleteItems do not change the system's state, but are only interface elements.
	 * 	State is changed only when the modal form is submitted
	 */
	function addItem(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		$rowId = 'selectList-' . $this->getId();
		$fileId = (int) $args[$rowId];

		if(!isset($fileId)) {
			$json = new JSONMessage(false);
			return $json->getString();
		} else {
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$monographFile =& $submissionFileDao->getLatestRevision($fileId);

			// Return JSON with formatted HTML to insert into list
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($fileId);
			$rowData = array('item' => $monographFile->getLocalizedName());
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSONMessage(true, $this->_renderRowInternally($request, $row));
			return $json->getString();
		}
	}


	/**
	 * Handle deleting items from the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function deleteItems(&$args, &$request) {
		$json = new JSONMessage(true);
		return $json->getString();
	}
}

?>
