<?php

/**
 * @file controllers/grid/files/copyedit/CopyeditingFilesGridRow.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingFilesGridRow
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief Handle fair copy file grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class CopyeditingFilesGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function CopyeditingFilesGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		// Is this a new row or an existing row?
		$rowId = $this->getId();

		// Get the signoff (the row)
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($rowId);

		// Get the id of the original file (the category header)
		$monographFileId = $signoff->getAssocId();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFile =& $submissionFileDao->getLatestRevision($monographFileId);
		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
		$monographId = $monographFile->getMonographId();
		$monograph =& $monographDao->getMonograph($monographId);
		$copyeditedFileId = $signoff->getFileId();

		$user =& $request->getUser();

		if (!empty($rowId) && is_numeric($rowId)) {
			// Actions
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'signoffId' => $rowId,
				'monographId' => $monographId,
				'fileId' => $copyeditedFileId
			);

			if($copyeditedFileId) {
				$this->addAction(
					new LegacyLinkAction(
						'moreInfo',
						LINK_ACTION_MODE_MODAL,
						LINK_ACTION_TYPE_NOTHING,
						$router->url($request, null, 'informationCenter.FileInformationCenterHandler', 'viewInformationCenter', null, array('monographId' => $monographId, 'fileId' => $copyeditedFileId)),
						'grid.action.moreInformation',
						null,
						'more_info'
					)
				);

				$this->addAction(
					new LegacyLinkAction(
						'deleteFile',
						LINK_ACTION_MODE_CONFIRM,
						LINK_ACTION_TYPE_REPLACE,
						$router->url($request, null, null, 'deleteFile', null, $actionArgs),
						'grid.action.delete',
						null,
						'delete',
						Locale::translate('common.confirmDelete')
					)
				);
			} else {
				$this->addAction(
					new LegacyLinkAction(
						'deleteUser',
						LINK_ACTION_MODE_CONFIRM,
						LINK_ACTION_TYPE_REMOVE,
						$router->url($request, null, null, 'deleteUser', null, $actionArgs),
						'grid.action.delete',
						null,
						'delete',
						Locale::translate('common.confirmDelete')
					));
			}

			// If there is no file uploaded, allow the user to upload if it is their signoff (i.e. their copyediting assignment)
			if(!$copyeditedFileId && $signoff->getUserid() == $user->getId()) {
				$this->addAction(
					new LegacyLinkAction(
						'addCopyeditedFile',
						LINK_ACTION_MODE_MODAL,
						LINK_ACTION_TYPE_REPLACE,
						$router->url($request, null, null, 'addCopyeditedFile', null, $actionArgs),
						'submission.addFile',
						null,
						'add'
					)
				);
			}

			// If there is a file uploaded, allow the user to edit it if it is their signoff (i.e. their copyediting assignment)
			if($copyeditedFileId && $signoff->getUserid() == $user->getId()) {
				$this->addAction(
					new LegacyLinkAction(
						'editCopyeditedFile',
						LINK_ACTION_MODE_MODAL,
						LINK_ACTION_TYPE_REPLACE,
						$router->url($request, null, null, 'editCopyeditedFile', null, $actionArgs),
						'common.edit',
						null,
						'add'
					)
				);
			}

		}
	}
}
