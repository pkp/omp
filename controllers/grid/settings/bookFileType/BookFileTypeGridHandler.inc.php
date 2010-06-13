<?php

/**
 * @file controllers/grid/settings/bookFileType/BookFileTypeGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookFileTypeGridHandler
 * @ingroup controllers_grid_bookFileType
 *
 * @brief Handle Book File Type grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');
import('controllers.grid.settings.bookFileType.BookFileTypeGridRow');

class BookFileTypeGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function BookFileTypeGridHandler() {
		parent::GridHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addBookFileType', 'editBookFileType', 'updateBookFileType', 'deleteBookFileType', 'restoreBookFileTypes'));
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

		// Load language components
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_GRID));

		// Basic grid configuration
		$this->setTitle('settings.setup.bookFileTypes');

		$press =& $request->getPress();

		// Elements to be displayed in the grid
		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$bookFileTypes =& $bookFileTypeDao->getEnabledByPressId($press->getId());
		$this->setData($bookFileTypes);

		// Add grid-level actions
		$router =& $request->getRouter();
		$actionArgs = array('gridId' => $this->getId());
		$this->addAction(
			new LinkAction(
				'addBookFileType',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addBookFileType', null, $actionArgs),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);
		$this->addAction(
			new LinkAction(
				'restoreBookFileTypes',
				LINK_ACTION_MODE_CONFIRM,
				LINK_ACTION_TYPE_NOTHING,
				$router->url($request, null, null, 'restoreBookFileTypes', null, $actionArgs),
				'grid.action.restoreDefaults'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		// Columns
		$cellProvider = new DataObjectGridCellProvider();
		$cellProvider->setLocale(Locale::getLocale());
		$this->addColumn(new GridColumn('name',
										'common.name',
										null,
										'controllers/grid/gridCell.tpl',
										$cellProvider));
		$this->addColumn(new GridColumn('designation',
										'common.designation',
										null,
										'controllers/grid/gridCell.tpl',
										$cellProvider));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return BookFileTypeGridRow
	 */
	function &getRowInstance() {
		$row = new BookFileTypeGridRow();
		return $row;
	}

	//
	// Public Book File Type Grid Actions
	//
	/**
	 * An action to add a new Book File Type
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addBookFileType(&$args, &$request) {
		// Calling editBookFileType with an empty row id will add a new Book File Type.
		return $this->editBookFileType($args, $request);
	}

	/**
	 * An action to edit a Book File Type
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editBookFileType(&$args, &$request) {
		$bookFileTypeId = isset($args['bookFileTypeId']) ? $args['bookFileTypeId'] : null;

		//FIXME: add validation here?
		$this->setupTemplate();

		import('controllers.grid.settings.bookFileType.form.BookFileTypeForm');
		$bookFileTypeForm = new BookFileTypeForm($bookFileTypeId);

		if ($bookFileTypeForm->isLocaleResubmit()) {
			$bookFileTypeForm->readInputData();
		} else {
			$bookFileTypeForm->initData($args, $request);
		}

		$json = new JSON('true', $bookFileTypeForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a Book File Type
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateBookFileType(&$args, &$request) {
		$bookFileTypeId = Request::getUserVar('rowId');

		//FIXME: add validation here?
		$press =& $request->getPress();

		import('controllers.grid.settings.bookFileType.form.BookFileTypeForm');
		$bookFileTypeForm = new BookFileTypeForm($bookFileTypeId);
		$bookFileTypeForm->readInputData();

		$router =& $request->getRouter();

		if ($bookFileTypeForm->validate()) {
			$bookFileTypeForm->execute($args, $request);

			// prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());

			$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
			$bookFileType =& $bookFileTypeDao->getById($bookFileTypeForm->bookFileTypeId, $press->getId());

			$row->setData($bookFileType);
			$row->setId($bookFileTypeForm->bookFileTypeId);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}

	/**
	 * Delete a Book File Type.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteBookFileType(&$args, &$request) {
		// Identify the Book File Type to be deleted
		$bookFileType =& $this->_getBookFileTypeFromArgs($args);

		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$result = $bookFileTypeDao->deleteObject($bookFileType);

		if ($result) {
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('settings.setup.errorDeletingItem'));
		}
		return $json->getString();
	}

	/**
	 * Restore the default Book File Type settings for the press.
	 * All default settings that were available when the press instance was created will be restored.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function restoreBookFileTypes(&$args, &$request) {
		$press =& $request->getPress();

		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$bookFileTypeDao->restoreByPressId($press->getId());

		$this->setData();
	}

	//
	// Private helper function
	//
	/**
	* This will retrieve a Book File Type object from the
	* grids data source based on the request arguments.
	* If no Book File Type can be found then this will raise
	* a fatal error.
	* @param $args array
	* @return BookFileType
	*/
	function &_getBookFileTypeFromArgs(&$args) {
		// Identify the Book File Type Id and retrieve the
		// corresponding element from the grid's data source.
		if (!isset($args['bookFileTypeId'])) {
			fatalError('Missing Book File Type Id!');
		} else {
			$bookFileType =& $this->getRowDataElement($args['bookFileTypeId']);
			if (is_null($bookFileType)) fatalError('Invalid Book File Type Id!');
		}
		return $bookFileType;
	}
}