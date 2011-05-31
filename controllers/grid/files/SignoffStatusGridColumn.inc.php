<?php

/**
 * @file controllers/grid/files/SignoffStatusGridColumn.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffStatusGridColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a grid column that displays the signoff status
 *  of a file.
 */

import('controllers.grid.files.UserGroupColumn');

class SignoffStatusGridColumn extends UserGroupColumn {

	/** @var integer */
	var $_stageId;

	/** @var array */
	var $_requestArgs;


	/**
	 * Constructor
	 * @param $signoffUserGroup UserGroup The user
	 *  group to be represented in this column.
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $requestArgs array Parameters for cell actions.
	 */
	function SignoffStatusGridColumn(&$signoffUserGroup, $stageId, $requestArgs) {
		$this->_stageId = $stageId;
		$this->_requestArgs = $requestArgs;
		parent::UserGroupColumn($signoffUserGroup, 'signoff');
	}


	//
	// Setters and Getters
	//
	/**
	 * Get the workflow stage id.
	 * @return integer
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the cell action request parameters.
	 * @return array
	 */
	function getRequestArgs() {
		return $this->_requestArgs;
	}


	//
	// Overridden methods from UserGroupColumn
	//
	/**
	 * @see UserGroupColiumn::getTemplateVarsFromRowColumn()
	 */
	function getTemplateVarsFromRow($row) {
		return array('status' => $this->_getSignoffStatus($row));
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
		if (in_array($status, array('accepted', 'new'))) {
			// Retrieve the submission file.
			$monographFile =& $this->getMonographFile($row);

			// Retrieve the user group.
			$userGroup =& $this->getUserGroup();

			// Assemble the request arguments for the signoff action.
			$actionArgs = $this->getRequestArgs();
			$actionArgs['fileId'] = $monographFile->getFileId();
			$actionArgs['userGroupId'] = $userGroup->getId();

			// Instantiate the signoff action.
			$router =& $request->getRouter();
			import('lib.pkp.classes.linkAction.request.AjaxAction');
			$signoffAction = new LinkAction(
				'fileSignoff',
				new AjaxAction(
					$router->url(
						$request, null, null, 'signOffFiles',
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
		static $statusCache = array();
		$rowId = $row->getId();

		if (!isset($statusCache[$rowId])) {
			$statusCache[$rowId] = '';

			// Retrieve the current user.
			$sessionManager =& SessionManager::getManager();
			$session =& $sessionManager->getUserSession();
			$user =& $session->getUser();
			$userId = $user->getId();

			// Retrieve the signoffs.
			$submissionFileData =& $row->getData();
			assert(isset($submissionFileData['signoffs']));
			$fileSignoffs = $submissionFileData['signoffs'];

			// Retrieve the user group & file
			$signoffColumnUserGroup =& $this->getUserGroup();
			$monographFile =& $this->getMonographFile($row);

			if ($signoffColumnUserGroup->getId() == $monographFile->getUserGroupId()) {
				// The uploader of the current file belongs to
				// the user group displayed in this column.
				$statusCache[$rowId] = 'uploaded';

			} else if (isset($fileSignoffs[$userId])) {
				// The current user has to sign off the file
				$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
				$viewsDao =& DAORegistry::getDAO('ViewsDAO'); /* @var $viewsDao ViewsDAO */
				foreach($fileSignoffs[$userId] as $signoffUserGroupId => $signoffUserGroup) {
					if ($signoffUserGroupId == $signoffColumnUserGroup->getId()) {
						// Find out whether the editor already signed
						// off the file.
						$signoff =& $signoffDao->getBySymbolic(
							'SIGNOFF_STAGE_FILE',
							ASSOC_TYPE_MONOGRAPH, $monographFile->getSubmissionId(),
							$userId, $this->getStageId(), $signoffUserGroupId,
							$monographFile->getFileId(), $monographFile->getRevision()
						);
						if ($signoff) {
							$status = 'completed';
						} else {
							// Find out whether the editor already downloaded
							// (=viewed) the file.
							$lastViewed = $viewsDao->getLastViewDate(
								ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileIdAndRevision(),
								$user->getId()
							);

							if($lastViewed) {
								$status = 'accepted';
							} else {
								$status = 'new';
							}
						}
						break;
					}
				}

				$statusCache[$rowId] = $status;
			}
		}

		return $statusCache[$row->getId()];
	}
}
