<?php

/**
 * @file controllers/tab/catalogEntry/CatalogEntryTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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

	/**
	 * Constructor
	 */
	function CatalogEntryTabHandler() {
		parent::Handler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array(
						'submissionMetadata',
						'catalogMetadata',
						'publicationMetadata',
						'saveForm'
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
	 * Show the catalog metadata form.
	 * @param $request Request
	 * @param $args array
	 * @return string JSON message
	 */
	function catalogMetadata($args, &$request) {
		import('controllers.modals.submissionMetadata.form.CatalogEntryCatalogMetadataForm');

		$monograph =& $this->getMonograph();
		$stageId =& $this->getStageId();

		$catalogEntryCatalogMetadataForm = new CatalogEntryCatalogMetadataForm($monograph->getId(), $stageId, array('displayedInTab' => true));

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

		$publicationFormatId =& $request->getUserVar('publicationFormatId');
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');

		$monograph =& $this->getMonograph();

		$enabledPressFormats =& $publicationFormatDao->getEnabledByPressId($monograph->getPressId());
		$publicationFormat =& $publicationFormatDao->getById($publicationFormatId);

		while ($format =& $enabledPressFormats->next()) {
			if ($format->getId() == $publicationFormat->getId()) { // belongs to current press
				$json = new JSONMessage();
				$json->setContent($publicationFormat->getLocalizedName());
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
				import('controllers.modals.submissionMetadata.form.CatalogEntryCatalogMetadataForm');
				$form = new CatalogEntryCatalogMetadataForm($monograph->getId(), $stageId, array('displayedInTab' => true));
				$notificationKey = 'notification.savedCatalogMetadata';
				break;
			case 'publication':
				assert(false); // placeholder
				break;
			default:
				fatalError('Invalid Tab');
		}

		if ($form) {
			$form->readInputData($request);
			if($form->validate()) {
				$form->execute($request);
				// Create trivial notification in place on the form
				$notificationManager = new NotificationManager();
				$user =& $request->getUser();
				$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __($notificationKey)));
			} else {
				$json->setStatus(false);
			}

			if ($request->getUserVar('displayedInTab')) {
				$router =& $request->getRouter();
				$dispatcher =& $router->getDispatcher();
				$url = $dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.submissionMetadata.CatalogEntryHandler', 'fetch', null, array('monographId' => $monograph->getId(), 'stageId' => $stageId));
				$json->setAdditionalAttributes(array('reloadTabs' => true, 'tabsUrl' => $url));
				$json->setContent(true); // prevents modal closure
				return $json->getString();
			} else {
				return $json->getString(); // closes the modal
			}
		}
	}

}
?>