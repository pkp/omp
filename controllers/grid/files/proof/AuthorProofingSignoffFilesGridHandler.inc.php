<?php

/**
 * @file controllers/grid/files/proof/AuthorProofingSignoffFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorProofingSignoffFilesGridHandler
 * @ingroup controllers_grid_files_proof
 *
 * @brief Display the files the author has been asked to sign off for proofing.
 */

import('lib.pkp.classes.controllers.grid.CategoryGridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class AuthorProofingSignoffFilesGridHandler extends CategoryGridHandler {
	/**
	 * Constructor
	 */
	function AuthorProofingSignoffFilesGridHandler() {
		import("controllers.grid.files.proof.AuthorProofingSignoffFilesCategoryGridDataProvider");
		parent::CategoryGridHandler(new AuthorProofingSignoffFilesCategoryGridDataProvider());

		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR),
			array('fetchGrid', 'fetchRow')
		);
	}

	/**
	 * @see GridHandler::initialize($request, $args)
	 */
	function initialize($request, $args) {
		parent::initialize($request);

		$dataProvider =& $this->getDataProvider();
		$user =& $request->getUser();
		$dataProvider->setUserId($user->getId());

		AppLocale::requireComponents(
			LOCALE_COMPONENT_PKP_COMMON,
			LOCALE_COMPONENT_APPLICATION_COMMON,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_OMP_SUBMISSION
		);


		$addSignoffFileLinkAction = $dataProvider->getAddSignoffFile($request);
		if ($addSignoffFileLinkAction) {
			$this->addAction($addSignoffFileLinkAction);
		}

		// The file name column is common to all file grid types.
		import('controllers.grid.files.FileNameGridColumn');
		$this->addColumn(new FileNameGridColumn(null, WORKFLOW_STAGE_ID_PRODUCTION));

		import('controllers.grid.files.fileSignoff.AuthorSignoffFilesGridCellProvider');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$cellProvider = new AuthorSignoffFilesGridCellProvider($monograph, WORKFLOW_STAGE_ID_PRODUCTION);

		// Add a column to show whether the author uploaded a copyedited version of the file
		$this->addColumn(
			new GridColumn(
				'response',
				'submission.response',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);

		// Set the grid title.
		$this->setTitle('monograph.proofReading');
	}

	/**
	 * @see GridHandler::getRowInstance()
	 */
	function getRowInstance() {
		import('controllers.grid.files.fileSignoff.AuthorSignoffFilesGridRow');
		$row = new AuthorSignoffFilesGridRow(WORKFLOW_STAGE_ID_PRODUCTION);
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 */
	function getCategoryRowInstance() {
		import('controllers.grid.files.proof.AuthorProofingGridCategoryRow');
		$row = new AuthorProofingGridCategoryRow();
		return $row;
	}
}

?>
