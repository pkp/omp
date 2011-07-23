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
		$copyeditedFileId = $signoff->getFileId();

		$user =& $request->getUser();

		if (!empty($rowId) && is_numeric($rowId)) {
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			import('controllers.api.file.linkAction.DeleteFileLinkAction');

			// Actions
			$router =& $request->getRouter();

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			$this->addAction(new LinkAction(
				'deleteSignoff',
				new RemoteActionConfirmationModal(
					__('common.confirmDelete'), null,
					$router->url(
						$request, null, null, 'deleteSignoff',
						null, array(
							'signoffId' => $rowId,
							'fileId' => $copyeditedFileId
						)
					)
				),
				__('grid.copyediting.deleteSignoff'),
				'delete'
			 ));

			if ($copyeditedFileId) {
				$copyeditedFile =& $submissionFileDao->getLatestRevision($copyeditedFileId);
				import('controllers.informationCenter.linkAction.FileInfoCenterLinkAction');
				$this->addAction(new FileInfoCenterLinkAction($request, $monographFile));

				$this->addAction(new DeleteFileLinkAction($request, $copyeditedFile, 'grid.copyediting.deleteCopyeditorResponse'));
			}

			// If there is no file uploaded, allow the user to upload if it is their signoff (i.e. their copyediting assignment)
			if (!$copyeditedFileId && $signoff->getUserId() == $user->getId()) {
				$this->addAction(new LinkAction(
					'addCopyeditedFile',
					new AjaxModal($router->url(
						$request, null, null, 'addCopyeditedFile',
						null, array(
							'signoffId' => $rowId,
							'monographId' => $monographId
						)
					)),
					__('submission.addFile'),
					'add'
				));
			}
		}
	}
}

?>
