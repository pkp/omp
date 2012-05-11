<?php

/**
 * @file controllers/api/file/ManageFileApiHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageFileApiHandler
 * @ingroup controllers_api_file
 *
 * @brief Class defining an AJAX API for file manipulation.
 */

// Import the base handler.
import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class ManageFileApiHandler extends Handler {

	/**
	 * Constructor.
	 */
	function ManageFileApiHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR),
			array('deleteFile')
		);
	}


	//
	// Implement methods from PKPHandler
	//
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpMonographFileAccessPolicy');
		$this->addPolicy(new OmpMonographFileAccessPolicy($request, $args, $roleAssignments, MONOGRAPH_FILE_ACCESS_MODIFY));

		return parent::authorize($request, $args, $roleAssignments);
	}

	//
	// Public handler methods
	//
	/**
	 * Delete a file or revision
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function deleteFile($args, &$request) {
		$monographFile =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH_FILE);
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		assert($monographFile && $monograph); // Should have been validated already

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$notes =& $noteDao->getByAssoc(ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileId());
		while ($note =& $notes->next()) {
			$noteDao->deleteById($note->getId());
			unset($note);
		}

		// Delete all signoffs related with this file.
		$signoffDao = DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoffFactory =& $signoffDao->getAllByAssocType(ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileId());
		$signoffs = $signoffFactory->toArray();

		foreach ($signoffs as $signoff) {
			$signoffDao->deleteObject($signoff);

			// Update NOTIFICATION_TYPE_AUDITOR_REQUEST.
			$notificationMgr = new NotificationManager();
			$notificationMgr->updateAuditorRequestNotification($signoff, $request, true);

			// Update NOTIFICATION_TYPE_SIGNOFF_...
			$notificationMgr->updateSignoffNotification($signoff, $request);

			// Remove the notification for the Copyeditor review, if they exist.
			$notificationMgr->deleteCopyeditRequestNotification($signoff, &$request);
		}

		// Delete the monograph file.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$success = (boolean)$submissionFileDao->deleteRevisionById($monographFile->getFileId(), $monographFile->getRevision(), $monographFile->getFileStage(), $monograph->getId());

		if ($success) {
			// update the monograph's search index if this was a proof file
			if ($monographFile->getFileStage() == MONOGRAPH_FILE_PROOF) {
				if ($monograph->getDatePublished()) {
					import('classes.search.MonographSearchIndex');
					MonographSearchIndex::indexMonographFiles($monograph);
				}
			}
			import('classes.file.MonographFileManager');
			$monographFileManager = new MonographFileManager($monograph->getPressId(), $monograph->getId());
			$monographFileManager->deleteFile($monographFile->getFileId(), $monographFile->getRevision());
			$this->setupTemplate();
			$user =& $request->getUser();
			NotificationManager::createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedFile')));

			return DAO::getDataChangedEvent($monographFile->getFileId());
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}
}

?>
