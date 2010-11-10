<?php

/**
 * @file controllers/grid/settings/monographFileType/form/MonographFileTypeForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileTypeForm
 * @ingroup controllers_grid_settings_monographFileType_form
 *
 * @brief Form for adding/editing a Monograph File Type.
 */

import('lib.pkp.classes.form.Form');

class MonographFileTypeForm extends Form {
	/** the id for the series being edited **/
	var $monographFileTypeId;

	/**
	 * Constructor.
	 */
	function MonographFileTypeForm($monographFileTypeId = null) {
		$this->monographFileTypeId = $monographFileTypeId;
		parent::Form('controllers/grid/settings/monographFileType/form/monographFileTypeForm.tpl');

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'name', 'required', 'manager.setup.form.monographFileType.nameRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$press =& $request->getPress();

		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');

		if($this->monographFileTypeId) {
			$monographFileType =& $monographFileTypeDao->getById($this->monographFileTypeId, $press->getId());
		}

		if (isset($monographFileType) ) {
			$this->_data = array(
				'monographFileTypeId' => $this->monographFileTypeId,
				'name' => $monographFileType->getLocalizedName(),
				'designation' => $monographFileType->getLocalizedDesignation(),
				'sortable' => $monographFileType->getSortable(),
				'category' => $monographFileType->getCategory()
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
		$templateMgr->assign('monographFileCategories', array(MONOGRAPH_FILE_CATEGORY_DOCUMENT => Locale::translate('submission.document'),
					MONOGRAPH_FILE_CATEGORY_ARTWORK => Locale::translate('submission.art')));

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('monographFileTypeId', 'name', 'designation', 'sortable', 'category'));
		$this->readUserVars(array('gridId', 'rowId'));
	}

	/**
	 * Save email template.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, $request) {
		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');
		$press =& $request->getPress();

		// Update or insert Monograph File Type
		if (!isset($this->monographFileTypeId)) {
			$monographFileType = $monographFileTypeDao->newDataObject();
		} else {
			$monographFileType =& $monographFileTypeDao->getById($this->monographFileTypeId);
		}

		$monographFileType->setName($this->getData('name'), Locale::getLocale()); // Localized
		$monographFileType->setDesignation($this->getData('designation'), Locale::getLocale()); // Localized
		$monographFileType->setSortable($this->getData('sortable'));
		$monographFileType->setCategory($this->getData('category'));

		if (!isset($this->monographFileTypeId)) {
			$this->monographFileTypeId = $monographFileTypeDao->insertObject($monographFileType);
		} else {
			$monographFileTypeDao->updateObject($monographFileType);
		}

		return true;
	}
}

?>
