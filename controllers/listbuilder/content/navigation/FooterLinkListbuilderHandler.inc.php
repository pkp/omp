<?php

/**
 * @file controllers/listbuilder/content/navigation/FooterLinkListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FooterLinkListbuilderHandler
 * @ingroup controllers_listbuilder_content_navigation
 *
 * @brief Class for managing footer links.
 */

import('lib.pkp.controllers.listbuilder.settings.SetupListbuilderHandler');

class FooterLinkListbuilderHandler extends SetupListbuilderHandler {

	/** @var int **/
	var $_footerCategoryId;

	/**
	 * Constructor
	 */
	function FooterLinkListbuilderHandler() {
		parent::SetupListbuilderHandler();
		$this->addRoleAssignment(
			ROLE_ID_MANAGER,
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
		$footerCategoryId = (int)$request->getUserVar('footerCategoryId');
		$press = $request->getPress();

		$footerCategoryDao = DAORegistry::getDAO('FooterCategoryDAO');
		$footerCategory = $footerCategoryDao->getById($footerCategoryId, $press->getId());
		if ($footerCategoryId && !isset($footerCategory)) {
			fatalError('Footer Category does not exist within this press context.');
		} else {
			$this->_footerCategoryId = $footerCategoryId;
		}

		// Basic configuration
		$this->setTitle('grid.content.navigation.footer.FooterLink');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT);
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('footerLinks');

		// Title column
		$titleColumn = new MultilingualListbuilderGridColumn($this, 'title', 'common.title', null, null, null, null, array('tabIndex' => 1));
		import('controllers.listbuilder.content.navigation.FooterLinkListbuilderGridCellProvider');
		$titleColumn->setCellProvider(new FooterLinkListbuilderGridCellProvider());
		$this->addColumn($titleColumn);

		$urlColumn = new ListbuilderGridColumn($this, 'url', 'common.url', null, null, null, array('tabIndex' => 2));
		$urlColumn->setCellProvider(new FooterLinkListbuilderGridCellProvider());
		$this->addColumn($urlColumn);
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData(&$request) {
		$press = $this->getContext();
		$footerLinkDao = DAORegistry::getDAO('FooterLinkDAO');
		return $footerLinkDao->getByCategoryId($this->_getFooterCategoryId(), $press->getId());
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
		import('controllers.grid.content.navigation.form.FooterCategoryForm');
		$press = $request->getPress();
		$footerCategoryForm = new FooterCategoryForm($press->getId());
		return $footerCategoryForm->getFooterLinkFromRowData($request, $rowData);
	}

	/**
	 * Fetch the category Id for this listbuilder.
	 * @return int
	 */
	function _getFooterCategoryId() {
		return $this->_footerCategoryId;
	}
}
?>
