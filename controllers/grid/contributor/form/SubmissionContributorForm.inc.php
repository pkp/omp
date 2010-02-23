<?php

/**
 * @file controllers/grid/submissionContributor/form/SubmissionContributorForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionContributorForm
 * @ingroup controllers_grid_submissionContributor_form
 *
 * @brief Form for adding/edditing a submissionContributor
 * stores/retrieves from an associative array
 */

import('form.Form');

class SubmissionContributorForm extends Form {
	/** the id for the submissionContributor being edited **/
	var $submissionContributorId;

	/**
	 * Constructor.
	 */
	function SubmissionContributorForm($submissionContributorId = null) {
		$this->submissionContributorId = $submissionContributorId;
		parent::Form('controllers/grid/submissionContributor/form/submissionContributorForm.tpl');

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'institution', 'required', 'manager.setup.form.submissionContributors.institutionRequired'));
		$this->addCheck(new FormValidator($this, 'url', 'required', 'manager.emails.form.submissionContributors.urlRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData(&$args, &$request) {
		$press =& Request::getPress();

		$submissionContributors = $press->getSetting('submissionContributors');
		if ( $this->submissionContributorId && isset($submissionContributors[$this->submissionContributorId]) ) {
			$this->_data = array(
				'submissionContributorId' => $this->submissionContributorId,
				'institution' => $submissionContributors[$this->submissionContributorId]['institution'],
				'url' => $submissionContributors[$this->submissionContributorId]['url']
				);
		} else {
			$this->_data = array(
				'institution' => '',
				'url' => ''
			);
		}

		// grid related data
		$this->_data['gridId'] = $args['gridId'];
		$this->_data['rowId'] = $args['rowId'];
	}

	/**
	 * Display
	 */
	function display() {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		parent::display();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('submissionContributorId', 'institution', 'url'));
		$this->readUserVars(array('gridId', 'rowId'));
	}

	/**
	 * Save email template.
	 */
	function execute() {
		$press =& Request::getPress();
		$submissionContributors = $press->getSetting('submissionContributors');
		//FIXME: a bit of kludge to get unique submissionContributor id's
		$this->submissionContributorId = ($this->submissionContributorId?$this->submissionContributorId:(max(array_keys($submissionContributors)) + 1));
		$submissionContributors[$this->submissionContributorId] = array('institution' => $this->getData('institution'),
							'url' => $this->getData('url'));

		$press->updateSetting('submissionContributors', $submissionContributors, 'object', false);
		return true;
	}
}

?>
