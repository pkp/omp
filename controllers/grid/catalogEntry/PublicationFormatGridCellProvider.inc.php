<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridCellProvider
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Base class for a cell provider that can retrieve labels for publication formats
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

// Import class which contains the SUBMISSION_FILE_* constants.
import('lib.pkp.classes.submission.SubmissionFile');

class PublicationFormatGridCellProvider extends DataObjectGridCellProvider {

	/** @var int Submission ID */
	var $_submissionId;

	/** @var boolean */
	protected $_canManage;

	/**
	 * Constructor
	 * @param $submissionId int Submission ID
	 * @param $canManage boolean
	 */
	function __construct($submissionId, $canManage) {
		parent::__construct();
		$this->_submissionId = $submissionId;
		$this->_canManage = $canManage;
	}


	//
	// Getters and setters.
	//
	/**
	 * Get submission ID.
	 * @return int
	 */
	function getSubmissionId() {
		return $this->_submissionId;
	}


	//
	// Template methods from GridCellProvider
	//
	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$data = $row->getData();

		if (is_a($data, 'Representation')) {
			/** @var $data Representation */
			switch ($column->getId()) {
				case 'indent': return array();
				case 'name':
					$remoteURL = $data->getRemoteURL();
					if ($remoteURL) {
						return array('label' => '<a href="'.htmlspecialchars($remoteURL).'" target="_blank">'.htmlspecialchars($data->getLocalizedName()).'</a>' . '<span class="onix_code">' . $data->getNameForONIXCode() . '</span>');
					}
					return array('label' => htmlspecialchars($data->getLocalizedName()) . '<span class="onix_code">' . $data->getNameForONIXCode() . '</span>');
				case 'isAvailable':
					return array('status' => $data->getIsAvailable()?'completed':'new');
				case 'isComplete':
					return array('status' => $data->getIsApproved()?'completed':'new');
			}
		} else {
			assert(is_array($data) && isset($data['submissionFile']));
			$proofFile = $data['submissionFile'];
			switch ($column->getId()) {
				case 'isAvailable':
					return array('status' => ($proofFile->getSalesType() != null && $proofFile->getDirectSalesPrice() != null)?'completed':'new');
				case 'name':
					import('lib.pkp.controllers.grid.files.FileNameGridColumn');
					$fileNameGridColumn = new FileNameGridColumn(true, WORKFLOW_STAGE_ID_PRODUCTION);
					return $fileNameGridColumn->getTemplateVarsFromRow($row);
				case 'isComplete':
					return array('status' => $proofFile->getViewable()?'completed':'new');
			}
		}

