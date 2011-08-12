<?php

/**
 * @file controllers/grid/files/SignoffOnSignoffGridColumn.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffOnSignoffGridColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a grid column that displays the signoff status of a file.
 *
 */

import('controllers.grid.files.BaseSignoffStatusColumn');

class SignoffOnSignoffGridColumn extends BaseSignoffStatusColumn {
	/**
	 * Constructor
	 * @param $title The title for the column
	 * @param $requestArgs array Parameters f5or cell actions.
	 */
	function SignoffOnSignoffGridColumn($title = null, $userIds = array(), $requestArgs, $flags = array()) {
		parent::BaseSignoffStatusColumn('editor', $title, null, $userIds, $requestArgs, $flags);
	}

	//
	// Overridden methods from GridColumn
	//
	/**
	 * @see GridColumn::getCellActions()
	 */
	function getCellActions($request, $row) {
		$status = $this->_getSignoffStatus($row);
		$actions = array();
		if ($status == 'accepted' || $status == 'new') {
			// Retrieve the submission file.
			$signoff =& $row->getData();

			// Assemble the request arguments for the signoff action.
			$actionArgs = $this->getRequestArgs();
			$actionArgs['signoffId'] = $signoff->getId();

			// Instantiate the signoff action.
			$router =& $request->getRouter();
			import('lib.pkp.classes.linkAction.request.AjaxAction');
			$signoffAction = new LinkAction(
				'fileSignoff',
				new AjaxAction(
					$router->url(
						$request, null, null, 'signOffsignOff',
						null, $actionArgs
					)
				),
				__('common.signoff'),
				'task '.$status
			);
			$actions[] = $signoffAction;
		}
		return $actions;
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
		$signoffInQuestion =& $row->getData();

		// No status until the signoff is completed
		if (!$signoffInQuestion->getDateCompleted()) {
			return '';
		}

		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoff SignoffDAO */
		$viewsDao =& DAORegistry::getDAO('ViewsDAO'); /* @var $viewsDao ViewsDAO */
		$viewed = false;
		$fileIdAndRevision = $signoffInQuestion->getFileId() . '-' . $signoffInQuestion->getFileRevision();
		foreach ($this->getUserIds() as $userId) {
			$signoff =& $signoffDao->getBySymbolic('SIGNOFF_SIGNOFF',
													ASSOC_TYPE_SIGNOFF, $signoffInQuestion->getId(),
													$userId);
			// somebody in one of the user groups signed off on the file
			if ($signoff && $signoff->getDateCompleted()) {
				return 'completed';
			} else {
				// Find out whether someone in the user group already downloaded
				// (=viewed) the file.
				$viewed = $viewed ||
					$viewsDao->getLastViewDate(ASSOC_TYPE_MONOGRAPH_FILE, $fileIdAndRevision, $userId);
			}
			unset($signoff);
		}

		// Any view means we can mark green.
		if($viewed) {
			return 'accepted';
		} else {
			return 'new';
		}
	}
}


?>