<?php

/**
 * @file controllers/catalogEntry/PublicationFormatsListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatsListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for assigning publication formats to Published Monographs.
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class PublicationFormatsListbuilderHandler extends ListbuilderHandler {
	/** @var $press Press */
	var $_press;

	/** @var The monograph id for this listbuilder */
	var $_monographId;

	/**
	 * Constructor
	 */
	function PublicationFormatsListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetch', 'fetchRow', 'fetchOptions')
		);
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Set the monograph ID
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		$this->_monographId = $monographId;
	}

	/**
	 * Get the monograph ID
	 * @return int
	 */
	function getMonographId() {
		return $this->_monographId;
	}

	/**
	 * Set the current press
	 * @param $press Press
	 */
	function setPress(&$press) {
		$this->_press =& $press;
	}

	/**
	 * Get the current press
	 * @return Press
	 */
	function &getPress() {
		return $this->_press;
	}

	/**
	 * Load the list from an external source into the grid structure
	 * @param $request PKPRequest
	 * @return DAOResultFactory the result set containing the objects you want to preload
	 */
	function loadData(&$request) {
		$press =& $this->getPress();
		$monographId = $this->getMonographId();

		if ($monographId) {
			// Preexisting monograph
			$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
			$publishedMonograph =& $publishedMonographDao->getById($monographId);

			$monographPublicationFormatAssignmentDAO =& DAORegistry::getDAO('MonographPublicationFormatAssignmentDAO');
			$assignedFormats =& $monographPublicationFormatAssignmentDAO->getFormatsByPublishedMonographId($publishedMonograph->getPubId());
			return $assignedFormats; // DAOResultFactory
		}

		return array();
	}

	/**
	 * Get possible items to populate autosuggest list with
	 */
	function getOptions() {
		$press =& $this->getPress();
		$monographId = $this->getMonographId();

		if ($monographId) {
			// Preexisting monograph
			$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
			$publishedMonograph =& $publishedMonographDao->getById($monographId);

			$monographPublicationFormatAssignmentDao =& DAORegistry::getDAO('MonographPublicationFormatAssignmentDAO');
			$availablePublicationFormats =& $monographPublicationFormatAssignmentDao->getUnassignedPublicationFormats($publishedMonograph->getPubId(), $press->getId());
		} else {
			// New monograph
			$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
			$availablePublicationFormats =& $publicationFormatDao->getEnabledByPressId($press->getId());
		}

		$itemList = array(0 => array());
		while ($format =& $availablePublicationFormats->next()) {
			$itemList[0][$format->getId()] = $format->getLocalizedName();
			unset($format);
		}
		return $itemList;
	}

	/**
	 * Preserve the monograph ID for internal listbuilder requests.
	 * @see GridHandler::getRequestArgs
	 */
	function getRequestArgs() {
		$args = parent::getRequestArgs();
		$args['monographId'] = $this->getMonographId();
		return $args;
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
		$newRowId = $this->getNewRowId($request);
		$formatId = $newRowId['name'];
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$press =& $request->getPress();
		$publicationFormat =& $publicationFormatDao->getById($formatId, $press->getId());
		return $publicationFormat;
	}

	//
	// Overridden template methods
	//
	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);

		// Basic configuration
		$this->setPress($request->getPress());
		$this->setTitle('monograph.publicationFormats');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('publicationFormats');

		$this->setMonographId($request->getUserVar('monographId'));

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name');

		import('controllers.listbuilder.publicationFormats.PublicationFormatListbuilderGridCellProvider');
		$nameColumn->setCellProvider(new PublicationFormatListbuilderGridCellProvider());
		$this->addColumn($nameColumn);
	}
}

?>
