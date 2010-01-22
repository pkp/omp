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

class PublicationFormatsListbuilderHandler extends ListbuilderHandler {	
	/** @var boolean internal state variable, true if row handler has been instantiated */
	var $_rowHandlerInstantiated = false;
	
	/**
	 * Constructor
	 */
	function PublicationFormatsListbuilderHandler() {
		parent::ListbuilderHandler();
	}


	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');		
		$press =& $request->getPress();
		
		$publicationFormats =& $publicationFormatDao->getEnabledByPressId($press->getId());


		$items = array();
		foreach($publicationFormats as $item) {
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
		$this->setId('publicationFormats');
		$this->setTitle('manager.setup.publicationFormats');
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
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$press =& $request->getPress();
	
		$publicationFormatName = $args['sourceTitle-publicationFormats'];
		$publicationFormatDesignation = $args['attribute-1-publicationFormats'];

		if(!isset($publicationFormatName) || !isset($publicationFormatDesignation)) {
			$json = new JSON('false');
			echo $json->getString();
		} else {
			$locale = Locale::getLocale();
			$publicationFormat = $publicationFormatDao->newDataObject();
			$publicationFormat->setName($publicationFormatName, $locale);
			$publicationFormat->setDesignation($publicationFormatDesignation, $locale);

			$publicationFormatId = $publicationFormatDao->insertObject($publicationFormat);

			// Return JSON with formatted HTML to insert into list
			$publicationFormatRow =& $this->getRowHandler();
			$rowData = array('item' => $publicationFormatName, 'attribute' => $publicationFormatDesignation);
			$publicationFormatRow->_configureRow($request);
			$publicationFormatRow->setData($rowData);
			$publicationFormatRow->setId($publicationFormatId);

			$json = new JSON('true', $publicationFormatRow->renderRowInternally($request));
			echo $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 */
	function deleteitems(&$args, &$request) {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');

		foreach($args as $publicationFormatId) {
			$publicationFormatDao->deleteById($publicationFormatId);
		}
		
		$json = new JSON('true');
		echo $json->getString();
	}
}
?>
