<?php
 
/**
 * @file classes/monograph/form/MonographArtworkForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographArtworkForm
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Form to create or edit an issue
 */

// $Id$


import('form.Form');
import('inserts.artwork.ArtworkInsert');

class MonographArtworkForm extends Form {

	var $monograph;
	var $artworkInsert;

	/**
	 * Constructor.
	 */
	function MonographArtworkForm($template, $monograph) {
		parent::Form($template);
		$this->addCheck(new FormValidatorPost($this));
		$this->monograph =& $monograph;
		$this->artworkInsert = new ArtworkInsert($monograph->getMonographId());
	}

	/**
	 * Get a list of fields for which localization should be used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array();
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $this->monograph);

		$this->artworkInsert->display($this);

		parent::display();
	}

	function processEvents() {
		return $this->artworkInsert->processEvents($this);
	}

	/**
	 * Initialize form data from current issue.
	 * returns issue id that it initialized the page with
	 */
	function initData() {

	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars($this->artworkInsert->listUserVars());
	}

	/**
	 * Save issue settings.
	 */
	function execute() {
		  return $this->artworkInsert->execute($this, $this->monograph);
	}

}

?>