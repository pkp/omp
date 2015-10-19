<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridCellProvider
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Base class for a cell provider that can retrieve labels for publication formats
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

// Import monograph file class which contains the SUBMISSION_FILE_* constants.
import('classes.monograph.MonographFile');

class PublicationFormatGridCellProvider extends DataObjectGridCellProvider {

	/** @var int */
	var $_monographId;

	/**
	 * Constructor
	 * @param $monographId int
	 */
	function PublicationFormatGridCellProvider($monographId) {
		parent::DataObjectGridCellProvider();
		$this->_monographId = $monographId;
	}


	//
	// Getters and setters.
	//
	/**
	 * Get monograph id.
	 * @return int
	 */
	function getMonographId() {
		return $this->_monographId;
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
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		if (is_a($data, 'PublicationFormat')) switch ($column->getId()) {
			case 'indent': return array();
			case 'name':
				return array('label' => $data->getLocalizedName() . ' (' . $data->getNameForONIXCode() . ')');
			case 'isComplete':
				return array('status' => $data->getIsApproved()?'completed':'new');
			case 'isAvailable':
				return array('status' => $data->getIsAvailable()?'completed':'new');
			default: assert(false);
		} else {
			assert(is_array($data) && isset($data['submissionFile']));
			$proofFile = $data['submissionFile'];
			switch ($column->getId()) {
				case 'name':
					return array('label' => $proofFile->getLocalizedName());
				case 'isComplete':
					return array('status' => $proofFile->getViewable()?'completed':'new');
				case 'isAvailable':
					return array('status' => ($proofFile->getSalesType() != null && $proofFile->getDirectSalesPrice() != null)?'completed':'new');
				default: assert(false);
			}
		}
	}

	/**
	 * Determine if at least one proof is complete for the publication format.
	 * @param $publicationFormat PublicationFormat
	 * @return boolean
	 */
	function isProofComplete(&$publicationFormat) {
		$monographFiles = $this->getMonographFiles($publicationFormat->getId());
		$proofComplete = false;
		// If we have at least one viewable file, we consider
		// proofs as approved.
		foreach ($monographFiles as $file) {
			if ($file->getViewable()) {
				$proofComplete = true;
				break;
			}
		}
		return $proofComplete;
	}

