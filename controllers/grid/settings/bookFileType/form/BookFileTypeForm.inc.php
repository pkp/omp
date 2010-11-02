<?php

/**
 * @file controllers/grid/settings/bookFileType/form/BookFileTypeForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookFileTypeForm
 * @ingroup controllers_grid_settings_bookFileType_form
 *
 * @brief Form for adding/editing a Book File Type.
 */

import('lib.pkp.classes.form.Form');

class BookFileTypeForm extends Form {
	/** the id for the series being edited **/
	var $bookFileTypeId;

	/**
	 * Constructor.
	 */
	function BookFileTypeForm($bookFileTypeId = null) {
		$this->bookFileTypeId = $bookFileTypeId;
		parent::Form('controllers/grid/settings/bookFileType/form/bookFileTypeForm.tpl');

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'name', 'required', 'manager.setup.form.bookFileType.nameRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$press =& $request->getPress();

		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');

		if($this->bookFileTypeId) {
			$bookFileType =& $bookFileTypeDao->getById($this->bookFileTypeId, $press->getId());
		}

		if (isset($bookFileType) ) {
			$this->_data = array(
				'bookFileTypeId' => $this->bookFileTypeId,
				'name' => $bookFileType->getLocalizedName(),
				'designation' => $bookFileType->getLocalizedDesignation(),
				'sortable' => $bookFileType->getSortable(),
				'category' => $bookFileType->getCategory()
			);
		} else {
			$this->_data = array(
				'name' => '',
				'designation' => ''
			);
		}

		// grid related data
		$this->_data['gridId'] = $args['gridId'];
		$this->_data['rowId'] = isset($args['rowId']) ? $args['rowId'] : null;
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('bookFileCategories', array(BOOK_FILE_CATEGORY_DOCUMENT => Locale::translate('submission.document'),
					BOOK_FILE_CATEGORY_ARTWORK => Locale::translate('submission.art')));

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('bookFileTypeId', 'name', 'designation', 'sortable', 'category'));
		$this->readUserVars(array('gridId', 'rowId'));
	}

	/**
	 * Save email template.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, $request) {
		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$press =& $request->getPress();

		// Update or insert Book File Type
		if (!isset($this->bookFileTypeId)) {
			$bookFileType = $bookFileTypeDao->newDataObject();
		} else {
			$bookFileType =& $bookFileTypeDao->getById($this->bookFileTypeId);
		}

		$bookFileType->setName($this->getData('name'), Locale::getLocale()); // Localized
		$bookFileType->setDesignation($this->getData('designation'), Locale::getLocale()); // Localized
		$bookFileType->setSortable($this->getData('sortable'));
		$bookFileType->setCategory($this->getData('category'));

		if (!isset($this->bookFileTypeId)) {
			$this->bookFileTypeId = $bookFileTypeDao->insertObject($bookFileType);
		} else {
			$bookFileTypeDao->updateObject($bookFileType);
		}

		return true;
	}
}

?>
