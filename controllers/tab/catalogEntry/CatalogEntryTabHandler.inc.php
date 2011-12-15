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

	/**
	 * Constructor
	 */
	function CatalogEntryTabHandler() {

		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array(
						'submissionMetadata',
						'catalogMetadata',
						'publicationMetadata',
						'saveForm'
				)
		);
		parent::Handler();
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
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
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
		$catalogEntrySubmissionReviewForm = new CatalogEntrySubmissionReviewForm($request->getUserVar('monographId'), $request->getUserVar('stageId'), array('displayedInTab' => true));

		$catalogEntrySubmissionReviewForm->initData($args, $request);
		$json = new JSONMessage(true, $catalogEntrySubmissionReviewForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Show the catalog metadata form.
	 * @param $request Request
	 * @param $args array
	 * @return string JSON message
	 */
	function catalogMetadata($args, &$request) {
		$json = new JSONMessage(true, 'Catalog Metadata');
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
		$publicationFormat =& $publicationFormatDao->getById($publicationFormatId);

		$json = new JSONMessage(true, $publicationFormat->getLocalizedName());
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

		switch ($this->getCurrentTab()) {

			case 'submission':

				import('controllers.modals.submissionMetadata.form.CatalogEntrySubmissionReviewForm');
				$form = new CatalogEntrySubmissionReviewForm($request->getUserVar('monographId'), $request->getUserVar('stageId'), array('displayedInTab' => true));
				break;
			case 'catalog':
				assert(false); // placeholder
				break;
			case 'publication':
				assert(false); // placeholder
				break;
			default:
				assert(false);
		}

		if ($form) {
			$form->readInputData($request);
			if($form->validate()) {
				$form->execute($request);
				// Create trivial notification in place on the form
				$notificationManager = new NotificationManager();
				$user =& $request->getUser();
				$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.savedSubmissionMetadata')));
			} else {
				$json->setStatus(false);
			}

			if ($request->getUserVar('displayedInTab')) {
				$methodName = $this->getCurrentTab() . 'Metadata';
				return $this->{$methodName}($args, $request); // displays the notification, keeps the modal open
			} else {
				return $json->getString(); // closes the modal
			}
		}
	}

}
?>