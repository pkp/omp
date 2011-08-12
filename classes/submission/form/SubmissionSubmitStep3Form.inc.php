<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep3Form.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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

		// Load the series. This is used in the step 3 form to
		// determine whether or not to display indexing options.
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$this->_data['series'] =& $seriesDao->getById($this->monograph->getSeriesId(), $this->monograph->getPressId());
	}

	/**
	 * Display the form
	 */
	function display($request) {
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('isEditedVolume', $this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME);

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
		$this->_metadataFormImplem->execute($this->monograph);

		// Get an updated version of the monograph.
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($this->monographId);

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
		$seriesEditorAction->assignDefaultStageParticipants($monograph, WORKFLOW_STAGE_ID_SUBMISSION);

		// Send a notification to associated users
		import('lib.pkp.classes.notification.NotificationManager');
		$notificationManager = new NotificationManager();
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$notificationUsers = array();
		$pressManagers = $roleDao->getUsersByRoleId(ROLE_ID_PRESS_MANAGER);
		$allUsers = $pressManagers->toArray();
		$editors = $roleDao->getUsersByRoleId(ROLE_ID_EDITOR);
		$router =& $request->getRouter();
		array_merge($allUsers, $editors->toArray());
		foreach ($allUsers as $user) {
			$notificationUsers[] = array('id' => $user->getId());
		}
		foreach ($notificationUsers as $userRole) {
			$url = $router->url($request, null, 'workflow', 'submission', $monograph->getId());
			$notificationManager->createNotification(
				$userRole['id'], 'notification.type.monographSubmitted',
				$monograph->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED
			);
		}

		// Send author notification email
		import('classes.mail.MonographMailTemplate');
		$mail = new MonographMailTemplate($monograph, 'SUBMISSION_ACK', null, null, null, false);
		$press =& $request->getPress();

		if ($mail->isEnabled()) {
			$user = $monograph->getUser();
			$mail->addRecipient($user->getEmail(), $user->getFullName());
			$mail->bccAssignedEditors($monograph->getId());
			$mail->bccAssignedSeriesEditors($monograph->getId());

			$mail->assignParams(array(
				'authorName' => $user->getFullName(),
				'authorUsername' => $user->getUsername(),
				'editorialContactSignature' => $press->getSetting('contactName') . "\n" . $press->getLocalizedName(),
				'submissionUrl' => $router->url($request, null, 'authorDashboard', 'submission', $monograph->getId())
			));
			$mail->send($request);
		}

		return $this->monographId;
	}
}

?>
