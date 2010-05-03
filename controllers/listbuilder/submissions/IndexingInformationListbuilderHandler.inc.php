<?php

/**
 * @file controllers/listbuilder/submissions/IndexingInformationListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IndexingInformationListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding supporting agencies to a monograph
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class IndexingInformationListbuilderHandler extends ListbuilderHandler {
	/**
	 * Constructor
	 */
	function IndexingInformationListbuilderHandler() {
		parent::ListbuilderHandler();
	}


	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographId = $request->getUserVar('monographId');
		$monograph =& $monographDao->getMonograph($monographId);
		
		$supportingAgencies = $monograph->getLocalizedSupportingAgencies();

		$items = array();
		if(isset($supportingAgencies)) {
			foreach($supportingAgencies as $item) {
				$id = $item['name'];
				$items[$id] = array('item' => $id);
			}
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
		$this->setTitle('submission.supportingAgencies');
		$this->setSourceTitle('common.name');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input
		$this->setListTitle('submission.currentAgencies');

		$this->loadList($request);

		$this->addColumn(new GridColumn('item', 'common.name'));
	}
	
	/**
	 * Need to override the fetch method to provide groupID as an argument
	 */
	function fetch(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();
		$router =& $request->getRouter();

		// Let the subclass configure the listbuilder
		$this->initialize($request);
		$groupId = $request->getUserVar('groupId');

		$templateMgr->assign('itemId', $monographId); // Autocomplete fields require a unique ID to avoid JS conflicts
		$templateMgr->assign('addUrl', $router->url($request, array(), null, 'addItem', null, array('monographId' => $monographId)));
		$templateMgr->assign('deleteUrl', $router->url($request, array(), null, 'deleteItems', null, array('monographId' => $monographId)));

		// Translate modal submit/cancel buttons
		$okButton = Locale::translate('common.ok');
		$warning = Locale::translate('common.warning');
		$templateMgr->assign('localizedButtons', "$okButton, $warning");

		$row =& $this->getRowInstance();
		// initialize to create the columns
		$row->initialize($request);
		$columns =& $this->getColumns();
		$templateMgr->assign_by_ref('columns', $columns);
		$templateMgr->assign('numColumns', count($columns));

		// Render the rows
		$rows = $this->_renderRowsInternally($request);
		$templateMgr->assign_by_ref('rows', $rows);

		$templateMgr->assign('listbuilder', $this);
		echo $templateMgr->fetch('controllers/listbuilder/listbuilder.tpl');
    }
	
	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		parent::setupTemplate();

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_AUTHOR, LOCALE_COMPONENT_PKP_SUBMISSION));
	}

	//
	// Public AJAX-accessible functions
	//

	/*
	 * Handle adding an item to the list
	 */
	function addItem(&$args, &$request) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographId = $request->getUserVar('monographId');
		$monograph =& $monographDao->getMonograph($monographId);
		
		$index = 'sourceTitle-' . $this->getId();
		$supportingAgency = $args[$index];

		if(!isset($supportingAgency)) {
			$json = new JSON('false');
			echo $json->getString();
		} else {
			// Make sure the item doesn't already exist
			$supportingAgencies = $monograph->getLocalizedSupportingAgencies();
			if(isset($supportingAgencies)) {
				foreach($supportingAgencies as $item) {
					if($item['name'] == $supportingAgency) {
						$json = new JSON('false', Locale::translate('common.listbuilder.itemExists'));
						echo $json->getString();
						return false;
					}
				}
			}

			$supportingAgencies[] = array('name' => $supportingAgency);

			$monograph->setSupportingAgencies($supportingAgencies, Locale::getLocale());
			$monographDao->updateMonograph($monograph);
			
			// Return JSON with formatted HTML to insert into list
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($supportingAgency);
			$rowData = array('item' => $supportingAgency);
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
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographId = array_shift($args);
		$monograph =& $monographDao->getMonograph($monographId);
		$supportingAgencies = $monograph->getLocalizedSupportingAgencies();
		
		foreach($args as $item) {
			for ($i = 0; $i < count($supportingAgencies); $i++) {
				if ($supportingAgencies[$i]['name'] == $item) {
					array_splice($supportingAgencies, $i, 1);
				}
			}
		}

		$monograph->setSupportingAgencies($supportingAgencies, Locale::getLocale());
		$monographDao->updateMonograph($monograph);

		$json = new JSON('true');
		echo $json->getString();
	}
}
?>
