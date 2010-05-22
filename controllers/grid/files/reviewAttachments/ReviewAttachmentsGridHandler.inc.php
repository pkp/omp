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

// import validation classes
import('classes.handler.validation.HandlerValidatorPress');
import('lib.pkp.classes.handler.validation.HandlerValidatorRoles');

class ReviewAttachmentsGridHandler extends GridHandler {
	/** the FileType for this grid */
	var $fileType;

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

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addFile', 'editFile', 'saveFile', 'deleteFile', 'returnFileRow', 'downloadFile'));
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
		$reviewId = $request->getUserVar('reviewId');
		$monographId = $request->getUserVar('monographId');
		$this->setId('reviewAttachments');
		$this->setTitle('email.attachments');
		$this->setReadOnly($request->getUserVar('readOnly')?true:false);

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

		// FIXME: should validate so that only editors can use this option.
		if ( !$reviewId && $monographId ) {
			$monographFiles =& $monographFileDao->getByMonographId($monographId, MonographFileManager::typeToPath(MONOGRAPH_FILE_REVIEW));
		} else {
			$monographFiles =& $monographFileDao->getMonographFilesByAssocId($reviewId, MONOGRAPH_FILE_REVIEW);
		}
		$this->setData($monographFiles);

		// Add grid-level actions
		if ( !$this->getReadOnly() ) {
			$router =& $request->getRouter();
			$this->addAction(
				new GridAction(
					'addFile',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_APPEND,
					$router->url($request, null, null, 'addFile', null, array('reviewId' => $reviewId)),
					'grid.action.addItem'
				)
			);
		}

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
		$this->addCheck(new HandlerValidatorRoles($this, false, 'Insufficient privileges!', null, array(ROLE_ID_REVIEWER, ROLE_ID_EDITOR)));

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

		import('controllers.grid.files.reviewAttachments.form.ReviewAttachmentsForm');
		$reviewAttachmentsForm = new ReviewAttachmentsForm($reviewId, $fileId, $this->getId());

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
		$reviewId = $request->getUserVar('reviewId');

		import('controllers.grid.files.reviewAttachments.form.ReviewAttachmentsForm');
		$reviewAttachmentsForm = new ReviewAttachmentsForm($reviewId, null, $this->getId());
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