<?php

/**
 * @file controllers/grid/files/proof/ApprovedProofFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApprovedProofFilesGridHandler
 * @ingroup controllers_grid_files_proof
 *
 * @brief Subclass of file grid for approved proof files.
 */

// import grid signoff files grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// Import monograph file class which contains the MONOGRAPH_FILE_* constants.
import('classes.monograph.MonographFile');

// Import the various classes for this grid
import('controllers.grid.files.proof.ApprovedProofFilesGridDataProvider');
import('controllers.grid.files.proof.ApprovedProofFilesGridRow');


class ApprovedProofFilesGridHandler extends GridHandler {
	/** @var $monograph Monograph */
	var $monograph;

	/** @var $publicationFormat PublicationFormat */
	var $publicationFormat;

	/**
	 * Constructor
	 */
	function ApprovedProofFilesGridHandler() {
		parent::GridHandler(new ApprovedProofFilesGridDataProvider());

		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR),
			array(
				'fetchGrid', 'fetchRow',
				'editApprovedProof', 'saveApprovedProof',
			)
		);
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		// Basic grid configuration
		$this->setId('proofFiles-' . $this->publicationFormat->getId());
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_EDITOR);
		$this->setTitle('payment.directSales');

		parent::initialize($request);

		// Columns
		$press =& $request->getPress();
		import('controllers.grid.files.proof.ApprovedProofFilesGridCellProvider');
		$cellProvider = new ApprovedProofFilesGridCellProvider($press->getSetting('pressCurrency'));
		$this->addColumn(new GridColumn(
			'name',
			'common.name',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'price',
			'payment.directSales.price',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider
		));
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_PRODUCTION));

		if (parent::authorize($request, $args, $roleAssignments)) {
			$publicationFormatId = (int) $request->getUserVar('publicationFormatId');
			$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
			$this->monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
			$this->publicationFormat =& $publicationFormatDao->getById($publicationFormatId, $this->monograph->getId());

			return true;
		}
		return false;
	}

	/**
	 * Get the row handler - override the default row handler
	 * @return ApprovedProofFilesGridRow
	 */
	function &getRowInstance() {
		$row = new ApprovedProofFilesGridRow();
		return $row;
	}
	/**
	 * @see GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		return array_merge(
			parent::getRequestArgs(),
			array('publicationFormatId' => $this->publicationFormat->getId())
		);
	}

	//
	// Public handler methods
	//
	function editApprovedProof($args, &$request) {
		$this->initialize($request);

		import('controllers.grid.files.proof.form.ApprovedProofForm');
		$approvedProofForm = new ApprovedProofForm($this->monograph, $this->publicationFormat, $request->getUserVar('fileId'));
		$approvedProofForm->initData();

		$json = new JSONMessage(true, $approvedProofForm->fetch($request));
		return $json->getString();
	}

	function saveApprovedProof($args, &$request) {
		import('controllers.grid.files.proof.form.ApprovedProofForm');
		$approvedProofForm = new ApprovedProofForm($this->monograph, $this->publicationFormat, $request->getUserVar('fileId'));
		$approvedProofForm->readInputData();

		if ($approvedProofForm->validate()) {
			$fileIdAndRevision = $approvedProofForm->execute($request);

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent($fileIdAndRevision);
		} else {
			$json = new JSONMessage(true, $approvedProofForm->fetch($request));
			return $json->getString();
		}
	}
}

?>
