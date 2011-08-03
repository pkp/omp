<?php

/**
 * @file controllers/grid/users/stageParticipant/StageParticipantGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
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
	/**
	 * Constructor
	 */
	function StageParticipantGridCategoryRow() {
		parent::GridCategoryRow();
	}

	//
	// Overridden methods from GridCategoryRow
	//
	/**
	 * @see GridCategoryRow::getCategoryLabel()
	 */
	function getCategoryLabel() {
		$userGroup =& $this->getData();
		return $userGroup->getLocalizedName();
	}
}

?>
