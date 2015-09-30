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

	/** @var boolean */
	var $_inCatalogEntryModal;

	/**
	 * Constructor
	 * @param $monographId int
	 * @param $inCatalogEntryModal boolean Tells if grid is loaded inside
	 * catalog entry modal.
	 */
	function PublicationFormatGridCellProvider($monographId, $inCatalogEntryModal) {
		parent::DataObjectGridCellProvider();
		$this->_monographId = $monographId;
		$this->_inCatalogEntryModal = $inCatalogEntryModal;
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

	/**
	 * Get a flag that tells if grid is loaded
	 * inside the catalog entry modal.
	 * @return boolean
	 */
	function getInCatalogEntryModal() {
		return $this->_inCatalogEntryModal;
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
		$publicationFormat = $row->getData();
		$columnId = $column->getId();
		assert(is_a($publicationFormat, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'name':
				$label = $publicationFormat->getLocalizedName() . ' (' .$publicationFormat->getNameForONIXCode() . ')';
				return array('label' => $label);
			case 'proofComplete':
			case 'isAvailable':
			case 'isApproved':
				return array('status' => $this->getCellState($row, $column));
			default:
				assert(false);
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
	 * Gathers the state of a given cell given a $row/$column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return string
	 */
	function getCellState($row, $column) {
		$publicationFormat = $row->getData();
		switch ($column->getId()) {
			case 'proofComplete':
				return $this->isProofComplete($publicationFormat)?'completed':'new';
			case 'isApproved':
				$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
				$publishedMonograph = $publishedMonographDao->getById($publicationFormat->getMonographId());
				return ($publicationFormat->getIsApproved() && $publishedMonograph)?'completed':'new';
			case 'isAvailable':
				return $publicationFormat->getIsAvailable()?'completed':'new';
			default:
				assert(false);
		}
	}

	/**
	 * @see GridCellProvider::getCellActions()
	 */
	function getCellActions($request, $row, $column) {
		$publicationFormat = $row->getData();
		$monographId = $publicationFormat->getMonographId();
		$representationId = $publicationFormat->getId();
		switch ($column->getId()) {
			case 'proofComplete':
				import('controllers.api.proof.linkAction.ApproveProofsLinkAction');
				return array(new ApproveProofsLinkAction($request, $monographId, $representationId, $this->getCellState($row, $column)));
				break;
			case 'isApproved':
				if ($this->getInCatalogEntryModal()) {
					import('lib.pkp.classes.linkAction.request.NullAction');
					$toolTip = ($this->getCellState($row, $column) == 'completed') ? __('grid.action.formatInCatalogEntry') : null;
					return array(new LinkAction('publicationFormatTab', new NullAction(), __('monograph.publicationFormat.openTab'), $this->getCellState($row, $column), $toolTip));
				} else {
					import('controllers.modals.submissionMetadata.linkAction.SubmissionEntryLinkAction');
					return array(new SubmissionEntryLinkAction($request, $monographId, WORKFLOW_STAGE_ID_PRODUCTION, $representationId, $this->getCellState($row, $column)));
				}
				break;
			case 'isAvailable':
				$router = $request->getRouter();
				$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
				$publishedMonograph = $publishedMonographDao->getById($publicationFormat->getMonographId());

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
				if (!$publicationFormat->getIsApproved()) {
					$templateMgr->assign('notificationId', uniqid('notAvailable'));
					$templateMgr->assign('notificationContents', __('grid.catalogEntry.availablePublicationFormat.notApprovedWarning'));
					$warningMarkup .= $templateMgr->fetch('controllers/notification/inPlaceNotificationContent.tpl');
				}
				if (!$this->isProofComplete($publicationFormat)) {
					$templateMgr->assign('notificationId', uniqid('notProofed'));
					$templateMgr->assign('notificationContents', __('grid.catalogEntry.availablePublicationFormat.proofNotApproved'));
					$warningMarkup .= $templateMgr->fetch('controllers/notification/inPlaceNotificationContent.tpl');
				}
				// If we have any notifications, wrap them in the appropriately styled div
				if ($warningMarkup !== '') $warningMarkup = "<div class=\"pkp_notification\">$warningMarkup</div>";
				$toolTip = ($this->getCellState($row, $column) == 'completed') ? __('grid.action.formatAvailable') : null;
				return array(new LinkAction(
					'availablePublicationFormat',
					new RemoteActionConfirmationModal(
						$warningMarkup . __($publicationFormat->getIsAvailable()?'grid.catalogEntry.availablePublicationFormat.removeMessage':'grid.catalogEntry.availablePublicationFormat.message'),
						__('grid.catalogEntry.availablePublicationFormat.title'),
						$router->url($request, null, 'grid.catalogEntry.PublicationFormatGridHandler',
							'setAvailable', null, array('representationId' => $publicationFormat->getId(), 'newAvailableState' => $publicationFormat->getIsAvailable()?0:1, 'submissionId' => $monographId)),
						'modal_approve'),
						__('common.disable'),
						$this->getCellState($row, $column),
						$toolTip
				));
				break;
			default:
				return array();
		}
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
