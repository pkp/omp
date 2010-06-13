<?php

/**
 * @file classes/manager/form/setup/PressSetupStep1Form.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSetupStep1Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 1 of the press setup.
 */

// $Id$


import('classes.settings.form.setup.PressSetupForm');

class PressSetupStep1Form extends PressSetupForm {
	/**
	 * Constructor.
	 */
	function PressSetupStep1Form() {
		parent::PressSetupForm(
			1,
			array(
				'name' => 'string',
				'initials' => 'string',
				'description' => 'string',
				'mailingAddress' => 'string',
				'pressEnabled' => 'bool',
				'useEditorialBoard' => 'bool',
				'contactName' => 'string',
				'contactTitle' => 'string',
				'contactAffiliation' => 'string',
				'contactEmail' => 'string',
				'contactPhone' => 'string',
				'contactFax' => 'string',
				'contactMailingAddress' => 'string',
				'supportName' => 'string',
				'supportEmail' => 'string',
				'supportPhone' => 'string',
				'sponsorNote' => 'string',
				'sponsors' => 'object',
				'contributorNote' => 'string',
				'contributors' => 'object',
				'envelopeSender' => 'string',
				'emailSignature' => 'string',
				'privacyStatement' => 'string'
			)
		);

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'settings.setup.form.pressNameRequired'));
		$this->addCheck(new FormValidatorLocale($this, 'initials', 'required', 'settings.setup.form.pressInitialsRequired'));
		$this->addCheck(new FormValidator($this, 'contactName', 'required', 'settings.setup.form.contactNameRequired'));
		$this->addCheck(new FormValidatorEmail($this, 'contactEmail', 'required', 'settings.setup.form.contactEmailRequired'));
		$this->addCheck(new FormValidator($this, 'supportName', 'required', 'settings.setup.form.supportNameRequired'));
		$this->addCheck(new FormValidatorEmail($this, 'supportEmail', 'required', 'settings.setup.form.supportEmailRequired'));
	}

	/**
	 * Get the list of field names for which localized settings are used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array(
			'name', 'initials', 'description', 'sponsorNote', 'contactTitle', 'contactAffiliation', 'contactMailingAddress', 'contributorNote', 'privacyStatement'
		);
	}

	/**
	 * Execute the form, but first:
	 * Make sure we're not saving an empty entry for sponsors. (This would
	 * result in a possibly empty heading for the Sponsors section in About
	 * the Press.)
	 */
	function execute() {
		foreach (array('sponsors', 'contributors') as $element) {
			$elementValue = (array) $this->getData($element);
			foreach (array_keys($elementValue) as $key) {
				$values = array_values((array) $elementValue[$key]);
				$isEmpty = true;
				foreach ($values as $value) {
					if (!empty($value)) $isEmpty = false;
				}
				if ($isEmpty) unset($elementValue[$key]);
			}
			$this->setData($element, $elementValue);
		}

		$press =& Request::getPress();

		if ($press->getEnabled() !== $this->getData('pressEnabled')) {
			$pressDao =& DAORegistry::getDAO('PressDAO');
			$press->setEnabled($this->getData('pressEnabled'));
			$pressDao->updatePress($press);

		}

		return parent::execute();
	}

	/**
	 * Display the form.
	 */
	function display() {
		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();
		if (Config::getVar('email', 'allow_envelope_sender'))
			$templateMgr->assign('envelopeSenderEnabled', true);

		$templateMgr->assign('pressEnabled', $press->getEnabled());

		parent::display();
	}
}

?>
