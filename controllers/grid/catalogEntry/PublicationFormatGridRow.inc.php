<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridRow.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridRow
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid row requests.
 */

import('lib.pkp.controllers.grid.files.SubmissionFilesGridRow');
import('lib.pkp.classes.controllers.grid.files.FilesGridCapabilities');

class PublicationFormatGridRow extends SubmissionFilesGridRow {
	/** @var boolean */
	protected $_canManage;

	/**
	 * Constructor
	 * @param $canManage boolean
	 */
	function __construct($canManage) {
		$this->_canManage = $canManage;

		$capabilities = FILE_GRID_ADD|FILE_GRID_DELETE|FILE_GRID_MANAGE|FILE_GRID_EDIT|FILE_GRID_VIEW_NOTES;
		if (!$this->_canManage) {
			$capabilities = FILE_GRID_VIEW_NOTES;
		}

		parent::__construct(
			new FilesGridCapabilities(
				$capabilities
			),
			WORKFLOW_STAGE_ID_PRODUCTION
		);
	}


	//
	// Overridden template methods from GridRow
	//
	/**
	 * @copydoc SubmissionFilesGridRow::initialize()
	 */
	function initialize($request, $template = 'controllers/grid/gridRow.tpl') {
		parent::initialize($request, $template);
		$submissionFileData =& $this->getData();
		$submissionFile =& $submissionFileData['submissionFile']; /* @var $submissionFile SubmissionFile */
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$router = $request->getRouter();
		$mimetype = $submissionFile->getData('mimetype');
		if (in_array($mimetype, array('application/xml', 'text/html'))) {
			$this->addAction(new LinkAction(
				'dependentFiles',
				new AjaxModal(
					$router->url($request, null, null, 'dependentFiles', null, array_merge(
						$this->getRequestArgs(),
						array(
							'submissionFileId' => $submissionFile->getId(),
							'isGridDisabled' => !$this->_canManage
						)
					)),
					__('submission.dependentFiles'),
					'modal_edit'
				),
				__('submission.dependentFiles'),
				'edit'
			));
		}
	}
}


