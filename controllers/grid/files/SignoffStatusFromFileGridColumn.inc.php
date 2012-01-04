<?php

/**
 * @file controllers/grid/files/SignoffStatusFromFileGridColumn.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffStatusFromFileGridColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a grid column that displays the signoff status
 *  of a file.
 */

import('controllers.grid.files.BaseSignoffStatusColumn');

class SignoffStatusFromFileGridColumn extends BaseSignoffStatusColumn {
	/* @var string */
	var $_symbolic;

	/**
	 * Constructor
	 * @param $userGroup UserGroup The user
	 *  group to be represented in this column.
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $requestArgs array Parameters for cell actions.
	 */
	function SignoffStatusFromFileGridColumn($id, $title, $titleTranslated, $symbolic, $userIds, $requestArgs, $flags = array()) {
		$this->_symbolic = $symbolic;
		parent::BaseSignoffStatusColumn(
			$id,
			$title,
			$titleTranslated,
			$userIds,
			$requestArgs,
			$flags
		);
	}

	//
	// Setters and Getters
	//
	function getSymbolic() {
		return $this->_symbolic;
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
		if (in_array($status, array('accepted', 'new')) && $this->hasFlag('myUserGroup')) {
			// Retrieve the submission file.
			$monographFile =& $this->getMonographFile($row);

			// Assemble the request arguments for the signoff action.
			$actionArgs = $this->getRequestArgs();
			$actionArgs['fileId'] = $monographFile->getFileId();

			// Instantiate the signoff action.
			$router =& $request->getRouter();
			import('lib.pkp.classes.linkAction.request.AjaxAction');
			$signoffAction = new LinkAction(
				'fileSignoff',
				new AjaxAction(
					$router->url(
						$request, null, null, 'signOffFile',
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
	// Protected helper methods
	//
	/**
	 * Get the monograph file from the row.
	 * @param $row GridRow
	 * @return MonographFile
	 */
	function &getMonographFile($row) {
		$submissionFileData =& $row->getData();
		assert(isset($submissionFileData['submissionFile']));
		$monographFile =& $submissionFileData['submissionFile']; /* @var $monographFile MonographFile */
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

		$userIds = $this->getUserIds();
		if (in_array($monographFile->getUploaderUserId(), $userIds)) {
			return 'uploaded';

		} else {
			// The current user has to sign off the file
			$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
			$viewsDao =& DAORegistry::getDAO('ViewsDAO'); /* @var $viewsDao ViewsDAO */
			$lastViewed = false;
			foreach ($userIds as $userId) {
				$signoffs =& $signoffDao->getAllBySymbolic(
					$this->getSymbolic(),
					ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileId(),
					$userId
				);

				// Check if any of the signoffs signed off.
				while($signoff =& $signoffs->next()) {
								if ($signoff->getDateCompleted()) {
						return 'completed';
							}
					unset($signoff);
				}

				if (!$lastViewed) {
					// Find out whether someone in the user group already downloaded
									// (=viewed) the file.
					// no users means a blank column (should not happen).

					$lastViewed = $viewsDao->getLastViewDate(
						ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileIdAndRevision(),
						$userId
					);
				}
			}
			// At least one user viewed the file
			if($lastViewed) {
				return 'accepted';
			}

			// No views means a white square.
			return 'new';
		}
	}
}

?>
