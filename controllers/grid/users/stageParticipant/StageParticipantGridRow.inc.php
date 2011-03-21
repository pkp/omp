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

	/** @var integer */
	var $_monographId;

	/** @var integer */
	var $_stageId;


	/**
	 * Constructor
	 * @param $monographId integer
	 * @param $stageId integer
	 */
	function StageParticipantGridRow($monographId, $stageId) {
		$this->_monographId = (int)$monographId;
		$this->_stageId = (int)$stageId;

		parent::GridRow();
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the monograph id.
	 * @return integer
	 */
	function getMonographId() {
		return $this->_monographId;
	}

	/**
	 * Get the workflow stage id.
	 * @return integer
	 */
	function getStageId() {
		return $this->_stageId;
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
			// Only add row actions if this is an existing row.
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $this->getMonographId(),
				'stageId' => $this->getStageId(),
				'signoffId' => $rowId
			);
			import('lib.pkp.classes.linkAction.request.ConfirmationModal');
			// FIXME: Not all roles should see this action. Bug #5975.
			$this->addAction(
				new LinkAction(
					'remove',
					new ConfirmationModal(
						__('common.confirmDelete'),
						__('common.delete'),
						$router->url($request, null, null, 'deleteStageParticipant', null, $actionArgs)
					),
					__('grid.action.remove'),
					'delete'
				)
			);

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}

?>
