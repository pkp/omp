<?php

/**
 * @file controllers/grid/content/navigation/ManageSocialMediaGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageSocialMediaGridHandler
 * @ingroup controllers_grid_content_navigation
 *
 * @brief Handle social media grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import grid specific classes
import('controllers.grid.content.navigation.SocialMediaGridCellProvider');
import('controllers.grid.content.navigation.SocialMediaGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class ManageSocialMediaGridHandler extends GridHandler {
	/** @var Press */
	var $_press;

	/**
	 * Constructor
	 */
	function ManageSocialMediaGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
			array(
				'fetchGrid', 'fetchRow', 'addMedia',
				'editMedia', 'updateMedia', 'deleteMedia'
			)
		);
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the press associated with this grid.
	 * @return Press
	 */
	function &getPress() {
		return $this->_press;
	}

	/**
	 * Set the Press
	 * @param Press
	 */
	function setPress($press) {
		$this->_press =& $press;
	}


	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Retrieve the authorized press.
		$this->setPress($request->getPress());

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS
		);

		// Basic grid configuration
		$this->setTitle('grid.content.navigation.socialMedia');

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addMedia',
				new AjaxModal(
					$router->url($request, null, null, 'addMedia', null, $actionArgs),
					__('grid.content.navigation.socialMedia.addSocialLink'),
					'addMedia'
				),
				__('grid.content.navigation.socialMedia.addSocialLink'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new SocialMediaGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'platform',
				'grid.content.navigation.socialMedia.platform',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider,
				array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return SocialMediaGridRow
	 */
	function &getRowInstance() {
		$press =& $this->getPress();
		$row = new SocialMediaGridRow($press);
		return $row;
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * @return array
	 */
	function getRequestArgs() {
		$press =& $this->getPress();
		return array(
			'pressId' => $press->getId()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData($request, $filter = null) {
		$press =& $this->getPress();
		$socialMediaDao =& DAORegistry::getDAO('SocialMediaDAO');
		$data =& $socialMediaDao->getByPressId($press->getId());
		return $data->toArray();
	}


	//
	// Public Grid Actions
	//

	function addMedia($args, $request) {
		return $this->editMedia($args, $request);
	}

	/**
	 * Edit a social media entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editMedia($args, &$request) {
		// Identify the object to be updated
		$socialMediaId = (int) $request->getUserVar('socialMediaId');
		$press =& $this->getPress();

		$socialMediaDao =& DAORegistry::getDAO('SocialMediaDAO');
		$socialMedia =& $socialMediaDao->getById($socialMediaId, $press->getId());

		// Form handling
		import('controllers.grid.content.navigation.form.SocialMediaForm');
		$socialMediaForm = new SocialMediaForm($press->getId(), $socialMedia);
		$socialMediaForm->initData();

		$json = new JSONMessage(true, $socialMediaForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Edit a social media entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateMedia($args, &$request) {
		// Identify the object to be updated
		$socialMediaId = (int) $request->getUserVar('socialMediaId');
		$press =& $this->getPress();

		$socialMediaDao =& DAORegistry::getDAO('SocialMediaDAO');
		$socialMedia = $socialMediaDao->getById($socialMediaId, $press->getId());

		// Form handling
		import('controllers.grid.content.navigation.form.SocialMediaForm');
		$socialMediaForm = new SocialMediaForm($press->getId(), $socialMedia);
		$socialMediaForm->readInputData();
		if ($socialMediaForm->validate()) {
			$socialMediaId = $socialMediaForm->execute($request);

			if(!isset($socialMedia)) {
				// This is a new media object
				$socialMedia =& $socialMediaDao->getById($socialMediaId, $press->getId());
				// New added media action notification content.
				$notificationContent = __('notification.addedSocialMedia');
			} else {
				// Media edit action notification content.
				$notificationContent = __('notification.editedSocialMedia');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($socialMediaId);
			$row->setData($socialMedia);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			$json = new JSONMessage(true, $socialMediaForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a media entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteMedia($args, &$request) {

		// Identify the object to be deleted
		$socialMediaId = (int) $request->getUserVar('socialMediaId');

		$press =& $this->getPress();

		$socialMediaDao =& DAORegistry::getDAO('SocialMediaDAO');
		$socialMedia =& $socialMediaDao->getById($socialMediaId, $press->getId());
		if (isset($socialMedia)) {
			$result = $socialMediaDao->deleteObject($socialMedia);
			if ($result) {
				$currentUser =& $request->getUser();
				$notificationMgr = new NotificationManager();
				$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedSocialMedia')));
				return DAO::getDataChangedEvent();
			} else {
				$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
				return $json->getString();
			}
		} else {
			fatalError('Social Media not in current press context.');
		}
	}
}

?>
