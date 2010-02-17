<?php

/**
 * @file classes/author/form/submit/AuthorSubmitStep2Form.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep2Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 2 of author manuscript submission.
 */

// $Id$


import('author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep2Form extends AuthorSubmitForm {

	/**
	 * Constructor.
	 */
	function AuthorSubmitStep2Form($monograph) {
		parent::AuthorSubmitForm($monograph);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {

	}

	function getHelpTopicId() {
		return 'submission.indexingAndMetadata';
	}
	
	function getTemplateFile() {
		return 'author/submit/step2.tpl';
	}
	
	/**
	 * Display the form.
	 */
	function display() {
		parent::display();
	}


	/**
	 * Save changes to monograph.
	 * @return int the monograph ID
	 */
	function execute() {
		return true;
	}
}

?>
