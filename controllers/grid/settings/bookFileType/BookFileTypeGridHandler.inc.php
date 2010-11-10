<?php

/**
 * @file controllers/grid/settings/monographFileType/MonographFileTypeGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileTypeGridHandler
 * @ingroup controllers_grid_settings_monographFileType
 *
 * @brief Handle Monograph File Type grid requests.
 */

import('controllers.grid.settings.SetupGridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');
import('controllers.grid.settings.monographFileType.MonographFileTypeGridRow');

class MonographFileTypeGridHandler extends SetupGridHandler {
	/**
	 * Constructor
	 */
	function MonographFileTypeGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'addMonographFileType', 'editMonographFileType', 'updateMonographFileType',
				'deleteMonographFileType', 'restoreMonographFileTypes'));
	}


	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load language components
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_GRID));

		// Basic grid configuration
		$this->setTitle('manager.setup.monographFileTypes');

		$press =& $request->getPress();

		// Elements to be displayed in the grid
		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');
		$monographFileTypes =& $monographFileTypeDao->getEnabledByPressId($press->getId());
		$this->setData($monographFileTypes);

		// Add grid-level actions
		$router =& $request->getRouter();
		$actionArgs = array('gridId' => $this->getId());
		$this->addAction(
			new LinkAction(
				'addMonographFileType',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addMonographFileType', null, $actionArgs),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);
		$this->addAction(
			new LinkAction(
				'restoreMonographFileTypes',
				LINK_ACTION_MODE_CONFIRM,
				LINK_ACTION_TYPE_NOTHING,
				$router->url($request, null, null, 'restoreMonographFileTypes', null, $actionArgs),
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
	 * @return MonographFileTypeGridRow
	 */
	function &getRowInstance() {
		$row = new MonographFileTypeGridRow();
		return $row;
	}

	//
	// Public Monograph File Type Grid Actions
	//
	/**
	 * An action to add a new Monograph File Type
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addMonographFileType($args, &$request) {
		// Calling editMonographFileType with an empty row id will add a new Monograph File Type.
		return $this->editMonographFileType($args, $request);
	}

	/**
	 * An action to edit a Monograph File Type
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editMonographFileType($args, &$request) {
		$monographFileTypeId = isset($args['monographFileTypeId']) ? $args['monographFileTypeId'] : null;

		$this->setupTemplate();

		import('controllers.grid.settings.monographFileType.form.MonographFileTypeForm');
		$monographFileTypeForm = new MonographFileTypeForm($monographFileTypeId);

		if ($monographFileTypeForm->isLocaleResubmit()) {
			$monographFileTypeForm->readInputData();
		} else {
			$monographFileTypeForm->initData($args, $request);
		}

		$json = new JSON('true', $monographFileTypeForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a Monograph File Type
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateMonographFileType($args, &$request) {
		$monographFileTypeId = Request::getUserVar('rowId');
		$press =& $request->getPress();

		import('controllers.grid.settings.monographFileType.form.MonographFileTypeForm');
		$monographFileTypeForm = new MonographFileTypeForm($monographFileTypeId);
		$monographFileTypeForm->readInputData();

		$router =& $request->getRouter();

		if ($monographFileTypeForm->validate()) {
			$monographFileTypeForm->execute($args, $request);

			// prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());

			$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');
			$monographFileType =& $monographFileTypeDao->getById($monographFileTypeForm->monographFileTypeId, $press->getId());

			$row->setData($monographFileType);
			$row->setId($monographFileTypeForm->monographFileTypeId);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}

	/**
	 * Delete a Monograph File Type.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteMonographFileType($args, &$request) {
		// Identify the Monograph File Type to be deleted
		$monographFileType =& $this->_getMonographFileTypeFromArgs($args);

		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');
		$result = $monographFileTypeDao->deleteObject($monographFileType);

		if ($result) {
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('manager.setup.errorDeletingItem'));
		}
		return $json->getString();
	}

	/**
	 * Restore the default Monograph File Type settings for the press.
	 * All default settings that were available when the press instance was created will be restored.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function restoreMonographFileTypes($args, &$request) {
		$press =& $request->getPress();

		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');
		$monographFileTypeDao->restoreByPressId($press->getId());

		$this->setData();
	}

	//
	// Private helper function
	//
	/**
	* This will retrieve a Monograph File Type object from the
	* grids data source based on the request arguments.
	* If no Monograph File Type can be found then this will raise
	* a fatal error.
	* @param $args array
	* @return MonographFileType
	*/
	function &_getMonographFileTypeFromArgs($args) {
		// Identify the Monograph File Type Id and retrieve the
		// corresponding element from the grid's data source.
		if (!isset($args['monographFileTypeId'])) {
			fatalError('Missing Monograph File Type Id!');
		} else {
			$monographFileType =& $this->getRowDataElement($args['monographFileTypeId']);
			if (is_null($monographFileType)) fatalError('Invalid Monograph File Type Id!');
		}
		return $monographFileType;
	}
}