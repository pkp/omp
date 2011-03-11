<?php

/**
 * @file controllers/wizard/fileUpload/form/SubmissionFilesUploadForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadForm
 * @ingroup controllers_wizard_fileUpload_form
 *
 * @brief Form for adding/editing a submission file
 */


import('controllers.wizard.fileUpload.form.SubmissionFilesUploadBaseForm');

class SubmissionFilesUploadForm extends SubmissionFilesUploadBaseForm {

	/** @var array */
	var $_uploaderRoles;


	/**
	 * Constructor.
	 * @param $request Request
	 * @param $monographId integer
	 * @param $uploaderRoles array
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $fileStage integer
	 * @param $revisionOnly boolean
	 * @param $reviewType integer
	 * @param $round integer
	 * @param $revisedFileId integer
	 */
	function SubmissionFilesUploadForm(&$request, $monographId, $stageId, $uploaderRoles, $fileStage,
			$revisionOnly = false, $reviewType = null, $round = null, $revisedFileId = null) {

		// Initialize class.
		assert(is_null($uploaderRoles) || (is_array($uploaderRoles) && count($uploaderRoles) > 1));
		$this->_uploaderRoles = $uploaderRoles;

		parent::SubmissionFilesUploadBaseForm(
			$request, 'controllers/wizard/fileUpload/form/fileUploadForm.tpl',
			$monographId, $stageId, $fileStage, $revisionOnly, $reviewType, $round, $revisedFileId
		);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the uploader roles.
	 * @return array
	 */
	function getUploaderRoles() {
		assert(!is_null($this->_uploaderRoles));
		return $this->_uploaderRoles;
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('genreId', 'uploaderUserGroupId'));
		return parent::readInputData();
	}

	/**
	 * @see Form::validate()
	 */
	function validate(&$request) {
		// Is this a revision?
		$revisedFileId = $this->getRevisedFileId();
		if ($this->getData('revisionOnly')) {
			assert($revisedFileId > 0);
		}

		// Retrieve the request context.
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		if (!$revisedFileId) {
			// Add an additional check for the genre to the form.
			$this->addCheck(
				new FormValidatorCustom(
					$this, 'genreId', FORM_VALIDATOR_REQUIRED_VALUE,
					'submission.upload.noFileType',
					create_function(
						'$genreId,$genreDao,$context',
						'return is_a($genreDao->getById($genreId, $context->getId()), "Genre");'
					),
					array(DAORegistry::getDAO('GenreDAO'), $context)
				)
			);
		}

		// Validate the uploader's user group.
		$uploaderUserGroupId = $this->getData('uploaderUserGroupId');
		if ($uploaderUserGroupId) {
			$user =& $request->getUser();
			$this->addCheck(
				new FormValidatorCustom(
					$this, 'uploaderUserGroupId', FORM_VALIDATOR_REQUIRED_VALUE,
					'submission.upload.invalidUserGroup',
					create_function(
						'$userGroupId,$userGroupDao,$userId,$context',
						'return $userGroupDao->userInGroup($context->getId(), $userId, $userGroupId);'
					),
					array(DAORegistry::getDAO('UserGroupDAO'), $user->getId(), $context)
				)
			);
		}

		return parent::validate();
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		// Retrieve available monograph file genres.
		$genreList =& $this->_retrieveGenreList($request);
		$this->setData('monographFileGenres', $genreList);

		// Retrieve the current context.
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		assert(is_a($context, 'Press'));

		// Retrieve the user's user groups.
		$user =& $request->getUser();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$assignedUserGroups =& $userGroupDao->getByUserId($user->getId(), $context->getId());

		// Check which of these groups make sense in the context
		// from which the uploader was instantiated.
		$uploaderRoles = $this->getUploaderRoles();
		$uploaderUserGroups = array();
		while($userGroup =& $assignedUserGroups->next()) { /* @var $userGroup UserGroup */
			if (in_array($userGroup->getRoleId(), $uploaderRoles)) {
				$uploaderUserGroups[$userGroup->getId()] = $userGroup->getLocalizedName();
			}
			unset($userGroup);
		}
		if (empty($uploaderUserGroups)) fatalError('Invalid uploader roles!');
		$this->setData('uploaderUserGroups', $uploaderUserGroups);

		// Identify the default user group (only required when there is
		// more than one group).
		$defaultUserGroupId = null;
		if (count($uploaderUserGroups) > 1) {
			// See whether the current user has been assigned as
			// a workflow stage participant.
			$stageId = $this->getStageId();
			$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
			$stageSignoffs =& $signoffDao->getAllBySymbolic(
				'SIGNOFF_STAGE',
				ASSOC_TYPE_MONOGRAPH, $this->getData('monographId'),
				$user->getId(), $stageId
			);
			while($stageSignoff =& $stageSignoffs->next()) { /* @var $stageSignoff Signoff */
				if (isset($uploaderUserGroups[$stageSignoff->getUserGroupId()])) {
					$defaultUserGroupId = $stageSignoff->getUserGroupId();
					break;
				}
			}

			// If we didn't find a corresponding stage signoff then
			// use the first group with the lowest role id as default.
			if (is_null($defaultUserGroupId)) {
				foreach($uploaderUserGroups as $uploaderUserGroup) { /* @var $uploaderUserGroup UserGroup */
					if (is_null($defaultUserGroupId) || $uploaderUserGroup->getRoleId() < $minRoleId) {
						$minRoleId = $uploaderUserGroup->getRoleId();
						$defaultUserGroupId = $uploaderUserGroup->getId();
					}
				}
			}
		}
		$this->setData('defaultUserGroupId', $defaultUserGroupId);

		return parent::fetch($request);
	}

	/**
	 * @see Form::execute()
	 * @return MonographFile if successful, otherwise null
	 */
	function &execute() {
		// Identify the file genre.
		$revisedFileId = $this->getRevisedFileId();
		if ($revisedFileId) {
			// The file genre will be copied over from the revised file.
			$fileGenre = null;
		} else {
			// This is a new file so we need the file genre from the form.
			$fileGenre = $this->getData('genreId') ? (int)$this->getData('genreId') : null;
		}

		// Retrieve the uploader's user group.
		$uploaderUserGroupId = $this->getData('uploaderUserGroupId');
		if (!$uploaderUserGroupId) fatalError('Invalid uploader user group!');

		// Upload the file.
		import('classes.file.MonographFileManager');
		return MonographFileManager::uploadMonographFile(
			$this->getData('monographId'), 'uploadedFile',
			$this->getData('fileStage'), $uploaderUserGroupId,
			$revisedFileId, $fileGenre
		);
	}


	//
	// Private helper methods
	//
	/**
	 * Retrieve the genre list.
	 * @param $request Request
	 * @return array
	 */
	function &_retrieveGenreList(&$request) {
		$context =& $request->getContext();
		$genreDao =& DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */
		$genres =& $genreDao->getEnabledByPressId($context->getId());

		// Transform the genres into an array and
		// assign them to the form.
		$genreList = array();
		while($genre =& $genres->next()){
			$genreId = $genre->getId();
			$genreList[$genreId] = $genre->getLocalizedName();
			unset($genre);
		}
		return $genreList;
	}
}

?>
