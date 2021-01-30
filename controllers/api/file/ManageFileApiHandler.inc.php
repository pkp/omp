<?php

/**
 * @file controllers/api/file/ManageFileApiHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ManageFileApiHandler
 * @ingroup controllers_api_file
 *
 * @brief Class defining an AJAX API for file manipulation.
 */

// Import the base handler.
import('lib.pkp.controllers.api.file.PKPManageFileApiHandler');
import('lib.pkp.classes.core.JSONMessage');

class ManageFileApiHandler extends PKPManageFileApiHandler {

	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR),
			array('identifiers', 'updateIdentifiers', 'clearPubId',)
		);
	}

	/**
	 * @copydoc PKPManageFileApiHandler::editMetadata
	 */
	function editMetadata($args, $request) {
		$submissionFile = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		if ($submissionFile->getFileStage() == SUBMISSION_FILE_PROOF) {
			$publisherIdEnabled = in_array('file', (array) $request->getContext()->getData('enablePublisherId'));
			$pubIdPlugins = PluginRegistry::getPlugins('pubIds');
			$pubIdEnabled = false;
			foreach ($pubIdPlugins as $pubIdPlugin) {
				if ($pubIdPlugin->isObjectTypeEnabled('SubmissionFile', $request->getContext()->getId())) {
					$pubIdEnabled = true;
					break;
				}
			}
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('showIdentifierTab', $publisherIdEnabled || $pubIdEnabled);
		}
		return parent::editMetadata($args, $request);
	}

	/**
	 * Edit proof submission file pub ids.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function identifiers($args, $request) {
		$submissionFile = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		$stageId = $request->getUserVar('stageId');
		import('controllers.tab.pubIds.form.PublicIdentifiersForm');
		$form = new PublicIdentifiersForm($submissionFile, $stageId);
		$form->initData();
		return new JSONMessage(true, $form->fetch($request));
	}

	/**
	 * Update proof submission file pub ids.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateIdentifiers($args, $request) {
		$submissionFile = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		$stageId = $request->getUserVar('stageId');
		import('lib.pkp.controllers.tab.pubIds.form.PKPPublicIdentifiersForm');
		$form = new PKPPublicIdentifiersForm($submissionFile, $stageId);
		$form->readInputData();
		if ($form->validate()) {
			$form->execute();
			return DAO::getDataChangedEvent($submissionFile->getId());
		} else {
			return new JSONMessage(true, $form->fetch($request));
		}
	}

	/**
	 * Clear proof submission file pub id.
	 * @param $args array
	 * @param $request Request
	 * @return JSONMessage JSON object
	 */
	function clearPubId($args, $request) {
		$submissionFile = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		$stageId = $request->getUserVar('stageId');
		import('lib.pkp.controllers.tab.pubIds.form.PKPPublicIdentifiersForm');
		$form = new PKPPublicIdentifiersForm($submissionFile, $stageId);
		$form->clearPubId($request->getUserVar('pubIdPlugIn'));
		return new JSONMessage(true);
	}


	//
	// Subclassed methods
	//

	/**
	 * Get the list of notifications to be updated on metadata form submission.
	 * @return array
	 */
	protected function getUpdateNotifications() {
		$updateNotifications = parent::getUpdateNotifications();
		$updateNotifications[] = NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS;
		return $updateNotifications;
	}
}


