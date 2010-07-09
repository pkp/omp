<?php

/**
 * @file controllers/grid/settings/submissionChecklist/form/SubmissionChecklistForm.inc.php
 *
 * Copyright (c) 2000-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionChecklistForm
 * @ingroup controllers_grid_submissionChecklist_form
 *
 * @brief Form for adding/edditing a submissionChecklist
 * stores/retrieves from an associative array
 */

import('lib.pkp.classes.form.Form');

class SubmissionChecklistForm extends Form {
	/** the id for the submissionChecklist being edited **/
	var $submissionChecklistId;

	/**
	 * Constructor.
	 */
	function SubmissionChecklistForm($submissionChecklistId = null) {
		$this->submissionChecklistId = $submissionChecklistId;
		parent::Form('controllers/grid/settings/submissionChecklist/form/submissionChecklistForm.tpl');

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData(&$args, &$request) {
		$press =& Request::getPress();

		$submissionChecklistAll = $press->getSetting('submissionChecklist');
		$checklistItem = array();
		// preparea  localizable array for this checklist Item
		foreach (Locale::getSupportedLocales() as $locale => $name) {
			$checklistItem[$locale] = null;
		}

		// if editing, set the content
		// use of 'content' as key is for backwards compatibility
		if ( isset($this->submissionChecklistId) ) {
			foreach (Locale::getSupportedLocales() as $locale => $name) {
				if ( !isset($submissionChecklistAll[$locale][$this->submissionChecklistId]['content'])) {
					$checklistItem[$locale] = '';
				} else {
					$checklistItem[$locale] = $submissionChecklistAll[$locale][$this->submissionChecklistId]['content'];
				}
			}
		}
		// assign the data to the form
		$this->_data = array( 'checklistItem' => $checklistItem	);

		// grid related data
		$this->_data['gridId'] = $args['gridId'];
		$this->_data['rowId'] = $args['rowId'];
	}

	/**
	 * Fetch
	 */
	function fetch(&$request) {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('submissionChecklistId', 'checklistItem'));
		$this->readUserVars(array('gridId', 'rowId'));
	}

	/**
	 * Save email template.
	 */
	function execute(&$args, &$request) {
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$submissionChecklistAll = $press->getSetting('submissionChecklist');
		//FIXME: a bit of kludge to get unique submissionChecklist id's
		$this->submissionChecklistId = ($this->submissionChecklistId?$this->submissionChecklistId:(max(array_keys($submissionChecklistAll[Locale::getPrimaryLocale()])) + 1));

		$checklistItem = $this->getData('checklistItem');
		foreach (Locale::getSupportedLocales() as $locale => $name) {
			$submissionChecklistAll[$locale][$this->submissionChecklistId]['content'] = $checklistItem[$locale];
		}

		$press->updateSetting('submissionChecklist', $submissionChecklistAll, 'object', true);
		return true;
	}
}

?>
