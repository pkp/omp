<?php

/**
 * @file controllers/modals/signoff/form/SignoffFileUploadForm.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffFileUploadForm
 * @ingroup controllers_modals_signoff_form
 *
 * @brief Form for adding a submission file to a signoff.
 */


import('lib.pkp.classes.form.Form');

class SignoffFileUploadForm extends Form {
	var $_monographId;
	var $_stageId;
	var $_symbolic;
	var $_signoffId;

	/**
	 * Constructor.
	 * @param $request Request
	 * @param $monographId integer
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $fileStage integer
	 * @param $revisedFileId integer
	 * @param $assocType integer
	 * @param $assocId integer
	 */
	function SignoffFileUploadForm($monographId, $stageId, $symbolic, $signoffId = null) {
		$this->_monographId = $monographId;
		$this->_stageId = $stageId;
		$this->_symbolic = $symbolic;
		$this->_signoffId = $signoffId;

		parent::Form('controllers/modals/signoff/form/signoffFileUploadForm.tpl');
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with the form
	 */
	function getMonographId() {
		return $this->_monographId;
	}

	/**
	 * Get the current stage id
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the signoff's symbolic
	 * @return string
	 */
	function getSymbolic() {
		return $this->_symbolic;
	}

	/*
	 * Get the Signoff ID for this form
	 */
	function getSignoffId() {
		return $this->_signoffId;
	}

	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData($request) {
		$this->setData('submissionId', $this->getMonographId());
		$this->setData('stageId', $this->getStageId());
	}


	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$signoffDao = DAORegistry::getDAO('SubmissionFileSignoffDAO'); /* @var $signoffDao SubmissionFileSignoffDAO */
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */

		$signoffId = $this->getSignoffId();
		if ($signoffId) {
			$signoff = $signoffDao->getById($signoffId);
		}

		// Signoff specified. Find related file and show file name
		if (isset($signoff)) {
			$templateMgr->assign('signoffId', $signoff->getId());

			$submissionFile = $submissionFileDao->getLatestRevision($signoff->getAssocId());
			assert(is_a($submissionFile, 'MonographFile'));

			$templateMgr->assign('signoffFileName', $submissionFile->getLocalizedName());
		} else {
			// No signoff specified, look at all available signoffs
			$user = $request->getUser();
			$signoffs = $signoffDao->getAllBySubmission($this->getMonographId(), $this->getSymbolic(), $user->getId(), null, true);
			$availableSignoffs = array();
			while ($signoff = $signoffs->next()) {
				$submissionFile = $submissionFileDao->getLatestRevision($signoff->getAssocId());
				assert(is_a($submissionFile, 'MonographFile'));

				$availableSignoffs[$signoff->getId()] = $submissionFile->getLocalizedName();
			}

			// Only one, act as if it had been specified originally.
			if (count($availableSignoffs) == 1) {
				// Array as quick way of getting key and value. Only one element anyway.
				foreach ($availableSignoffs as $signoffId => $fileName) {
					$templateMgr->assign('signoffId', $signoffId);
					$templateMgr->assign('signoffFileName', $fileName);
				}
			} else {
				// Should let user choose from all available.
				$templateMgr->assign('availableSignoffs', $availableSignoffs);
			}
		}

		return parent::fetch($request);
	}

	/**
	 * @see Form::readInputData();
	 */
	function readInputData() {
		$this->readUserVars(array('signoffId', 'newNote', 'temporaryFileId'));
	}

	/**
	 * @see Form::validate()
	 */
	function validate($request) {
		// FIXME: this should go in a FormValidator in the constructor.
		$signoffId = $this->getSignoffId();
		return (is_numeric($signoffId) && $signoffId > 0);
	}

	//
	// Override from SubmissionFileUploadForm
	//
	/**
	 * @see Form::execute()
	 * @param $request Request
	 * @return MonographFile if successful, otherwise null
	 */
	function execute($request) {
		// Retrieve the signoff we're working with.
		$signoffDao = DAORegistry::getDAO('SubmissionFileSignoffDAO');
		$signoff = $signoffDao->getById($this->getData('signoffId'));
		assert(is_a($signoff, 'Signoff'));

		// Insert the note, if existing content and/or file.
		$temporaryFileId = $this->getData('temporaryFileId');
		if ($temporaryFileId || $this->getData('newNote')) {
			$user = $request->getUser();

			$noteDao = DAORegistry::getDAO('NoteDAO');
			$note = $noteDao->newDataObject();

			$note->setUserId($user->getId());
			$note->setContents($this->getData('newNote'));
			$note->setAssocType(ASSOC_TYPE_SIGNOFF);
			$note->setAssocId($signoff->getId());
			$noteId = $noteDao->insertObject($note);
			$note->setId($noteId);

			// Upload the file, if any, and associate it with the note.
			if ($temporaryFileId) {
				// Fetch the temporary file storing the uploaded library file
				$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
				$temporaryFile = $temporaryFileDao->getTemporaryFile(
					$temporaryFileId,
					$user->getId()
				);

				// Upload the file.
				// Bring in the SUBMISSION_FILE_* constants
				import('classes.monograph.MonographFile');

				$press = $request->getPress();
				import('lib.pkp.classes.file.SubmissionFileManager');
				$monographFileManager = new SubmissionFileManager($press->getId(), $this->getMonographId());
				$signoffFileId = $monographFileManager->temporaryFileToSubmissionFile(
					$temporaryFile,
					SUBMISSION_FILE_NOTE, $signoff->getUserId(),
					$signoff->getUserGroupId(), $signoff->getAssocId(), null,
					ASSOC_TYPE_NOTE, $noteId
				);


				// FIXME: Currently the code allows for a signoff to be
				// added many times (if the option is presented in the
				// form). Need to delete previous files uploaded to this
				// signoff. Partially due to #6799.

				// Mark ALL the signoffs for this user as completed with this file upload.
				if ($signoffFileId) {
					$signoff->setFileId($signoffFileId);
					$signoff->setFileRevision(1);
				}
			}

			// Now mark the signoff as completed (we have a note with content
			// or a file or both).
			$signoff->setDateCompleted(Core::getCurrentDate());
			$signoffDao->updateObject($signoff);

			$notificationMgr = new NotificationManager();
			$notificationMgr->updateNotification(
				$request,
				array(NOTIFICATION_TYPE_AUDITOR_REQUEST),
				array($signoff->getUserId()),
				ASSOC_TYPE_SIGNOFF,
				$signoff->getId()
			);

			$notificationMgr->updateNotification(
				$request,
				array(NOTIFICATION_TYPE_SIGNOFF_COPYEDIT, NOTIFICATION_TYPE_SIGNOFF_PROOF),
				array($signoff->getUserId()),
				ASSOC_TYPE_SUBMISSION,
				$this->getMonographId()
			);

			// log the event.
			import('lib.pkp.classes.log.SubmissionFileLog');
			import('lib.pkp.classes.log.SubmissionFileEventLogEntry'); // constants
			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
			$monographFile = $submissionFileDao->getLatestRevision($signoff->getFileId());

			if (isset($monographFile)) {
				SubmissionFileLog::logEvent($request, $monographFile, SUBMISSION_LOG_FILE_AUDIT_UPLOAD, 'submission.event.fileAuditUploaded', array('file' => $monographFile->getOriginalFileName(), 'name' => $user->getFullName(), 'username' => $user->getUsername()));
			}
			return $signoff->getId();
		}
	}
}

?>
