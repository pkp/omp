<?php

/**
 * @file pages/workflow/WorkflowHandler.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for the submssion workflow.
 */

import('lib.pkp.pages.workflow.PKPWorkflowHandler');

// Access decision actions constants.
import('classes.workflow.EditorDecisionActionsManager');

class WorkflowHandler extends PKPWorkflowHandler {
	/**
	 * Constructor
	 */
	function WorkflowHandler() {
		parent::PKPWorkflowHandler();

		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER, ROLE_ID_ASSISTANT),
			array(
				'access', 'index', 'submission',
				'editorDecisionActions', // Submission & review
				'internalReview', // Internal review
				'externalReview', // External review
				'editorial',
				'production', 'productionFormatsTab', // Production
				'submissionHeader',
				'submissionProgressBar',
				'expedite'
			)
		);
	}


	//
	// Public handler methods
	//
	/**
	 * Show the internal review stage.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function internalReview($args, $request) {
		$this->_redirectToIndex($args, $request);
	}

	/**
	 * Show the production stage accordion contents
	 * @param $request PKPRequest
	 * @param $args array
	 * @return JSONMessage JSON object
	 */
	function productionFormatsTab(&$args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		$publicationFormats = $publicationFormatDao->getBySubmissionId($submission->getId());
		$templateMgr->assign('submission', $submission);
		$templateMgr->assign('publicationFormats', $publicationFormats->toAssociativeArray());
		$templateMgr->assign('currentFormatTabId', (int) $request->getUserVar('currentFormatTabId'));

		return $templateMgr->fetchJson('workflow/productionFormatsTab.tpl');
	}

	/**
	 * Expedites a submission through the submission process, if the submitter is a manager or editor.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function expedite($args, $request) {

		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		import('controllers.modals.submissionMetadata.form.CatalogEntrySubmissionReviewForm');
		$user = $request->getUser();
		$form = new CatalogEntrySubmissionReviewForm($submission->getId(), null, array('expeditedSubmission' => true));
		if ($submission && $request->getUserVar('confirm') != '') {

			// Process our submitted form in order to create the catalog entry.
			$form->readInputData();
			if($form->validate()) {
				$form->execute($request);
				// Create trivial notification in place on the form.
				$notificationManager = new NotificationManager();
				$user = $request->getUser();
				$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.savedSubmissionMetadata')));

				// Now, create a publication format for this submission.  Assume PDF, digital, and set to 'available'.
				$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
				$publicationFormat = $publicationFormatDao->newDataObject();
				$publicationFormat->setPhysicalFormat(false);
				$publicationFormat->setIsApproved(true);
				$publicationFormat->setIsAvailable(true);
				$publicationFormat->setSubmissionId($submission->getId());
				$publicationFormat->setProductAvailabilityCode('20'); // ONIX code for Available.
				$publicationFormat->setEntryKey('DA'); // ONIX code for Digital
				$publicationFormat->setData('name', 'PDF', $submission->getLocale());
				$publicationFormat->setSeq(REALLY_BIG_NUMBER);
				$publicationFormatId = $publicationFormatDao->insertObject($publicationFormat);

				// Next, create a galley PROOF file out of the submission file uploaded.
				$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
				import('lib.pkp.classes.submission.SubmissionFile'); // constants.
				$submissionFiles = $submissionFileDao->getLatestRevisions($submission->getId(), SUBMISSION_FILE_SUBMISSION);
				// Assume a single file was uploaded, but check for something that's PDF anyway.
				foreach ($submissionFiles as $submissionFile) {
					// test both mime type and file extension in case the mime type isn't correct after uploading.
					if ($submissionFile->getFileType() == 'application/pdf' || preg_match('/\.pdf$/', $submissionFile->getOriginalFileName())) {

						// Get the path of the current file because we change the file stage in a bit.
						$currentFilePath = $submissionFile->getFilePath();

						// this will be a new file based on the old one.
						$submissionFile->setFileId(null);
						$submissionFile->setRevision(1);
						$submissionFile->setViewable(true);
						$submissionFile->setFileStage(SUBMISSION_FILE_PROOF);
						$submissionFile->setAssocType(ASSOC_TYPE_REPRESENTATION);
						$submissionFile->setAssocId($publicationFormatId);

						// Assign the sales type and price for the submission file.
						switch ($request->getUserVar('salesType')) {
							case 'notAvailable':
								$submissionFile->setDirectSalesPrice(null);
								$submissionFile->setSalesType('notAvailable');
								break;
							case 'openAccess':
								$submissionFile->setDirectSalesPrice(0);
								$submissionFile->setSalesType('openAccess');
								break;
							default:
								$submissionFile->setDirectSalesPrice($request->getUserVar('price'));
								$submissionFile->setSalesType('directSales');
						}

						$submissionFileDao->insertObject($submissionFile, $currentFilePath);
						break;
					}
				}

				// no errors, clear all notifications for this submission which may have been created during the submission process and close the modal.
				$context = $request->getContext();
				$notificationDao = DAORegistry::getDAO('NotificationDAO');
				$notificationFactory = $notificationDao->deleteByAssoc(
					ASSOC_TYPE_SUBMISSION,
					$submission->getId(),
					null,
					null,
					$context->getId()
				);

				return new JSONMessage(true);
			} else {
				return new JSONMessage(true, $form->fetch($request));
			}
		} else {
			$form->initData($args, $request);
			return new JSONMessage(true, $form->fetch($request));
		}
	}


	//
	// Protected helper methods
	//
	/**
	 * Return the editor assignment notification type based on stage id.
	 * @param $stageId int
	 * @return int
	 */
	protected function getEditorAssignmentNotificationTypeByStageId($stageId) {
		switch ($stageId) {
			case WORKFLOW_STAGE_ID_SUBMISSION:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION;
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW;
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW;
			case WORKFLOW_STAGE_ID_EDITING:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING;
			case WORKFLOW_STAGE_ID_PRODUCTION:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION;
		}
		return null;
	}

	/**
	* @see PKPWorkflowHandler::isSubmissionReady()
	*/
	protected function isSubmissionReady($monograph) {
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph = $publishedMonographDao->getById($monograph->getId());
		if ($publishedMonograph) {
			// first check, there's a published monograph
			$publicationFormats = $publishedMonograph->getPublicationFormats(true);
			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
			import('classes.monograph.MonographFile'); // constants

			foreach ($publicationFormats as $format) {
				// there is at least one publication format.
				if ($format->getIsApproved()) {
					// it's ready to be included in the catalog

					$monographFiles = $submissionFileDao->getLatestRevisionsByAssocId(
					ASSOC_TYPE_PUBLICATION_FORMAT, $format->getId(),
					$publishedMonograph->getId()
					);

					foreach ($monographFiles as $file) {
						if ($file->getViewable() && !is_null($file->getDirectSalesPrice())) {
							// at least one file has a price set.
							return true;
						}
					}
				}
			}
		}

		return false;
	}
}

?>
