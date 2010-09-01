<?php

/**
 * @file controllers/listbuilder/users/StageParticipantListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding contributors to a chapter
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class StageParticipantListbuilderHandler extends ListbuilderHandler {
	/**
	 * Constructor
	 */
	function StageParticipantListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetch', 'addItem', 'deleteItems'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
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
		$userGroupId = $request->getUserVar('userGroupId');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroup =& $userGroupDao->getById($userGroupId);

		// Basic configuration
		$this->setSourceTitle('common.name');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT); // Free text input
		$this->setListTitle('submission.submit.currentParticipants');

		$this->loadList($request);
		$this->loadPossibleItemList($request);

		$this->addColumn(new GridColumn('item', 'common.name'));
	}


	//
	// Public methods
	//
	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$monographId = $request->getUserVar('monographId');
		$userGroupId = $request->getUserVar('userGroupId');

		$monograph =& $monographDao->getMonograph($monographId);

		// Retrieve the participants associated with the current group, monograph, and stage
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoffs =& $signoffDao->getAllBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, null, $monograph->getCurrentStageId(), $userGroupId);
		$items = array();
		while ($item =& $signoffs->next()) {
			$id = $item->getId();
			$userId = $item->getUserId();
			$user =& $userDao->getUser($userId);
			$items[$id] = array('item' => $user->getFullName());
			unset($item);
		}
		$this->setData($items);
	}

	/* Get possible items to populate drop-down list with */
	function getPossibleItemList() {
		return $this->possibleItems;
	}

	/* Load possible items to populate drop-down list with */
	function loadPossibleItemList(&$request) {
		$monographId = $request->getUserVar('monographId');
		$userGroupId = $request->getUserVar('userGroupId');

		// Retrieve all users that belong to the current user group
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$users =& $userGroupDao->getUsersById($userGroupId);

		$itemList = array();
		while($item =& $users->next()) {
			$id = $item->getId();
			$itemList[] = $this->_buildListItemHTML($id, $item->getFullName());
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
		$userGroupId = $request->getUserVar('userGroupId');

		$additionalVars = array('itemId' => $userGroupId,
			'addUrl' => $router->url($request, array(), null, 'addItem', null, array('monographId' => $monographId, 'userGroupId' => $userGroupId)),
			'deleteUrl' => $router->url($request, array(), null, 'deleteItems', null, array('monographId' => $monographId, 'userGroupId' => $userGroupId))
		);

		return parent::fetch(&$args, &$request, $additionalVars);
    }

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		parent::setupTemplate();

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION));
	}

	//
	// Public AJAX-accessible functions
	//

	/*
	 * Handle adding an item to the list
	 */
	function addItem(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$userGroupId = $request->getUserVar('userGroupId');

		$rowId = "selectList-" . $this->getId();
		$userId = (int) $args[$rowId];

		if(!isset($userId)) {
			$json = new JSON('false');
			return $json->getString();
		} else {
			$signoffDao =& DAORegistry::getDAO('SignoffDAO');
			$userDao =& DAORegistry::getDAO('UserDAO');
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monograph =& $monographDao->getMonograph($monographId);

			// Make sure the item doesn't already exist
			if(isset($monograph) && $signoffDao->signoffExists('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, $userId, $monograph->getCurrentStageId(), $userGroupId)) {
				$json = new JSON('false', Locale::translate('common.listbuilder.itemExists'));
				return $json->getString();
			}

			$signoffDao->build('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, $userId, $monograph->getCurrentStageId(), $userGroupId);
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
	}


	/*
	 * Handle deleting items from the list
	 */
	function deleteItems(&$args, &$request) {
		array_shift($args); array_shift($args); // Remove the monograph and user group IDs from the argument array; All we need are the signoff IDs
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		foreach($args as $item) {
			$signoffDao->deleteObjectById($item);
		}

		$json = new JSON('true');
		return $json->getString();
	}
}
?>
