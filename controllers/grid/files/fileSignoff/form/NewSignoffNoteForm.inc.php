<?php

/**
 * @file controllers/grid/files/fileSignoff/form/NewSignoffNoteForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewSignoffNoteForm
 * @ingroup informationCenter_form
 *
 * @brief Form to display and post notes on a signoff.
 */


import('controllers.informationCenter.form.NewNoteForm');

class NewSignoffNoteForm extends NewNoteForm {
	/** @var $signoffId int The ID of the signoff to attach the note to */
	var $signoffId;

	/** @var $monographId int The ID of the signoff monograph */
	var $_monographId;

	/** @var $symbolic int The signoff symbolic. */
	var $_symbolic;

	/** @var $stageId int The signoff stage id. */
	var $_stageId;

	/** @var $actionArgs array The fetch notes list action args. */
	var $_actionArgs;

	/**
	 * Constructor.
	 */
	function NewSignoffNoteForm($signoffId, $monographId, $signoffSymbolic, $stageId) {
		parent::NewNoteForm();

		$this->signoffId = $signoffId;
		$this->_monographId = $monographId;
		$this->_symbolic = $signoffSymbolic;
		$this->_stageId = $stageId;
		$this->_actionArgs = array(
			'signoffId' => $signoffId,
			'monographId' => $monographId,
			'stageId' => $stageId
		);
	}

	/**
	 * Return the assoc type for this note.
	 * @return int
	 */
	function getAssocType() {
		return ASSOC_TYPE_SIGNOFF;
	}

	/**
	 * Return the assoc ID for this note.
	 * @return int
	 */
	function getAssocId() {
		return $this->signoffId;
	}

	/**
	 * @see NewNoteForm::getNewNoteFormTemplate()
	 */
	function getNewNoteFormTemplate() {
		return 'controllers/informationCenter/newFileUploadNoteForm.tpl';
	}

	/**
	 * @see NewNoteForm::getSubmitNoteLocaleKey()
	 */
	function getSubmitNoteLocaleKey() {
		return 'monograph.task.addNote';
	}

	/**
	 * @see NewNoteForm::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('signoffId', 'temporaryFileId'));
		parent::readInputData();
	}

	/**
	 * @see Form::validate()
	 */
	function validate() {
		// FIXME: this should go in a FormValidator in the constructor.
		$signoffId = $this->signoffId;
		return (is_numeric($signoffId) && $signoffId > 0);
	}

	/**
	 * @see NewNoteForm::fetch()
	 */
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('linkParams', $this->_actionArgs);
		$templateMgr->assign('showEarlierEntries', false);
		$templateMgr->assign('signoffId', $this->signoffId);
		$templateMgr->assign('symbolic', $this->_symbolic);
		$templateMgr->assign('stageId', $this->_stageId);
		$templateMgr->assign('monographId', $this->_monographId);

		return parent::fetch($request);
	}

	function execute($request, $userRoles) {
		$user =& $request->getUser();

		// Retrieve the signoff we're working with.
		$signoffDao =& DAORegistry::getDAO('MonographFileSignoffDAO');
		$signoff =& $signoffDao->getById($this->getData('signoffId'));
		assert(is_a($signoff, 'Signoff'));

		// Insert the note, if existing content and/or file.
		$temporaryFileId = $this->getData('temporaryFileId');
		if ($temporaryFileId || $this->getData('newNote')) {
			$user =& $request->getUser();

			$noteDao =& DAORegistry::getDAO('NoteDAO');
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
				$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
				$temporaryFile =& $temporaryFileDao->getTemporaryFile(
					$temporaryFileId,
					$user->getId()
				);

				// Upload the file.
				// Bring in the MONOGRAPH_FILE_* constants
				import('classes.monograph.MonographFile');

				$press =& $request->getPress();
				import('classes.file.MonographFileManager');
				$monographFileManager = new MonographFileManager($press->getId(), $this->_monographId);

				// Get the monograph file that is associated with the signoff.
				$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /** @var $submissionFileDao SubmissionFileDAO */
				$signoffFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId());
				assert(is_a($signoffFile, 'MonographFile'));

				$noteFileId = $monographFileManager->temporaryFileToMonographFile(
					$temporaryFile,
					MONOGRAPH_FILE_NOTE, $signoff->getUserId(),
					$signoff->getUserGroupId(), null, $signoffFile->getGenreId(),
					ASSOC_TYPE_NOTE, $noteId
				);
			}

			if ($user->getId() == $signoff->getUserId() && !$signoff->getDateCompleted()) {
				// Considered as a signoff response.
				// Mark the signoff as completed (we have a note with content
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
					ASSOC_TYPE_MONOGRAPH,
					$this->_monographId
				);

				// Define the success trivial notification locale key.
				$successLocaleKey = 'notification.uploadedResponse';

				// log the event.
				import('classes.log.MonographFileLog');
				import('classes.log.MonographFileEventLogEntry'); // constants
				$monographDao =& DAORegistry::getDAO('MonographDAO');
				$monograph =& $monographDao->getById($this->_monographId);
				$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
				$monographFile =& $submissionFileDao->getLatestRevision($signoff->getFileId());

				if (isset($monographFile)) {
					MonographFileLog::logEvent($request, $monographFile, MONOGRAPH_LOG_FILE_AUDIT_UPLOAD, 'submission.event.fileAuditUploaded', array('file' => $monographFile->getOriginalFileName(), 'name' => $user->getFullName(), 'username' => $user->getUsername()));
				}
			} else {
				// Common note addition.
				if ($user->getId() !== $signoff->getUserId() &&
						array_intersect($userRoles, array(ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_SERIES_EDITOR))) {
					// If the current user is a press/series editor or assistant, open the signoff again.
					if ($signoff->getDateCompleted()) {
						$signoff->setDateCompleted(null);
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
							ASSOC_TYPE_MONOGRAPH,
							$this->_monographId
						);
					}
				}
				$successLocaleKey = 'notification.addedNote';
			}

			NotificationManager::createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __($successLocaleKey)));

			return $signoff->getId();
		}
	}
}

?>
