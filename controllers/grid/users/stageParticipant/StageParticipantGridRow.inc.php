<?php

/**
 * @file controllers/grid/users/stageParticipant/StageParticipantGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantGridRow
 * @ingroup controllers_grid_users_stageParticipant
 *
 * @brief StageParticipant grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class StageParticipantGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function StageParticipantGridRow(&$monograph, $stageId) {
		$this->_monograph =& $monograph;
		$this->_stageId =& $stageId;

		parent::GridRow();
	}


	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$press =& $request->getPress();
			$userGroup =& $userGroupDao->getById($rowId, $press->getId());
			$this->setUserGroupId($userGroup->getId());

			// Only add row actions if this is an existing row.
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $this->getMonograph()->getId(),
				'stageId' => $this->_stageId,
				'userGroupId' => $this->getUserGroupId()
			);

			import('lib.pkp.classes.linkAction.request.AjaxModal');
			// FIXME: Not all roles should see this action. Bug #5975.
			$this->addAction(
				new LinkAction(
					'edit',
					new AjaxModal(
						$router->url($request, null, null, 'editStageParticipantList', null, $actionArgs),
						__('grid.user.edit'),
						'edit',
						true
						),
					__('grid.user.edit'),
					'edit'
				)
			);

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the monograph for this row (already authorized)
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the stage id for this row
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Set the user group id
	 * @param $userGroupId integer
	 */
	function setUserGroupId($userGroupId) {
		$this->_userGroupId = $userGroupId;
	}


	/**
	 * Get the user group id
	 * @return integer
	 */
	function getUserGroupId() {
		return $this->_userGroupId;
	}

	/**
	 * Get the grid request parameters.
	 * @see GridHandler::getRequestArgs()
	 * @return array
	 */
	function getRequestArgs() {
		return array('userGroupId' => $this->getUserGroupId());
	}
}

?>
