<?php

/**
 * @file controllers/listbuilder/settings/NavBarListbuilder.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InternalReviewRolesListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for editing external review roles
 */

import('controllers.listbuilder.settings.SetupListbuilderHandler');

class ExternalReviewRolesListbuilderHandler extends SetupListbuilderHandler {
	/**
	 * Constructor
	 */
	function ExternalReviewRolesListbuilderHandler() {
		parent::SetupListbuilderHandler();

	}

	/* Load the list from an external source into the listbuilder structure */
	function loadList(&$request) {
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		$press =& $request->getPress();

		// Get items to populate listBuilder current item list
		$roles = $flexibleRoleDao->getByArrangementId(FLEXIBLE_ROLE_ARRANGEMENT_EXTERNAL_REVIEW, $press->getId());

		$items = array();
		foreach($roles as $item) {
			$id = $item->getId();
			$items[$id] = array('item' => $item->getLocalizedName(), 'attribute' => $item->getLocalizedDesignation());
		}

		$this->setData($items);
	}


	/* Get possible items to populate drop-down list with */
	function getPossibleItemList() {
		return $this->possibleItems;
	}

	/* Get possible items to populate drop-down list with */
	function loadPossibleItemList(&$request) {
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		$press =& $request->getPress();

		// Get items to populate possible items list with
		$currentRoleIds = $flexibleRoleDao->getIdsByArrangementId(FLEXIBLE_ROLE_ARRANGEMENT_EXTERNAL_REVIEW, $press->getId()); // Don't include current roles

		$itemList = array();
		$availableRoles = $flexibleRoleDao->getEnabledByPressId($press->getId());
		foreach ($availableRoles as $availableRole) {
			if ($availableRole->getType() == FLEXIBLE_ROLE_CLASS_PRESS && !in_array($availableRole->getId(), $currentRoleIds)) {
				$itemList[] = $this->_buildListItemHTML($availableRole->getId(), $availableRole->getLocalizedName(), $availableRole->getLocalizedDesignation());
			}
		}

		$this->possibleItems = $itemList;
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic configuration
		$this->setTitle('manager.setup.externalReviewRoles');
		$this->setSourceTitle('manager.setup.availableRoles');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT); //Select from drop-down list
		$this->setListTitle('manager.setup.currentRoles');

		// Add grid-level actions
		$router =& $request->getRouter();

		$this->loadPossibleItemList($request);
		$this->loadList($request);

		$this->addColumn(new GridColumn('item', 'common.name'));
		$this->addColumn(new GridColumn('attribute', 'common.designation'));
	}

	//
	// Public AJAX-accessible functions
	//

	/*
	 * Handle adding an item to the list
	 */
	function addItem(&$args, &$request) {
		$this->setupTemplate();
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');
		$press =& $request->getPress();

		$flexibleRoleId = (int) array_shift($args);

		if(empty($flexibleRoleId)) {
			$json = new JSON('false', Locale::translate('common.listbuilder.selectValidOption'));
			echo $json->getString();
		} else {
			$flexibleRole =& $flexibleRoleDao->getById($flexibleRoleId);

			// FIXME: Make sure associated series doesn't already exist, else return an error modal

			$flexibleRole->addAssociatedArrangement(FLEXIBLE_ROLE_ARRANGEMENT_EXTERNAL_REVIEW);
			$flexibleRoleDao->updateObject($flexibleRole);

			// Return JSON with formatted HTML to insert into list
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($flexibleRoleId);
			$rowData = array('item' => $flexibleRole->getLocalizedName(), 'attribute' => $flexibleRole->getLocalizedDesignation());
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
			echo $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 */
	function deleteItems(&$args, &$request) {
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		foreach($args as $flexibleRoleId) {
			$flexibleRole =& $flexibleRoleDao->getById($flexibleRoleId);

			$flexibleRole->removeAssociatedArrangement(FLEXIBLE_ROLE_ARRANGEMENT_EXTERNAL_REVIEW);
			$flexibleRoleDao->updateObject($flexibleRole);

			unset($flexibleRole);
		}

		$json = new JSON('true');
		echo $json->getString();
	}

}
?>
