<?php

/**
 * @file controllers/modals/signoff/form/SignoffFileUploadForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	function getMonographId() {
		return $this->_monographId;
	}

	function getStageId() {
		return $this->_stageId;
	}

	function getSymbolic() {
		return $this->_symbolic;
	}

	function getSignoffId() {
		return $this->_signoffId;
	}

	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData(&$request) {
		$this->setData('monographId', $this->getMonographId());
		$this->setData('stageId', $this->getStageId());
	}


	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();
		$signoffDao =& DAORegistry::getDAO('MonographFileSignoffDAO'); /* @var $signoffDao MonographFileSignoffDAO */
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */

		import('controllers.api.file.linkAction.DownloadFileLinkAction');

		$signoffId = $this->getSignoffId();
		if ($signoffId) {
			$signoff =& $signoffDao->getById($signoffId);
		}

		// Looking at one signoff only
		if (!isset($signoff)) {
			$user =& $request->getUser();
			$signoffs =& $signoffDao->getAllByMonograph($this->getSymbolic(), $this->getMonographId(), $user->getId());
			$availableSignoffs = array();
			while ($signoff =& $signoffs->next()) {
				// Only include signoffs that are not yet completed.
				if (!$signoff->getDateCompleted()) {
					$availableSignoffs[$signoff->getId()] =& $signoff->getAssocId();

					// FIXME: just breaking out of loop to default to the first one for now.
					break;
				}
				unset($signoff);
			}
			// FIXME: code currently defaulting to first signoff off.
			// Should let user choose from all available.
//			$templateMgr->assign('availableSignoffs', $availableSignoffs);
		}

		if (isset($signoff)) {
			$templateMgr->assign('signoffId', $signoff->getId());

			$submissionFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId());
			assert(is_a($submissionFile, 'MonographFile'));

			$downloadFileAction = new DownloadFileLinkAction($request, $submissionFile);
			$templateMgr->assign('downloadFileAction', $downloadFileAction);
		}

		return parent::fetch($request);
	}

	/**
	 * @see Form::readInputData();
	 */
	function readInputData() {
		$this->readUserVars(array('signoffId', 'note', 'temporaryFileId'));
	}

	/**
	 * @see Form::validate()
	 */
	function validate(&$request) {
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
		$user =& $request->getUser();

		// Retrieve the signoff we're working with.
		$signoffDao =& DAORegistry::getDAO('MonographFileSignoffDAO');
		$signoff =& $signoffDao->getById($this->getData('signoffId'));
		assert(is_a($signoff, 'Signoff'));

		$temporaryFileId = $this->getData('temporaryFileId');
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

			import('classes.file.MonographFileManager');
			$signoffFileId = MonographFileManager::temporaryFileToMonographFile($this->getMonographId(), &$temporaryFile,
													  MONOGRAPH_FILE_SIGNOFF, $signoff->getUserId(), $signoff->getUserGroupId(),
													  $signoff->getAssocId(), null, ASSOC_TYPE_SIGNOFF,  $signoff->getId());

			// FIXME: Currently the code allows for a signoff to be added many times. (if the option is presented in the form)
			// Need to delete previous files uploaded to this signoff.
			// Partially due to #6799.

			// Mark ALL the signoffs for this user as completed with this file upload.
			$signoff->setFileId($signoffFileId);
			$signoff->setFileRevision(1);
		}
		// FIXME: insert the note
		$note = $this->getData('note');

		// Now mark the signoff as completed
		$signoff->setDateCompleted(Core::getCurrentDate());
		$signoffDao->updateObject($signoff);

		return $signoff->getId();
	}
}

?>
