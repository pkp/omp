<?php

/**
 * @file classes/manager/listbuilder/PublicationFormatsListbuilderHandler.inc.php
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
	var $_rowHandlerInstantiated = false;

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
		// Only initialize once
		if ($this->getInitialized()) return;

		// Basic configuration
		$this->setId('bookFileTypes');
		$this->setTitle('manager.setup.bookFileTypes');
		$this->setSourceTitle('common.name');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input
		$this->setListTitle('manager.setup.publicationFormats');
		$this->setAttributeNames(array('common.designation'));

		$this->loadList($request);

		parent::initialize($request);
	}

	/**
	 * Get the row handler - override the default row handler
	 * @return SponsorRowHandler
	 */
	function &getRowHandler() {
		if (!$this->_rowHandlerInstantiated) {
			import('controllers.listbuilder.ListbuilderGridRowHandler');
			$rowHandler =& new ListbuilderGridRowHandler();

			// Basic grid row configuration
			$rowHandler->addColumn(new GridColumn('item', 'common.name'));
			$rowHandler->addColumn(new GridColumn('attribute', 'common.designation'));

			$this->setRowHandler($rowHandler);
			$this->_rowHandlerInstantiated = true;
		}
		return parent::getRowHandler();
	}


	//
	// Public AJAX-accessible functions
	//

	/*
	 * Handle adding an item to the list
	 */
	function additem(&$args, &$request) {
		$this->setupTemplate();
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$press =& $request->getPress();

		$bookFileName = $args['sourceTitle-bookFileTypes'];
		$bookFileDesignation = $args['attribute-1-bookFileTypes'];


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

			$bookFileType->setName($bookFileName);
			$bookFileType->setDesignation($bookFileDesignation);

			$bookFileTypeDao->insertObject($bookFileType);

			// Return JSON with formatted HTML to insert into list
			$bookFileTypeRow =& $this->getRowHandler();
			$rowData = array('item' => $bookFileName, 'attribute' => $bookFileDesignation);
			$bookFileTypeRow->configureRow($request);
			$bookFileTypeRow->setData($rowData);
			$bookFileTypeRow->setId($publicationFormatId);

			$json = new JSON('true', $bookFileTypeRow->renderRowInternally($request));
			echo $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 */
	function deleteitems(&$args, &$request) {
		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');

		foreach($args as $bookFileId) {
			$bookFileTypeDao->deleteById($bookFileId);
		}

		$json = new JSON('true');
		echo $json->getString();
	}
}
?>
