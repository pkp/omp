<?php

/**
 * @file controllers/grid/files/SignoffStatusFromSignoffGridColumn.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffStatusFromSignoffGridColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a grid column that displays the signoff status of a file.
 *
 */

import('controllers/grid/files/BaseSignoffStatusColumn');

class SignoffStatusFromSignoffGridColumn extends BaseSignoffStatusColumn {
	/**
	 * Constructor
	 * @param $title The title for the column
	 * @param $requestArgs array Parameters f5or cell actions.
	 */
	function SignoffStatusFromSignoffGridColumn($title = null, $requestArgs, $flags = array()) {
		parent::BaseSignoffStatusColumn('auditor', $title, null, null, $requestArgs, $flags);
	}

	//
	// Overridden methods from UserGroupColumn
	//

	//
	// Overridden methods from GridColumn
	//
	/**
	 * @see GridColumn::getCellActions()
	 */
	function getCellActions($request, $row) {
		$status = $this->_getSignoffStatus($row);
		$actions = array();
		if (in_array($status, array('accepted', 'new'))) {
			// Retrieve the submission file.
			$monographFile =& $this->getMonographFile($row);

			// Retrieve the signoff
			$signoff =& $row->getData();

			// Action to signoff on a file -- Lets user interact with their own rows.;
			import('controllers.api.signoff.linkAction.AddSignoffFileLinkAction');
			$signoffAction = new AddSignoffFileLinkAction(
														$request, $monographFile->getMonographId(),
														$row->getStageId(), $signoff->getSymbolic(), $signoff->getId(),
														__('submission.upload.signoff'), null);
			// FIXME: not ideal
			$signoffAction->_image = 'task ' . $status;
			$signoffAction->_title = null;

			$actions[] = $signoffAction;
		}
		return $actions;
	}


	//
	// Protected helper methods
	//
	/**
	 * Get the monograph file from the row.
	 * @param $row GridRow
	 * @return MonographFile
	 */
	function &getMonographFile($row) {
		$signoff =& $row->getData();
		assert(is_a($signoff, 'SignOff'));
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId());
		assert(is_a($monographFile, 'MonographFile'));
		return $monographFile;
	}

	//
	// Private helper methods
	//
	/**
	 * Identify the signoff status of a row.
	 * @param $row GridRow
	 * @return string
	 */
	function _getSignoffStatus(&$row) {
		$monographFile =& $this->getMonographFile($row);
		$signoff =& $row->getData();

		if ($signoff->getDateCompleted()) {
			return 'completed';
		}

		// The current user has to sign off the file
		$viewsDao =& DAORegistry::getDAO('ViewsDAO'); /* @var $viewsDao ViewsDAO */

		// Find out whether someone in the user group already downloaded
		// (=viewed) the file.
		// no users means a blank column (should not happen).
		$lastViewed = $viewsDao->getLastViewDate(
			ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileIdAndRevision(),
			$signoff->getUserId()
		);

		// Any view means we can stop looping and mark green.
		if($lastViewed) {
			return 'accepted';
		} else {
			return 'new';
		}
	}
}


?>