<?php

/**
 * @file controllers/tab/settings/contact/form/ContactForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContactForm
 * @ingroup controllers_tab_settings_contact_form
 *
 * @brief Form to edit press contact settings.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class ContactForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function ContactForm() {
		$settings = array(
			'contactName' => 'string',
			'contactTitle' => 'string',
			'contactAffiliation' => 'string',
			'contactEmail' => 'string',
			'contactPhone' => 'string',
			'contactMailingAddress' => 'string',
			'contactFax' => 'string'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/contact/form/contactForm.tpl');

		$this->addCheck(new FormValidator($this, 'contactName', 'required', 'manager.setup.form.contactNameRequired'));
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('contactTitle', 'contactAffiliation', 'contactMailingAddress');
	}
}

