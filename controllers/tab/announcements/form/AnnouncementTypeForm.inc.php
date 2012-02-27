<?php

/**
 * @file controllers/tab/announcements/form/AnnouncementTypeForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementTypeForm
 * @ingroup controllers_tab_announcements
 * @see AnnouncementType
 *
 * @brief Form for press managers to create/edit announcement types.
 */


import('lib.pkp.classes.form.Form');

class AnnouncementTypeForm extends Form {

	/** @var AnnouncementTypeDAO */
	var $_announcementTypeDao;

	/** @var boolean */
	var $_executeResult;

	/**
	 * Constructor
	 * @param typeId int leave as default for new announcement type
	 */
	function AnnouncementTypeForm() {
		parent::Form('controllers/tab/announcements/form/announcementTypeForm.tpl');

		$this->_announcementTypeDao =& DAORegistry::getDAO('AnnouncementTypeDAO');
		$this->_executeResult = true;
	}


	//
	// Implement template methods.
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('announcementTypes'));
	}

	/**
	 * @see Form::execute()
	 */
	function execute($request) {
		$announcementTypes = $this->getData('announcementTypes');
		ListbuilderHandler::unpack($request, $announcementTypes);

		return $this->_executeResult;
	}

	/**
	* @see ListbuilderHandler::insertEntry()
	*/
	function insertEntry($request, $newRowId) {
		$rowData = $newRowId;

		$announcementType =& $this->getAnnouncementTypeFromRowData($request, $rowData);
		$press =& $request->getPress();

		if ($this->_announcementTypeDao->getByTypeName($announcementType->getLocalizedTypeName(), ASSOC_TYPE_PRESS, $press->getId())) {
			// Create form error notification.
			$user =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification(
				$user->getId(), NOTIFICATION_TYPE_FORM_ERROR, array('contents' => array('announcementType' => __('manager.announcementTypes.form.typeNameExists')))
			);

			$this->_executeResult = false;
			return false;
		} else {
			$this->_announcementTypeDao->insertAnnouncementType($announcementType);
			return true;
		}


	}

	/**
	 * @see ListbuilderHandler::updateEntry()
	 */
	function updateEntry($request, $rowId, $newRowId) {
		$rowData = $newRowId;

		$announcementType =& $this->_announcementTypeDao->getById($rowId);
		if (!is_a($announcementType, 'AnnouncementType')) {
			assert(false);
			return false;
		}

		$announcementType =& $this->_setLocaleData($announcementType, $rowData);

		$press =& $request->getPress();
		if ($this->_announcementTypeDao->getByTypeName($announcementType->getLocalizedTypeName(), ASSOC_TYPE_PRESS, $press->getId())) {
			$this->_executeResult = false;
			return false;
		} else {
			$this->_announcementTypeDao->updateAnnouncementType($announcementType);
			return true;
		}
	}

	/**
	 * @see ListbuilderHandler::save()
	 */
	function deleteEntry($request, $rowId) {
		if ($rowId) {
			$announcementType =& $this->_announcementTypeDao->getById($rowId);
			if (!is_a($announcementType, 'AnnouncementType')) {
				assert(false);
				return false;
			}
			$this->_announcementTypeDao->deleteById($announcementType->getId());
		}
	}

	/**
	 * Get an announcement type object, with the
	 * rowData setted.
	 * @param $rowData array
	 * @return AnnouncementType
	 */
	function &getAnnouncementTypeFromRowData(&$request, $rowData) {
		$announcementTypeDao =& DAORegistry::getDAO('AnnouncementTypeDAO');
		$announcementType = $announcementTypeDao->newDataObject();
		if ($rowData) {
			$announcementType =& $this->_setLocaleData($announcementType, $rowData);
		}

		$press =& $request->getPress();

		$announcementType->setAssocType(ASSOC_TYPE_PRESS);
		$announcementType->setAssocId($press->getId());

		return $announcementType;
	}


	//
	// Private helper methods.
	//
	/**
	 * Set the localized data on announcement
	 * type object.
	 * @param $announcementType AnnouncementType
	 * @param $rowData array
	 * @return AnnouncementType
	 */
	function &_setLocaleData(&$announcementType, $rowData) {
		foreach($rowData['name'] as $locale => $data) {
			$announcementType->setName($data, $locale);
		}

		return $announcementType;
	}
}

?>
