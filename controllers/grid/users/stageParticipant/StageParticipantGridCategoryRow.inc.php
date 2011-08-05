<?php

/**
 * @file controllers/grid/users/stageParticipant/StageParticipantGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantGridCategoryRow
 * @ingroup controllers_grid_users_stageParticipant
 *
 * @brief Stage participant grid category row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

// Link actions
import('lib.pkp.classes.linkAction.request.AjaxModal');

class StageParticipantGridCategoryRow extends GridCategoryRow {
	/** @var $_monograph Monograph **/
	var $_monograph;

	/** @var $_stageId int */
	var $_stageId;

	/**
	 * Constructor
	 */
	function StageParticipantGridCategoryRow(&$monograph, $stageId) {
		$this->_monograph =& $monograph;
		$this->_stageId = $stageId;
		parent::GridCategoryRow();
	}

	//
	// Overridden methods from GridCategoryRow
	//
	/**
	 * @see GridCategoryRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		$userGroup =& $this->getData();
		$monograph =& $this->getMonograph();

		$router =& $request->getRouter();
		$this->addAction(new LinkAction(
			'editStageParticipantList',
			new AjaxModal(
				$router->url(
					$request, null, null,
					'editStageParticipantList', null,
					array(
						'monographId' => $monograph->getId(),
						'stageId' => $this->getStageId(),
						'userGroupId' => $userGroup->getId()
					)
				),
				$userGroup->getLocalizedName()
			),
			$userGroup->getLocalizedName()
		));
	}

	//
	// Private methods
	//
	/**
	 * Get the monograph for this row (already authorized)
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the stage ID for this grid.
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}
}

?>
