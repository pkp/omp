<?php

/**
 * @filecontrollers/grid/files/editorReviewFileSelection/EditorReviewFileSelectionGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileGridHandler
 * @ingroup controllers_grid_file
 *
 * @brief Handle the editor review file selection grid (selects which files to send to review)
 */

import('controllers.grid.files.editorReviewFileSelection.EditorReviewFileSelectionGridRow');
import('lib.pkp.classes.controllers.grid.GridHandler');

// import validation classes
import('classes.handler.validation.HandlerValidatorPress');
import('lib.pkp.classes.handler.validation.HandlerValidatorRoles');

class EditorReviewFileSelectionGridHandler extends GridHandler {
	/** the FileType for this grid */
	var $fileType;

	/**
	 * Constructor
	 */
	function EditorReviewFileSelectionGridHandler() {
		parent::GridHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addFile', 'editFile', 'saveFile', 'deleteFile'));
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic grid configuration
		$monographId = $request->getUserVar('monographId');
		$this->setId('editorReviewFileSelection');
		$this->setTitle('common.file');
		$this->setTemplate('controllers/grid/files/editorReviewFileSelection/grid.tpl');

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($monographId);
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$monographFileId = $monographFile->getFileId();
			$rowData[$monographFileId] = $monographFile;
		}

		$this->setData($rowData);

		// Columns
		import('controllers.grid.files.editorReviewFileSelection.EditorReviewFileSelectionGridCellProvider');
		$cellProvider =& new EditorReviewFileSelectionGridCellProvider();
		$this->addColumn(new GridColumn('select',
			'common.select',
			null,
			'controllers/grid/files/editorReviewFileSelection/selectRow.tpl',
			$cellProvider)
		);

		$this->addColumn(new GridColumn('name',
			'common.file',
			null,
			'controllers/grid/gridCellInSpan.tpl',
			$cellProvider)
		);

		$this->addColumn(new GridColumn('type',
			'common.type',
			null,
			'controllers/grid/gridCellInSpan.tpl',
			$cellProvider)
		);

		// Set the already selected elements of the grid
		$reviewType = (int) $request->getUserVar('reviewType');
		$round = (int) $request->getUserVar('round');

		$reviewAssignmentDAO =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$selectedFiles =& $reviewAssignmentDAO->getReviewFilesByRound($monographId);
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('selectedFileIds', array_keys($selectedFiles[$reviewType][$round]));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return EditorReviewFileSelectionGridRow
	 */
	function &getRowInstance() {
		$row = new EditorReviewFileSelectionGridRow();
		return $row;
	}

	/**
	 * Validate that the user is the assigned author for the monograph
	 * Raises a fatal error if validation fails.
	 * @param $requiredContexts array
	 * @param $request PKPRequest
	 * @return boolean
	 */
	function validate($requiredContexts, $request) {
		// Retrieve the request context
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$user =& $request->getUser();

		// 1) Ensure we're in a press
		$this->addCheck(new HandlerValidatorPress($this, false, 'No press in context!'));

		// 2) Only Authors may access
		$this->addCheck(new HandlerValidatorRoles($this, false, 'Insufficient privileges!', null, array(ROLE_ID_EDITOR)));

		// Execute standard checks
		if (!parent::validate($requiredContexts, $request)) return false;

		return true;

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
		$fileId = $request->getUserVar('rowId');
		$reviewId = $request->getUserVar('reviewId');

		import('controllers.grid.files.editorReviewFileSelection.form.EditorReviewFileSelectionForm');
		$editorReviewFileSelectionForm = new EditorReviewFileSelectionForm($reviewId, $fileId, $this->getId());

		if ($editorReviewFileSelectionForm->isLocaleResubmit()) {
			$editorReviewFileSelectionForm->readInputData();
		} else {
			$editorReviewFileSelectionForm->initData($args, $request);
		}
		$json = new JSON('true', $editorReviewFileSelectionForm->fetch($request));
		return $json->getString();
	}

	/**
	 * upload a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function saveFile(&$args, &$request) {
		$router =& $request->getRouter();
		$reviewId = $request->getUserVar('reviewId');

		import('controllers.grid.files.editorReviewFileSelection.form.EditorReviewFileSelectionForm');
		$editorReviewFileSelectionForm = new EditorReviewFileSelectionForm($reviewId, null, $this->getId());
		$editorReviewFileSelectionForm->readInputData();

		if ($editorReviewFileSelectionForm->validate()) {
			$fileId = $editorReviewFileSelectionForm->execute($args, $request);

			$additionalAttributes = array(
				'deleteUrl' => $router->url($request, null, null, 'deleteFile', null, array('rowId' => $fileId)),
				'saveUrl' => $router->url($request, null, null, 'returnFileRow', null, array('rowId' => $fileId))
			);
			$json = new JSON('true', Locale::translate('submission.uploadSuccessful'), 'false', $fileId, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('common.uploadFailed'));
		}

		return '<textarea>' . $json->getString() . '</textarea>';
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

}