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

	/**
	 * Constructor
	 */
	function AnnouncementTypeListbuilderHandler() {
		parent::SetupListbuilderHandler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array('fetchOptions')
		);
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
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('announcementTypes');

		// Name column
		$nameColumn = new MultilingualListbuilderGridColumn($this, 'name', 'common.name');

		import('controllers.listbuilder.announcements.AnnouncementTypeListbuilderGridCellProvider');
		$nameColumn->setCellProvider(new AnnouncementTypeListbuilderGridCellProvider());
		$this->addColumn($nameColumn);
	}

	/**
	* @see GridHandler::loadData()
	*/
	function loadData(&$request) {
		$press =& $this->getPress();
		$announcementTypeDao =& DAORegistry::getDAO('AnnouncementTypeDAO');
		$announcementTypes =& $announcementTypeDao->getByAssoc(ASSOC_TYPE_PRESS, $press->getId());

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
		import('controllers.tab.announcements.form.AnnouncementTypeForm');
		$announcementTypeForm = new AnnouncementTypeForm();

		$rowData = $this->getNewRowId($request);
		$announcementType =& $announcementTypeForm->getAnnouncementTypeFromRowData($request, $rowData);

		return $announcementType;
	}
}

?>
