<?php

/**
 * @file controllers/listbuilder/files/CopyeditingFilesListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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

		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_REVIEWER), array());
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
				array('fetch', 'addItem', 'deleteItems'));
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

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic configuration
		$this->setTitle('submission.files');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT); // Free text input
		$this->setListTitle('editor.monograph.copyediting.currentFiles');

		$this->loadList($request);
		$this->loadPossibleItemList($request);

		$this->addColumn(new GridColumn('item', 'common.name'));
	}


	//
	// Public methods
	//

	function loadList(&$request) {
		$data = array();
		$this->setData($data);
	}

	/* Get possible items to populate drop-down list with */
	function getPossibleItemList() {
		return $this->possibleItems;
	}

	/* Load possible items to populate drop-down list with */
	function loadPossibleItemList(&$request) {
		$monographId = $request->getUserVar('monographId');

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($monographId, MONOGRAPH_FILE_COPYEDIT);
		$itemList = array();
		foreach ($monographFiles as $item) {
			$id = $item->getFileId();
			$itemList[] = $this->_buildListItemHTML($id, $item->getLocalizedName());

			unset($item);
		}

		$this->possibleItems = $itemList;
	}

	//
	// Overridden template methods
	//
	/**
	 * Need to add additional data to the template via the fetch method
	 */
	function fetch(&$args, &$request) {
		$router =& $request->getRouter();

		$monographId = $request->getUserVar('monographId');
		$additionalVars = array('itemId' => $monographId,
			'addUrl' => $router->url($request, array(), null, 'addItem', null, array('monographId' => $monographId)),
			'deleteUrl' => $router->url($request, array(), null, 'deleteItems', null, array('monographId' => $monographId))
		);

		return parent::fetch(&$args, &$request, $additionalVars);
    }

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		parent::setupTemplate();

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION));
	}

	//
	// Public AJAX-accessible functions
	//


	/*
	 * Handle adding an item to the list
	 * NB: This and deleteItems do not change the system's state, but are only interface elements.
	 * 	State is changed only when the modal form is submitted
	 */
	function addItem(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		$rowId = "selectList-" . $this->getId();
		$fileId = (int) $args[$rowId];

		if(!isset($fileId)) {
			$json = new JSON('false');
			return $json->getString();
		} else {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFile =& $monographFileDao->getMonographFile($fileId);

			// Return JSON with formatted HTML to insert into list
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($fileId);
			$rowData = array('item' => $monographFile->getLocalizedName());
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
			return $json->getString();
		}
	}


	/*
	 * Handle deleting items from the list
	 */
	function deleteItems(&$args, &$request) {
		$json = new JSON('true');
		return $json->getString();
	}
}
?>
