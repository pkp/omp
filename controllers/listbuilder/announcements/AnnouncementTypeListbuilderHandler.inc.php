<?php

/**
 * @file controllers/listbuilder/announcements/AnnouncementTypeListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementTypeListbuilderHandler
 * @ingroup listbuilder_announcements
 *
 * @brief Class for managing announcements types.
 */

import('controllers.listbuilder.settings.SetupListbuilderHandler');

class AnnouncementTypeListbuilderHandler extends SetupListbuilderHandler {

	/** @var AnnouncementTypeDAO */
	var $_announcementTypeDao;

	/**
	 * Constructor
	 */
	function AnnouncementTypeListbuilderHandler() {
		parent::SetupListbuilderHandler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array('fetchOptions')
		);

		$this->_announcementTypeDao =& DAORegistry::getDAO('AnnouncementTypeDAO');
	}


	//
	// Overridden template methods
	//
	/**
	 * @see SetupListbuilderHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);

		// Basic configuration
		$this->setTitle('manager.announcementTypes');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT);
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_INTERNAL);

		// Name column
		$availableLocales = AppLocale::getSupportedFormLocales();
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name', null, null, null,
			array('multilingual' => true, 'availableLocales' => $availableLocales));

		import('controllers.listbuilder.announcements.AnnouncementTypeListbuilderGridCellProvider');
		$nameColumn->setCellProvider(new AnnouncementTypeListbuilderGridCellProvider());
		$this->addColumn($nameColumn);
	}

	/**
	* @see GridHandler::loadData()
	*/
	function loadData(&$request) {
		$press =& $this->getPress();
		$announcementTypes =& $this->_announcementTypeDao->getAnnouncementTypesByAssocId(ASSOC_TYPE_PRESS, $press->getId());

		return $announcementTypes;
	}

	/**
	* @see GridHandler::getRowDataElement
	* Get the data element that corresponds to the current request
	* Allow for a blank $rowId for when creating a not-yet-persisted row
	*/
	function getRowDataElement(&$request, $rowId) {
		// fallback on the parent if a rowId is found
		if ( !empty($rowId) ) {
			return parent::getRowDataElement($request, $rowId);
		}

		// Otherwise return from the $newRowId
		$rowData = $this->getNewRowId($request);
		$announcementType =& $this->_getAnnouncementTypeFromRowData($request, $rowData);

		return $announcementType;
	}

	/**
	 * @see ListbuilderHandler::insertEntry()
	 */
	function insertEntry($request, $newRowId) {
		$rowData = $newRowId;

		$announcementType =& $this->_getAnnouncementTypeFromRowData($request, $rowData);

		$this->_announcementTypeDao->insertAnnouncementType($announcementType);
	}

	/**
	 * @see ListbuilderHandler::updateEntry()
	 */
	function updateEntry($request, $rowId, $newRowId) {
		$rowData = $newRowId;

		$announcementType =& $this->getRowDataElement($request, $rowId);
		$announcementType =& $this->_setLocaleData($announcementType, $rowData);

		$this->_announcementTypeDao->updateAnnouncementType($announcementType);
	}

	/**
	 * @see ListbuilderHandler::save()
	 */
	function deleteEntry($request, $rowId) {
		$this->_announcementTypeDao->deleteAnnouncementTypeById($rowId);
	}


	//
	// Private helper methods.
	//
	/**
	 * Get an announcement type object, with the
	 * rowData setted.
	 * @param $rowData array
	 * @return AnnouncementType
	 */
	function &_getAnnouncementTypeFromRowData(&$request, $rowData) {
		$announcementType = new AnnouncementType();
		$announcementType =& $this->_setLocaleData($announcementType, $rowData);

		$press =& $request->getPress();

		$announcementType->setAssocType(ASSOC_TYPE_PRESS);
		$announcementType->setAssocId($press->getId());

		return $announcementType;
	}

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
