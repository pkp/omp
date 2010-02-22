<?php

/**
 * @file controllers/listbuilder/setup/PublicationFormatsListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatsListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding new publication formats
 */

import('controllers.listbuilder.ListbuilderHandler');

class BookFileTypesListbuilderHandler extends ListbuilderHandler {
	/** @var boolean internal state variable, true if row handler has been instantiated */
	var $_rowInstantiated = false;

	/**
	 * Constructor
	 */
	function BookFileTypesListbuilderHandler() {
		parent::ListbuilderHandler();
	}


	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$press =& $request->getPress();

		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$bookFileTypes = $bookFileTypeDao->getEnabledByPressId($press->getId());

		$items = array();
		foreach($bookFileTypes as $item) {
			$id = $item->getId();
			$items[$id] = array('item' => $item->getLocalizedName(), 'attribute' => $item->getLocalizedDesignation());
		}
		$this->setData($items);
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
		$this->setTitle('manager.setup.bookFileTypes');
		$this->setSourceTitle('common.name');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input
		$this->setListTitle('manager.setup.publicationFormats');
		$this->setAttributeNames(array('common.designation'));

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
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$press =& $request->getPress();

		$nameIndex = 'sourceTitle-' . $this->getId();
		$bookFileName = $args[$nameIndex];
		$abbrevIndex = 'attribute-1-' . $this->getId();
		$bookFileDesignation = $args[$abbrevIndex];

		if(empty($bookFileName) || empty($bookFileDesignation)) {
			$json = new JSON('false', Locale::translate('common.listbuilder.completeForm'));
			echo $json->getString();
		} else {
			$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');

			// Make sure the role name or abbreviation doesn't already exist
			$bookFileTypes = $bookFileTypeDao->getEnabledByPressId($press->getId());
			foreach ($bookFileTypes as $bookFileType) {
				if ($bookFileName == $bookFileType->getLocalizedName() || $bookFileDesignation == $bookFileType->getLocalizedDesignation()) {
					$json = new JSON('false', Locale::translate('common.listbuilder.itemExists'));
					echo $json->getString();
					return false;
				}
			}

			$bookFileType =& new BookFileType();

			$bookFileType->setName($bookFileName, Locale::getLocale());
			$bookFileType->setDesignation($bookFileDesignation, Locale::getLocale());

			$bookFileTypeId = $bookFileTypeDao->insertObject($bookFileType);

			// Return JSON with formatted HTML to insert into list
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($bookFileTypeId);
			$rowData = array('item' => $bookFileName, 'attribute' => $bookFileDesignation);
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
		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');

		foreach($args as $bookFileId) {
			$bookFileTypeDao->deleteById($bookFileId);
		}

		$json = new JSON('true');
		echo $json->getString();
	}
}
?>
