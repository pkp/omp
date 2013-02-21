<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep3Form.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep3Form
 * @ingroup submission_form
 *
 * @brief Form for Step 3 of author monograph submission.
 */


import('classes.submission.form.SubmissionSubmitForm');
import('classes.submission.SubmissionMetadataFormImplementation');

class SubmissionSubmitStep3Form extends SubmissionSubmitForm {

	/** @var SubmissionMetadataFormImplementation */
	var $_metadataFormImplem;

	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep3Form($press, $monograph) {
		parent::SubmissionSubmitForm($press, $monograph, 3);

		$this->_metadataFormImplem = new SubmissionMetadataFormImplementation($this);

		$this->_metadataFormImplem->addChecks($monograph);
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {

		$this->_metadataFormImplem->initData($this->monograph);

		return parent::initData();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {

		$this->_metadataFormImplem->readInputData();

		// Include category information.
		$this->readUserVars(array('categories'));

		// Load the series. This is used in the step 3 form to
		// determine whether or not to display indexing options.
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$this->_data['series'] =& $seriesDao->getById($this->monograph->getSeriesId(), $this->monograph->getPressId());
	}

	/**
	 * Display the form
	 */
	function display($request) {
		$templateMgr =& TemplateManager::getManager($request);

		$templateMgr->assign('isEditedVolume', $this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME);

		// If categories are configured for the press, present the LB.
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$templateMgr->assign('categoriesExist', $categoryDao->getCountByPressId($this->press->getId()) > 0);

		return parent::display($request);
	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		$this->_metadataFormImplem->getLocaleFieldNames();
	}

	/**
	 * Save changes to monograph.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int the monograph ID
	 */
	function execute($args, &$request) {

		// Execute monograph metadata related operations.
		$this->_metadataFormImplem->execute($this->monograph, $request);

		// handle category assignment.
		ListbuilderHandler::unpack($request, $this->getData('categories'));

		// Get an updated version of the monograph.
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getById($this->monographId);

		// Set other monograph data.
		if ($monograph->getSubmissionProgress() <= $this->step) {
			$monograph->setDateSubmitted(Core::getCurrentDate());
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress(0);
		}

		// Save the monograph.
		$monographDao->updateMonograph($monograph);

		// Assign the default users to the submission workflow stage
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->assignDefaultStageParticipants($monograph, WORKFLOW_STAGE_ID_SUBMISSION, $request);

		//
		// Send a notification to associated users
		//

		$roleDao =& DAORegistry::getDAO('RoleDAO'); /* @var $roleDao RoleDAO */

		// Get the managers.
		$pressManagers = $roleDao->getUsersByRoleId(ROLE_ID_MANAGER, $monograph->getPressId());

		$pressManagersArray = $pressManagers->toAssociativeArray();

		$allUserIds = array_keys($pressManagersArray);

		$notificationManager = new NotificationManager();
		foreach ($allUserIds as $userId) {
			$notificationManager->createNotification(
				$request, $userId, NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED,
				$monograph->getPressId(), ASSOC_TYPE_MONOGRAPH, $monograph->getId()
			);

			// Add TASK notification indicating that a submission is unassigned
			$notificationManager->createNotification(
				$request,
				$userId,
				NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_REQUIRED,
				$monograph->getPressId(),
				ASSOC_TYPE_MONOGRAPH,
				$monograph->getId(),
				NOTIFICATION_LEVEL_TASK
			);
		}

		// Send author notification email
		import('classes.mail.MonographMailTemplate');
		$mail = new MonographMailTemplate($monograph, 'SUBMISSION_ACK');
		$authorMail = new MonographMailTemplate($monograph, 'SUBMISSION_ACK_NOT_USER');

		$press =& $request->getPress();

		$router =& $request->getRouter();
		if ($mail->isEnabled()) {
			// submission ack emails should be from the press contact.
			$mail->setFrom($this->press->getSetting('contactEmail'), $this->press->getSetting('contactName'));
			$authorMail->setFrom($this->press->getSetting('contactEmail'), $this->press->getSetting('contactName'));

			$user = $monograph->getUser();
			$primaryAuthor = $monograph->getPrimaryAuthor();
			if (!isset($primaryAuthor)) {
				$authors =& $monograph->getAuthors();
				$primaryAuthor = $authors[0];
			}
			$mail->addRecipient($user->getEmail(), $user->getFullName());

			if ($user->getEmail() != $primaryAuthor->getEmail()) {
				$authorMail->addRecipient($primaryAuthor->getEmail(), $primaryAuthor->getFullName());
			}
			if ($press->getSetting('copySubmissionAckPrimaryContact')) {
				$authorMail->addBcc(
					$press->getSetting('contactEmail'),
					$press->getSetting('contactName')
				);
			}
			if ($copyAddress = $press->getSetting('copySubmissionAckAddress')) {
				$authorMail->addBcc($copyAddress);
			}

			$assignedAuthors = $monograph->getAuthors();

			foreach ($assignedAuthors as $author) {
				$authorEmail = $author->getEmail();
				// only add the author email if they have not already been added as the primary author
				// or user creating the submission.
				if ($authorEmail != $primaryAuthor->getEmail() && $authorEmail != $user->getEmail()) {
					$authorMail->addRecipient($author->getEmail(), $author->getFullName());
				}
			}
			$mail->bccAssignedSeriesEditors($monograph->getId(), WORKFLOW_STAGE_ID_SUBMISSION);

			$mail->assignParams(array(
				'authorName' => $user->getFullName(),
				'authorUsername' => $user->getUsername(),
				'editorialContactSignature' => $press->getSetting('contactName') . "\n" . $press->getLocalizedName(),
				'submissionUrl' => $router->url($request, null, 'authorDashboard', 'submission', $monograph->getId()),
			));

			$authorMail->assignParams(array(
				'submitterName' => $user->getFullName(),
				'editorialContactSignature' => $press->getSetting('contactName') . "\n" . $press->getLocalizedName(),
			));

			$mail->send($request);

			$recipients = $authorMail->getRecipients();
			if (!empty($recipients)) {
				$authorMail->send($request);
			}
		}

		$notificationManager->updateNotification(
			$request,
			array(NOTIFICATION_TYPE_APPROVE_SUBMISSION),
			null,
			ASSOC_TYPE_MONOGRAPH,
			$monograph->getId()
		);

		// Log submission.
		import('classes.log.MonographLog');
		MonographLog::logEvent($request, $monograph, MONOGRAPH_LOG_MONOGRAPH_SUBMIT, 'submission.event.monographSubmitted');

		return $this->monographId;
	}

	/**
	 * Associate a category with a monograph.
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertEntry(&$request, $newRowId) {

		$application =& PKPApplication::getApplication();
		$request =& $application->getRequest();

		$categoryId = $newRowId['name'];
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$press =& $request->getPress();
		$monograph =& $this->monograph;

		$category =& $categoryDao->getById($categoryId, $press->getId());
		if (!$category) return true;

		// Associate the category with the monograph
		$monographDao->addCategory(
				$monograph->getId(),
				$categoryId
		);
	}

	/**
	 * Delete a category association.
	 * @see ListbuilderHandler::deleteEntry
	 */
	function deleteEntry(&$request, $rowId) {
		if ($rowId) {
			$categoryDao =& DAORegistry::getDAO('CategoryDAO');
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$category =& $categoryDao->getById($rowId);
			if (!is_a($category, 'Category')) {
				assert(false);
				return false;
			}
			$monograph =& $this->monograph;
			$monographDao->removeCategory($monograph->getId(), $rowId);
		}

		return true;
	}

	/**
	 * Update a category association.
	 * @see ListbuilderHandler::updateEntry
	 */
	function updateEntry($request, $rowId, $newRowId) {

		$this->deleteEntry($request, $rowId);
		$this->insertEntry($request, $newRowId);
	}
}

?>
