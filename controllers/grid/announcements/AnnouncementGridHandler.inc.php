<?php

/**
 * @file controllers/grid/announcements/AnnouncementGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementGridHandler
 * @ingroup controllers_grid_announcements
 *
 * @brief Handle announcements grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class AnnouncementGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function AnnouncementGridHandler() {
		parent::GridHandler();
	}


	//
	// Overridden template methods
	//
	/**
	 * @see GridHandler::authorize()
	 */
	function authorize($request, $args, $roleAssignments) {
		$returner = parent::authorize($request, $args, $roleAssignments);

		// Ensure announcements are enabled.
		$press =& $request::getPress();
		if (!$press->getSetting('enableAnnouncements')) {
			return false;
		}

		$announcementId = $request->getUserVar('announcementId');
		if ($announcementId) {
			// Ensure announcement is valid and for this context
			$press =& $request->getPress();
			$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
			if ($announcementDao->getAnnouncementAssocId($announcementId) != $press->getId()) {
				return false;
			}
		}

		return $returner;
	}

	/**
	 * @see GridHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load language components
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);

		// Basic grid configuration
		$this->setTitle('manager.announcements');

		// Set the no items row text
		$this->setEmptyRowText('grid.noItems');

		$press =& $request->getPress();

		// Columns
		import('controllers.grid.announcements.AnnouncementGridCellProvider');
		$announcementCellProvider = new AnnouncementGridCellProvider();
		$this->addColumn(
			new GridColumn('title',
				'manager.announcements.form.title',
				null,
				'controllers/grid/gridCell.tpl',
				$announcementCellProvider
			)
		);

		$this->addColumn(
			new GridColumn('type',
				'manager.announcements.form.typeId',
				null,
				'controllers/grid/gridCell.tpl',
				$announcementCellProvider
			)
		);

		$cellProvider = new DataObjectGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'datePosted',
				'manager.announcements.datePublish',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		$press =& $request->getPress();
		$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
		$pressAnnouncements =& $announcementDao->getAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_PRESS, $press->getId());

		return $pressAnnouncements;
	}


	//
	// Public grid actions.
	//
	/**
	 * Load and fetch the announcement form in read-only mode.
	 * @param $args array
	 * @param $request Request
	 * @return string
	 */
	function moreInformation($args, &$request) {
		$announcementId = (int)$request->getUserVar('announcementId');

		import('controllers.grid.announcements.form.AnnouncementForm');
		$announcementForm = new AnnouncementForm($announcementId, true);

		$announcementForm->initData($args, $request);

		$json = new JSONMessage(true, $announcementForm->fetch($request));
		return $json->getString();
	}
}

?>
