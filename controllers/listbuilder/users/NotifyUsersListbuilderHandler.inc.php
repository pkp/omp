<?php

/**
 * @file controllers/listbuilder/submit/NotifyUsersListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotifyUsersListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding users to a information center notification
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class NotifyUsersListbuilderHandler extends ListbuilderHandler {
	/**
	 * Constructor
	 */
	function NotifyUsersListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetch', 'addItem', 'deleteItems'));
	}

	//
	// Overridden template methods
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic configuration
		$this->setTitle('');
		$this->setSourceTitle('');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT); // Drop-down select
		$this->setListTitle('');

		$this->loadPossibleItemList($request);

		$this->addColumn(new GridColumn('item', 'common.name'));
	}

	/**
	 * Need to add additional data to the template via the fetch method
	 * @see Form::fetch()
	 */
	function fetch($args, &$request) {
		$router =& $request->getRouter();

		$monographId = $request->getUserVar('monographId');
		$additionalVars = array('monographId' => $monographId,
			'addUrl' =>  $router->url($request, array(), null, 'addItem', null, array('monographId' => $monographId)),
			'deleteUrl' => $router->url($request, array(), null, 'deleteItems', null, array('monographId' => $monographId))
		);

		return parent::fetch($args, &$request, $additionalVars);
	}


	//
	// Public AJAX-accessible functions
	//

	/*
	 * Handle adding an item to the list
	 */
	function addItem($args, &$request) {
		$rowId = "selectList-" . $this->getId();
		$userId = (int) $args[$rowId];

		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $userDao->getUser($userId);
		// Return JSON with formatted HTML to insert into list
		$row =& $this->getRowInstance();
		$row->setGridId($this->getId());
		$row->setId($userId);
		$rowData = array('item' => $user->getFullName());
		$row->setData($rowData);
		$row->initialize($request);

		$json = new JSON('true', $this->_renderRowInternally($request, $row));
		return $json->getString();

	}


	/*
	 * Handle deleting items from the list
	 */
	function deleteItems($args, &$request) {
		$json = new JSON('true');
		return $json->getString();
	}


	/**
	 * Override the listbuilder template (the regular one does not fit well in a tabbed modal)
	 * @return string
	 */
	function getTemplate() {
		$this->setTemplate('controllers/informationCenter/notifyListbuilder.tpl');
		return $this->_template;
	}

	/* Get possible items to populate drop-down list with */
	function getPossibleItemList() {
		return $this->possibleItems;
	}

	/* Load possible items to populate drop-down list with */
	function loadPossibleItemList(&$request) {
		$monographId = $request->getUserVar('monographId');

		// Retrieve all users associated with the monograph to populate the drop-down list with
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);
		$associatedUsers = $monograph->getAssociatedUserIds();
		$userDao =& DAORegistry::getDAO('UserDAO');

		$itemList = array();
		foreach($associatedUsers as $item) {
			$id = $item['id'];
			$user =& $userDao->getUser($item);
			$itemList[] = $this->_buildListItemHTML($id, $user->getFullName());
			unset($item);
		}

		$this->possibleItems = $itemList;
	}
}
?>
