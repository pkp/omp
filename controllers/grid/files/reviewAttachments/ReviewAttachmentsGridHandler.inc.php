<?php

/**
 * @filecontrollers/grid/files/reviewAttachments/ReviewAttachmentsGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileGridHandler
 * @ingroup controllers_grid_file
 *
 * @brief Handle file grid requests.
 */

import('controllers.grid.files.reviewAttachments.ReviewAttachmentsGridRow');
import('lib.pkp.classes.controllers.grid.GridHandler');

class ReviewAttachmentsGridHandler extends GridHandler {
	/** boolean flag to make grid read only **/
	var $_readOnly;

	/**
	 * Constructor
	 */
	function ReviewAttachmentsGridHandler() {
		parent::GridHandler();
	}

	//
	// Getters/Setters
	//
	/**
	* Set the boolean flag to make grid read only
	* @param $readOnly bool
	*/
	function setReadOnly($readOnly) {
		$this->_readOnly = $readOnly;
	}

	/**
	 * Get the boolean flag to make grid read only
	 * @return bool
	 */
	function getReadOnly() {
		return $this->_readOnly;
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	* @see PKPHandler::authorize()
	*/
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_INTERNAL_REVIEW));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic grid configuration

		$this->setId('reviewAttachments');
		$this->setTitle('grid.reviewAttachments.title');
		$this->setReadOnly($request->getUserVar('readOnly')?true:false);

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		// Basic grid row configuration
		import('controllers.grid.files.reviewAttachments.ReviewAttachmentsGridCellProvider');
		$cellProvider =& new ReviewAttachmentsGridCellProvider();
		$this->addColumn(new GridColumn('files',
			'common.file',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider)
		);
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	* Get the row handler - override the default row handler
	* @return ReviewAttachmentsGridRow
	*/
	function &getRowInstance() {
		$row = new ReviewAttachmentsGridRow();
		return $row;
	}

	//
	// Public File Grid Actions
	//
	/**
	* An action to add a new file
	* @param $args array
	* @param $request PKPRequest
	*/
	function addFile(&$args, &$request) {
		// Calling editSponsor with an empty row id will add
		// a new sponsor.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('newFile', 'true');
		return $this->editFile($args, $request);
	}

	/**
	 * An action to add a new file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editFile(&$args, &$request) {
		assert(false); // Subclasses must implement
	}

	/**
	 * upload a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function saveFile(&$args, &$request) {
		assert(false); // Subclasses mustimplement
	}

	/**
	 * Return a grid row with for the submission grid
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function returnFileRow(&$args, &$request) {
		$fileId = isset($args['rowId']) ? $args['rowId'] : null;

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);

		if($monographFile) {
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($fileId);
			$row->setData($monographFile);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false', Locale::translate("There was an error with trying to fetch the file"));
		}

		return $json->getString();
	}

	/**
	 * Delete a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteFile(&$args, &$request) {
		$fileId = $request->getUserVar('rowId');

		if($fileId) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFileDao->deleteMonographFileById($fileId);

			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}
		return $json->getString();
	}

	/**
	 * Download the monograph file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function downloadFile(&$args, &$request) {
		//FIXME: add validation
		$monographId = $request->getUserVar('monographId');
		$fileId = $request->getUserVar('fileId');
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);
		$monographFileManager->downloadFile($fileId);
	}

}