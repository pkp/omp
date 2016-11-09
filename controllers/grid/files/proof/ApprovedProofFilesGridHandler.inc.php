<?php

/**
 * @file controllers/grid/files/proof/ApprovedProofFilesGridHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApprovedProofFilesGridHandler
 * @ingroup controllers_grid_files_proof
 *
 * @brief Subclass of file grid for approved proof files.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// Import submission file class which contains the SUBMISSION_FILE_* constants.
import('lib.pkp.classes.submission.SubmissionFile');

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
	function __construct() {
		parent::__construct(new ApprovedProofFilesGridDataProvider());

		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR),
			array(
				'fetchGrid', 'fetchRow',
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
	function initialize($request) {
		// Basic grid configuration
		$this->setId('proofFiles-' . $this->publicationFormat->getId());
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR);
		$this->setTitle('payment.directSales');

		parent::initialize($request);

		// Columns
		$press = $request->getPress();
		import('controllers.grid.files.proof.ApprovedProofFilesGridCellProvider');
		$cellProvider = new ApprovedProofFilesGridCellProvider($press->getSetting('currency'));
		$this->addColumn(new GridColumn(
			'name',
			'common.name',
			null,
			null,
			$cellProvider,
			array('width' => 60, 'alignment' => COLUMN_ALIGNMENT_LEFT)
		));
		$this->addColumn(new GridColumn(
			'approved',
			'payment.directSales.approved',
			null,
			'controllers/grid/common/cell/statusCell.tpl',
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'price',
			'payment.directSales.availability',
			null,
			null,
			$cellProvider
		));
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.WorkflowStageAccessPolicy');
		$this->addPolicy(new WorkflowStageAccessPolicy($request, $args, $roleAssignments, 'submissionId', WORKFLOW_STAGE_ID_PRODUCTION));

		if (parent::authorize($request, $args, $roleAssignments)) {
			$representationId = (int) $request->getUserVar('representationId');
			$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
			$this->monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
			$this->publicationFormat = $publicationFormatDao->getById($representationId, $this->monograph->getId());

			return true;
		}
		return false;
	}

	/**
	 * Get the row handler - override the default row handler
	 * @return ApprovedProofFilesGridRow
	 */
	function getRowInstance() {
		return new ApprovedProofFilesGridRow();
	}
	/**
	 * @see GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		return array_merge(
			parent::getRequestArgs(),
			array('representationId' => $this->publicationFormat->getId())
		);
	}

	//
	// Public handler methods
	//
	/**
	 * Edit an approved proof.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editApprovedProof($args, $request) {
		$this->initialize($request);

		import('controllers.grid.files.proof.form.ApprovedProofForm');
		$approvedProofForm = new ApprovedProofForm($this->monograph, $this->publicationFormat, $request->getUserVar('fileId'));
		$approvedProofForm->initData();

		return new JSONMessage(true, $approvedProofForm->fetch($request));
	}

	/**
	 * Save an approved proof.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function saveApprovedProof($args, $request) {
		import('controllers.grid.files.proof.form.ApprovedProofForm');
		$approvedProofForm = new ApprovedProofForm($this->monograph, $this->publicationFormat, $request->getUserVar('fileId'));
		$approvedProofForm->readInputData();

		if ($approvedProofForm->validate()) {
			$fileIdAndRevision = $approvedProofForm->execute($request);

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent($fileIdAndRevision);
		} else {
			return new JSONMessage(true, $approvedProofForm->fetch($request));
		}
	}
}

?>
