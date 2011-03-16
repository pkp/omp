<?php

/**
 * @file controllers/listbuilder/users/StageParticipantListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding participants to a stage.
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class StageParticipantListbuilderHandler extends ListbuilderHandler {

	/**
	 * Constructor
	 */
	function StageParticipantListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetch', 'addItem', 'deleteItems'));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}

	/**
	 * Get the authorized workflow stage.
	 * @return integer
	 */
	function getStageId() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
	}


	//
	// Implement protected template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = (int)$request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic configuration.
		$this->setSourceTitle('common.name');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setListTitle('submission.submit.currentParticipants');

		// Load possible items.
		$this->_loadPossibleItemList($request);

		// Configure listbuilder column.
		$this->addColumn(new GridColumn('item', 'common.name'));
	}

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION));
	}


	//
	// Implement protected template methods from GridHandler.
	//
	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		// Retrieve the participants associated with the
		// current group, monograph, and stage.
		$userGroupId = (int)$request->getUserVar('userGroupId');
		$monograph =& $this->getMonograph();
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoffFactory =& $signoffDao->getAllBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monograph->getId(), null, $this->getStageId(), $userGroupId);

		// Retrieve the corresponding users for display.
		$userDao =& DAORegistry::getDAO('UserDAO');
		$signoffs = array();
		while ($signoff =& $signoffFactory->next()) {
			$user =& $userDao->getUser($signoff->getUserId());
			$signoffs[(int)$signoff->getId()] = array('item' => $user->getFullName(), 'userId' => $user->getId());
			unset($signoff);
		}
		return $signoffs;
	}


	//
	// Implement protected template methods from ListbuilderHandler.
	//
	/**
	 * @see ListbuilderHandler::fetch()
	 */
	function fetch($args, &$request) {
		$userGroupId = (int)$request->getUserVar('userGroupId');
		$monograph =& $this->getMonograph();
		$params = array(
			'monographId' => $monograph->getId(),
			'userGroupId' => $userGroupId,
			'stageId' => $this->getStageId()
		);
		$router =& $request->getRouter();
		$additionalVars = array(
			'addUrl' => $router->url($request, array(), null, 'addItem', null, $params),
			'deleteUrl' => $router->url($request, array(), null, 'deleteItems', null, $params)
		);

		return parent::fetch($args, &$request, $additionalVars);
	}

	/**
	 * @see ListbuilderHandler::addItem()
	 */
	function addItem($args, &$request) {
		// Make sure the item doesn't already exist.
		$userId = (int)$this->getAddedItemId($args);
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();
		$userGroupId = (int)$request->getUserVar('userGroupId');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		if(
			$signoffDao->signoffExists(
				'SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId,
				$userId, $this->getStageId(), $userGroupId
			)
		) {
			// Warn the user that the item has been added before.
			$json = new JSON(false, __('common.listbuilder.itemExists'));
			return $json->getString();
		}

		// Create a new signoff.
		$signoff =& $signoffDao->build('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, $userId, $this->getStageId(), $userGroupId);

		// Return JSON with formatted HTML to insert into list.
		// FIXME: This is duplicate code! See #6193.
		$row =& $this->getRowInstance();
		$row->setGridId($this->getId());
		$row->setId($signoff->getId());
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $userDao->getUser($userId);
		$rowData = array('item' => $user->getFullName());
		$row->setData($rowData);
		$row->initialize($request);

		$json = new JSON(true , $this->_renderRowInternally($request, $row));
		return $json->getString();
	}

	/**
	 * @see ListbuilderHandler::deleteItems()
	 */
	function deleteItems($args, &$request) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoffIds = $this->getDeletedItemIds($request, $args, 2);
		foreach($signoffIds as $signoffId) {
			$signoffDao->deleteObjectById((int)$signoffId);
		}
		$json = new JSON(true);
		return $json->getString();
	}


	//
	// Private helper methods
	//
	/**
	 * Load possible items to populate drop-down list with.
	 * @param $request Request
	 */
	function _loadPossibleItemList(&$request) {
		// Retrieve the existing sign offs so that we can leave out
		// users that have already been selected.
		$items = $this->getGridDataElements($request);

		// Retrieve all users that belong to the current user group
		// FIXME #6000: If user group is in the series editor role, only allow it
		// if the series editor is assigned to the monograph's series.
		$userGroupId = (int)$request->getUserVar('userGroupId');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$userFactory =& $userGroupDao->getUsersById($userGroupId);
		$users = array();
		while($user =& $userFactory->next()) {
			$users[(int)$user->getId()] = $user->getFullName();
			unset($user);
		}
		$this->setPossibleItemList($users);
	}
}
?>