		return parent::getTemplateVarsFromRowColumn($row, $column);
	}

	/**
	 * Get request arguments.
	 * @param $row GridRow
	 * @return array
	 */
	function getRequestArgs($row) {
		return array(
			'submissionId' => $this->_submission->getId(),
			'submissionVersion' => $this->_submission->getSubmissionVersion(),
		);
	}

	/**
	 * @see GridCellProvider::getCellActions()
	 */
	function getCellActions($request, $row, $column, $position = GRID_ACTION_POSITION_DEFAULT) {
		$data = $row->getData();
		$router = $request->getRouter();
		if (is_a($data, 'Representation')) {
			switch ($column->getId()) {
				case 'isAvailable':
					return array(new LinkAction(
						'availableRepresentation',
						new RemoteActionConfirmationModal(
							$request->getSession(),
							__($data->getIsAvailable()?'grid.catalogEntry.availableRepresentation.removeMessage':'grid.catalogEntry.availableRepresentation.message'),
							__('grid.catalogEntry.availableRepresentation.title'),
							$router->url(
								$request, null, null, 'setAvailable', null,
								array(
									'representationId' => $data->getId(),
									'newAvailableState' => $data->getIsAvailable()?0:1,
									'submissionId' => $data->getSubmissionId(),
									'submissionVersion' => $data->getSubmissionVersion(),
								)
							),
							'modal_approve'
						),
						$data->getIsAvailable()?__('grid.catalogEntry.isAvailable'):__('grid.catalogEntry.isNotAvailable'),
						$data->getIsAvailable()?'complete':'incomplete',
						__('grid.action.formatAvailable')
					));
				case 'name':
					// if it is a remotely hosted content, don't provide
					// file upload and select link actions
					$remoteURL = $data->getRemoteURL();
					if ($remoteURL) {
						return array();
					}
					// If this is just an author account, don't give any actions
					if (!$this->_canManage) return array();
					import('lib.pkp.controllers.api.file.linkAction.AddFileLinkAction');
					import('lib.pkp.controllers.grid.files.fileList.linkAction.SelectFilesLinkAction');
					AppLocale::requireComponents(LOCALE_COMPONENT_PKP_EDITOR);
					return array(
						new AddFileLinkAction(
							$request, $data->getSubmissionId(), WORKFLOW_STAGE_ID_PRODUCTION,
							array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT), SUBMISSION_FILE_PROOF,
							ASSOC_TYPE_REPRESENTATION, $data->getId()
						),
						new SelectFilesLinkAction(
							$request,
							array(
								'submissionId' => $data->getSubmissionId(),
								'assocType' => ASSOC_TYPE_REPRESENTATION,
								'assocId' => $data->getId(),
								'representationId' => $data->getId(),
								'stageId' => WORKFLOW_STAGE_ID_PRODUCTION,
								'fileStage' => SUBMISSION_FILE_PROOF,
								'submissionVersion' => $data->getSubmissionVersion(),
							),
							__('editor.submission.selectFiles')
						)
					);
				case 'isComplete':
					import('lib.pkp.classes.linkAction.request.AjaxModal');
					return array(new LinkAction(
						'approveRepresentation',
						new AjaxModal(
							$router->url(
								$request, null, null, 'setApproved', null,
								array(
									'representationId' => $data->getId(),
									'newApprovedState' => $data->getIsApproved()?0:1,
									'submissionId' => $data->getSubmissionId(),
									'submissionVersion' => $data->getSubmissionVersion(),
								)
							),
							__('grid.catalogEntry.approvedRepresentation.title'),
							'modal_approve'
						),
						$data->getIsApproved()?__('submission.complete'):__('submission.incomplete'),
						$data->getIsApproved()?'complete':'incomplete',
						__('grid.action.setApproval')
					));
			}
		} else {
			assert(is_array($data) && isset($data['submissionFile']));
			$submissionFile = $data['submissionFile'];
			switch ($column->getId()) {
				case 'isAvailable':
					$salesType = preg_replace('/[^\da-z]/i', '', $submissionFile->getSalesType());
					$salesTypeString = 'editor.monograph.approvedProofs.edit.linkTitle';
					if ($salesType == 'openAccess') {
						$salesTypeString = 'payment.directSales.openAccess';
					} elseif ($salesType == 'directSales') {
						$salesTypeString = 'payment.directSales.directSales';
					} elseif ($salesType == 'notAvailable') {
						$salesTypeString = 'payment.directSales.notAvailable';
					}
					return array(new LinkAction(
						'editApprovedProof',
						new AjaxModal(
							$router->url($request, null, null, 'editApprovedProof', null, array(
								'fileId' => $submissionFile->getFileId() . '-' . $submissionFile->getRevision(),
								'submissionId' => $submissionFile->getSubmissionId(),
								'representationId' => $submissionFile->getAssocId(),
								'submissionVersion' => $submissionFile->getSubmissionVersion(),
							)),
							__('editor.monograph.approvedProofs.edit'),
							'edit'
						),
						__($salesTypeString),
						$salesType
					));
				case 'name':
					import('lib.pkp.controllers.grid.files.FileNameGridColumn');
					$fileNameColumn = new FileNameGridColumn(true, WORKFLOW_STAGE_ID_PRODUCTION, true);
					return $fileNameColumn->getCellActions($request, $row, $position);
				case 'isComplete':
					AppLocale::requireComponents(LOCALE_COMPONENT_PKP_EDITOR);
					import('lib.pkp.classes.linkAction.request.AjaxModal');
					$title = __($submissionFile->getViewable()?'editor.submission.proofreading.revokeProofApproval':'editor.submission.proofreading.approveProof');
					return array(new LinkAction(
						$submissionFile->getViewable()?'approved':'not_approved',
						new AjaxModal(
							$router->url(
								$request, null, null, 'setProofFileCompletion',
								null,
								array(
									'submissionId' => $submissionFile->getSubmissionId(),
									'fileId' => $submissionFile->getFileId(),
									'revision' => $submissionFile->getRevision(),
									'approval' => !$submissionFile->getViewable(),
									'submissionVersion' => $submissionFile->getSubmissionVersion(),
								)
							),
							$title,
							'modal_approve'
						),
						$submissionFile->getViewable()?__('grid.catalogEntry.availableRepresentation.approved'):__('grid.catalogEntry.availableRepresentation.notApproved'),
						$submissionFile->getViewable()?'complete':'incomplete',
						__('grid.action.setApproval')
					));
			}
		}
		return parent::getCellActions($request, $row, $column, $position);
	}
}