	/**
	 * @see GridCellProvider::getCellActions()
	 */
	function getCellActions($request, $row, $column) {
		$data = $row->getData();
		$router = $request->getRouter();
		if (is_a($data, 'PublicationFormat')) {
			$monographId = $data->getMonographId();
			switch ($column->getId()) {
				case 'name':
					import('lib.pkp.controllers.api.file.linkAction.AddFileLinkAction');
					import('lib.pkp.controllers.grid.files.fileList.linkAction.SelectFilesLinkAction');
					AppLocale::requireComponents(LOCALE_COMPONENT_PKP_EDITOR);
					return array(
						new AddFileLinkAction(
							$request, $data->getSubmissionId(), WORKFLOW_STAGE_ID_PRODUCTION,
							array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT), null, SUBMISSION_FILE_PROOF,
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
							),
							__('editor.submission.selectFiles')
						)
					);
				case 'isComplete':
					import('controllers.modals.submissionMetadata.linkAction.SubmissionEntryLinkAction');
					return array(new SubmissionEntryLinkAction($request, $monographId, WORKFLOW_STAGE_ID_PRODUCTION, $data->getId(), $data->getIsApproved()?'complete':'incomplete'));
				case 'isAvailable':
					$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
					$publishedMonograph = $publishedMonographDao->getById($data->getMonographId());

					// FIXME: Bug #7715
					$warningMarkup = '';
					$templateMgr = TemplateManager::getManager();
					$templateMgr->assign('notificationStyleClass', 'notifyWarning');
					$templateMgr->assign('notificationTitle', __('common.warning'));
					if (!$publishedMonograph) {
						$templateMgr->assign('notificationId', uniqid('notPublished'));
						$templateMgr->assign('notificationContents', __('grid.catalogEntry.availablePublicationFormat.catalogNotApprovedWarning'));
						$warningMarkup .= $templateMgr->fetch('controllers/notification/inPlaceNotificationContent.tpl');
					}
					if (!$data->getIsApproved()) {
						$templateMgr->assign('notificationId', uniqid('notAvailable'));
						$templateMgr->assign('notificationContents', __('grid.catalogEntry.availablePublicationFormat.notApprovedWarning'));
						$warningMarkup .= $templateMgr->fetch('controllers/notification/inPlaceNotificationContent.tpl');
					}
					if (!$this->isProofComplete($data)) {
						$templateMgr->assign('notificationId', uniqid('notProofed'));
						$templateMgr->assign('notificationContents', __('grid.catalogEntry.availablePublicationFormat.proofNotApproved'));
						$warningMarkup .= $templateMgr->fetch('controllers/notification/inPlaceNotificationContent.tpl');
					}
					// If we have any notifications, wrap them in the appropriately styled div
					if ($warningMarkup !== '') $warningMarkup = "<div class=\"pkp_notification\">$warningMarkup</div>";
					return array(new LinkAction(
						'availablePublicationFormat',
						new RemoteActionConfirmationModal(
							$warningMarkup . __($data->getIsAvailable()?'grid.catalogEntry.availablePublicationFormat.removeMessage':'grid.catalogEntry.availablePublicationFormat.message'),
							__('grid.catalogEntry.availablePublicationFormat.title'),
							$router->url($request, null, 'grid.catalogEntry.PublicationFormatGridHandler',
								'setAvailable', null, array('representationId' => $data->getId(), 'newAvailableState' => $data->getIsAvailable()?0:1, 'submissionId' => $monographId)),
							'modal_approve'
						),
						$data->getIsAvailable()?__('common.disable'):__('common.enable'),
						$data->getIsAvailable()?'complete':'incomplete',
						__('grid.action.formatAvailable')
					));
			}
		} else {
			assert(is_array($data) && isset($data['submissionFile']));
			$submissionFile = $data['submissionFile'];
			switch ($column->getId()) {
				case 'isComplete':
					import('lib.pkp.classes.linkAction.request.AjaxAction');
					return array(new LinkAction(
						$submissionFile->getViewable()?'disapprove':'approve',
						new AjaxAction($router->url(
							$request, null, null, 'setProofFileCompletion',
							null,
							array(
								'submissionId' => $submissionFile->getSubmissionId(),
								'fileId' => $submissionFile->getFileId(),
								'revision' => $submissionFile->getRevision(),
								'approval' => !$submissionFile->getViewable(),
							)
						)),
						$submissionFile->getViewable()?__('grid.action.disapprove'):__('grid.action.approve')
					));
				case 'isAvailable':
					return array(new LinkAction(
						'editApprovedProof',
						new AjaxModal(
							$router->url($request, null, null, 'editApprovedProof', null, array(
								'fileId' => $submissionFile->getFileId() . '-' . $submissionFile->getRevision(),
								'submissionId' => $submissionFile->getSubmissionId(),
								'representationId' => $submissionFile->getAssocId(),
							)),
							__('editor.monograph.approvedProofs.edit'),
							'edit'
						),
						__('editor.monograph.approvedProofs.edit.linkTitle'),
						preg_replace('/[^\da-z]/i', '', $submissionFile->getSalesType())
					));
			}
		}
		return parent::getCellActions($request, $row, $column);
	}

	/**
	 * Get the monograph files associated with the passed
	 * publication format id.
	 * @param $representationId int
	 * @return array
	 */
	function &getMonographFiles($representationId) {
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles = $submissionFileDao->getLatestRevisionsByAssocId(
			ASSOC_TYPE_PUBLICATION_FORMAT, $representationId,
			$this->getMonographId(), SUBMISSION_FILE_PROOF
		);

		return $monographFiles;
	}
}

?>
