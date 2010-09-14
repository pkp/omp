<?php

/**
 * @file controllers/grid/files/reviewAttachments/EditorReviewAttachmentsGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorReviewAttachmentsGridHandler
 * @ingroup controllers_grid_files_reviewAttachments
 *
 * @brief Handle review attachment grid requests (editor's perspective)
 */

import('controllers.grid.files.reviewAttachments.ReviewAttachmentsGridRow');
import('controllers.grid.files.reviewAttachments.ReviewAttachmentsGridHandler');

class EditorReviewAttachmentsGridHandler extends ReviewAttachmentsGridHandler {
	/** Boolean flag if grid is selectable **/
	var $_isSelectable;

	/**
	 * Constructor
	 */
	function EditorReviewAttachmentsGridHandler() {
		parent::ReviewAttachmentsGridHandler();
		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER), array());
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'addFile', 'editFile', 'saveFile', 'deleteFile', 'returnFileRow', 'downloadFile'));
	}

	//
	// Getters/Setters
	//
	/**
	 * Set the selectable flag
	 * @param $isSelectable bool
	 */
	function setIsSelectable($isSelectable) {
		$this->_isSelectable = $isSelectable;
	}

	/**
	 * Get the selectable flag
	 * @return bool
	 */
	function getIsSelectable() {
		return $this->_isSelectable;
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_INTERNAL_REVIEW));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Set the Is Selectable boolean flag
		$isSelectable = (boolean)$request->getUserVar('isSelectable');
		$this->setIsSelectable($isSelectable);
		// Columns
		import('controllers.grid.files.reviewAttachments.ReviewAttachmentsGridCellProvider');
		$cellProvider =& new ReviewAttachmentsGridCellProvider();
		if ($this->getIsSelectable()) {
			$this->addColumn(new GridColumn('select',
				'common.select',
				null,
				'controllers/grid/files/reviewAttachments/gridRowSelectInput.tpl',
				$cellProvider)
			);
		}
		parent::initialize($request);
		$monographId = (int) $request->getUserVar('monographId');

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($monographId, MonographFileManager::typeToPath(MONOGRAPH_FILE_REVIEW));
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] = $monographFile;
		}
		$this->setData($rowData);

		if($isSelectable) {
			// Load a different grid template
			$this->setTemplate('controllers/grid/files/reviewFiles/grid.tpl');

			// There are no pre-selected files--Assign an empty array to gridRowSelectInput.tpl to avoid warnings
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('selectedFileIds', array());
		}

		// Add grid-level actions
		if (!$this->getReadOnly()) {
			$router =& $request->getRouter();
			$this->addAction(
				new LinkAction(
					'addFile',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_APPEND,
					$router->url($request, null, null, 'addFile', null, array('monographId' => $monographId)),
					'grid.reviewAttachments.add'
				)
			);
		}


	}

	//
	// Overridden methods from GridHandler
	//
	/**
	* Get the row handler - override the default row handler
	* @return ReviewAttachmentsGridRow
	*/
	function &getRowInstance() {
		$row = new GridRow();
		return $row;
	}

	//
	// Public File Grid Actions
	//

	/**
	 * An action to add a new file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function editFile(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$fileId = $request->getUserVar('rowId');

		import('controllers.grid.files.reviewAttachments.form.EditorReviewAttachmentsForm');
		$reviewAttachmentsForm = new EditorReviewAttachmentsForm($monographId, $fileId, $this->getId());

		if ($reviewAttachmentsForm->isLocaleResubmit()) {
			$reviewAttachmentsForm->readInputData();
		} else {
			$reviewAttachmentsForm->initData($args, $request);
		}
		$json = new JSON('true', $reviewAttachmentsForm->fetch($request));
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
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.files.reviewAttachments.form.EditorReviewAttachmentsForm');
		$reviewAttachmentsForm = new EditorReviewAttachmentsForm($monographId, null, $this->getId());
		$reviewAttachmentsForm->readInputData();

		if ($reviewAttachmentsForm->validate()) {
			$fileId = $reviewAttachmentsForm->execute($args, $request);

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

}