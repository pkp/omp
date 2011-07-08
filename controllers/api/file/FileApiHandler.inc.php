<?php
/**
 * @defgroup controllers_api_file
 */

/**
 * @file controllers/api/file/FileApiHandler.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileApiHandler
 * @ingroup controllers_api_file
 *
 * @brief Class defining an AJAX API for file manipulation.
 */

// Import the base handler.
import('classes.file.FileManagementHandler');
import('lib.pkp.classes.core.JSONMessage');

class FileApiHandler extends FileManagementHandler {

	/**
	 * Constructor.
	 */
	function FileApiHandler() {
		parent::FileManagementHandler();
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR),
				array('deleteFile', 'downloadFile', 'viewFile'));
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
		// FIXME: authorize! bug #6199
		$fileId = (int)$request->getUserVar('fileId');

		$success = false;
		if($fileId) {
			// Delete all revisions or only one?
			$revision = $request->getUserVar('revision')? (int)$request->getUserVar('revision') : null;

			// Delete the file/revision but only when it belongs to the authorized monograph
			// and to the right file stage.
			$monograph =& $this->getMonograph();
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			if ($revision) {
				$success = (boolean)$submissionFileDao->deleteRevisionById($fileId, $revision, $this->getFileStage(), $monograph->getId());
			} else {
				$success = (boolean)$submissionFileDao->deleteAllRevisionsById($fileId, $this->getFileStage(), $monograph->getId());
			}
		}

		if ($success) {
			return DAO::getDataChangedEvent($fileId);
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}

	/**
	 * Download a file.
	 * @param $args array
	 * @param $request Request
	 */
	function downloadFile($args, &$request) {
		// FIXME: authorize! bug #6199
		$fileId = (int)$request->getUserVar('fileId');
		$revision = (int)$request->getUserVar('fileRevision');

		$monograph =& $this->getMonograph();
		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFile($monograph->getId(), $fileId, ($revision ? $revision : null));
	}

	/**
	 * View a file.
	 * @param $args array
	 * @param $request Request
	 */
	function viewFile($args, &$request) {
		// FIXME: authorize! bug #6199
		$fileId = (int)$request->getUserVar('fileId');
		$revision = (int)$request->getUserVar('fileRevision');

		$monograph =& $this->getMonograph();
		import('classes.file.MonographFileManager');
		MonographFileManager::viewFile($monograph->getId(), $fileId, ($revision ? $revision : null));
	}
}

?>
