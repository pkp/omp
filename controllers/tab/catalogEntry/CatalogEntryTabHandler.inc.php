<?php

/**
 * @file controllers/tab/catalogEntry/CatalogEntryTabHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryTabHandler
 * @ingroup controllers_tab_catalogEntry
 *
 * @brief Handle AJAX operations for tabs on the New Catalog Entry management page.
 */

// Import the base Handler.
import('classes.handler.Handler');

class CatalogEntryTabHandler extends Handler {


	/** @var string */
	var $_currentTab;

	/** @var Monograph object */
	var $_monograph;

	/** @var int stageId */
	var $_stageId;

	/** @var int */
	var $_tabPosition;

	/**
	 * Constructor
	 */
	function CatalogEntryTabHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array(
				'submissionMetadata',
				'catalogMetadata',
				'publicationMetadata',
				'saveForm',
				'uploadCoverImage',
			)
		);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the current tab name.
	 * @return string
	 */
	function getCurrentTab() {
		return $this->_currentTab;
	}

	/**
	 * Set the current tab name.
	 * @param $currentTab string
	 */
	function setCurrentTab($currentTab) {
		$this->_currentTab = $currentTab;
	}


	//
	// Extended methods from Handler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		$this->setCurrentTab($request->getUserVar('tab'));
		$this->_monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$this->_stageId =& $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$this->_tabPosition = (int) $request->getUserVar('tabPos');

		AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_SUBMISSION);
		$this->setupTemplate();
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = (int) $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Show the original submission metadata form.
	 * @param $request Request
	 * @param $args array
	 * @return string JSON message
	 */
	function submissionMetadata($args, &$request) {

		import('controllers.modals.submissionMetadata.form.CatalogEntrySubmissionReviewForm');

		$monograph =& $this->getMonograph();
		$stageId =& $this->getStageId();

		$catalogEntrySubmissionReviewForm = new CatalogEntrySubmissionReviewForm($monograph->getId(), $stageId, array('displayedInTab' => true));

		$catalogEntrySubmissionReviewForm->initData($args, $request);
		$json = new JSONMessage(true, $catalogEntrySubmissionReviewForm->fetch($request));
		return $json->getString();
	}

	/**
	 * @return the authorized monograph for this handler
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * @return the authorized workflow stage id for this handler
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * @return the current tab position
	 */
	function getTabPosition() {
		return $this->_tabPosition;
	}

	/**
	 * Show the catalog metadata form.
	 * @param $request Request
	 * @param $args array
	 * @return string JSON message
	 */
	function catalogMetadata($args, &$request) {
		import('controllers.tab.catalogEntry.form.CatalogEntryCatalogMetadataForm');

		$monograph =& $this->getMonograph();
		$stageId =& $this->getStageId();
		$user =& $request->getUser();

		$catalogEntryCatalogMetadataForm = new CatalogEntryCatalogMetadataForm($monograph->getId(), $user->getId(), $stageId, array('displayedInTab' => true));

		$catalogEntryCatalogMetadataForm->initData($args, $request);
		$json = new JSONMessage(true, $catalogEntryCatalogMetadataForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Show the publication metadata form.
	 * @param $request Request
	 * @param $args array
	 * @return string JSON message
	 */
	function publicationMetadata($args, &$request) {

		$assignedPublicationFormatId = (int) $request->getUserVar('assignedPublicationFormatId');
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');

		$monograph =& $this->getMonograph();
		$stageId =& $this->getStageId();

		$enabledPressFormats =& $publicationFormatDao->getEnabledByPressId($monograph->getPressId());
		$publicationFormat =& $assignedPublicationFormatDao->getById($assignedPublicationFormatId);

		while ($format =& $enabledPressFormats->next()) {
			if ($format->getId() == $publicationFormat->getId()) { // belongs to current press (and is enabled)
				import('controllers.tab.catalogEntry.form.CatalogEntryPublicationMetadataForm');
				$catalogEntryPublicationMetadataForm = new CatalogEntryPublicationMetadataForm($monograph, $assignedPublicationFormatId, $format->getId(), $stageId, array('displayedInTab' => true, 'tabPos' => $this->getTabPosition()));
				$catalogEntryPublicationMetadataForm->initData($args, $request);
				$json = new JSONMessage(true, $catalogEntryPublicationMetadataForm->fetch($request));
				return $json->getString();
			}
		}

		$json = new JSONMessage(false);
		return $json->getString();
	}

	/**
	 * Save the forms handled by this Handler.
	 * @param $request Request
	 * @param $args array
	 * @return string JSON message
	 */
	function saveForm($args, &$request) {
		$json = new JSONMessage();
		$form = null;

		$monograph =& $this->getMonograph();
		$stageId =& $this->getStageId();
		$notificationKey = null;

		switch ($this->getCurrentTab()) {

			case 'submission':
				import('controllers.modals.submissionMetadata.form.CatalogEntrySubmissionReviewForm');
				$form = new CatalogEntrySubmissionReviewForm($monograph->getId(), $stageId, array('displayedInTab' => true));
				$notificationKey = 'notification.savedSubmissionMetadata';
				break;
			case 'catalog':
				import('controllers.tab.catalogEntry.form.CatalogEntryCatalogMetadataForm');
				$user =& $request->getUser();
				$form = new CatalogEntryCatalogMetadataForm($monograph->getId(), $user->getId(), $stageId, array('displayedInTab' => true, 'tabPos' => $this->getTabPosition()));
				$notificationKey = 'notification.savedCatalogMetadata';
				break;
			default: // publication format tabs
				import('controllers.tab.catalogEntry.form.CatalogEntryPublicationMetadataForm');
				$assignedPublicationFormatId =& $request->getUserVar('assignedPublicationFormatId');

				// perform some validation to make sure this format is enabled and assigned to this monograph
				$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
				$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
				$publishedMonograph =& $publishedMonographDao->getById($monograph->getId());
				$formats =& $assignedPublicationFormatDao->getFormatsByMonographId($monograph->getId());
				$form = null;
				while ($format =& $formats->next()) {
					if ($format->getAssignedPublicationFormatId() == $assignedPublicationFormatId) {
						$form = new CatalogEntryPublicationMetadataForm($monograph->getId(), $assignedPublicationFormatId, $format->getId(), $stageId, array('displayedInTab' => true, 'tabPos' => $this->getTabPosition()));
						$notificationKey = 'notification.savedPublicationFormatMetadata';
						break;
					}
				}
				break;
		}

		if ($form) { // null if we didn't have a valid tab
			$form->readInputData($request);
			if($form->validate()) {
				$form->execute($request);
				// Create trivial notification in place on the form
				$notificationManager = new NotificationManager();
				$user =& $request->getUser();
				$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __($notificationKey)));
			} else {
				// Could not validate; redisplay the form.
				$json->setStatus(true);
				$json->setContent($form->fetch($request));
			}

			if ($request->getUserVar('displayedInTab')) {
				$router =& $request->getRouter();
				$dispatcher =& $router->getDispatcher();
				$url = $dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.submissionMetadata.CatalogEntryHandler', 'fetch', null, array('monographId' => $monograph->getId(), 'stageId' => $stageId, 'tabPos' => $this->getTabPosition()));
				$json->setAdditionalAttributes(array('reloadTabs' => true, 'tabsUrl' => $url));
				$json->setContent(true); // prevents modal closure
				return $json->getString();
			} else {
				return $json->getString(); // closes the modal
			}
		} else {
			fatalError('Unknown or unassigned format id!');
		}
	}

	/**
	 * Upload a new cover image file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function uploadCoverImage($args, &$request) {
		$router =& $request->getRouter();
		$context = $request->getContext();
		$user =& $request->getUser();

		$monograph =& $this->getMonograph();

		import('classes.file.TemporaryFileManager');
		$temporaryFileManager = new TemporaryFileManager();
		$temporaryFile = $temporaryFileManager->handleUpload('uploadedFile', $user->getId());
		if ($temporaryFile) {
			$json = new JSONMessage(true);
			$json->setAdditionalAttributes(array(
				'temporaryFileId' => $temporaryFile->getId()
			));
		} else {
			$json = new JSONMessage(false, __('common.uploadFailed'));
		}

		return $json->getString();
	}
}

?>
