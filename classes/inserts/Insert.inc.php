<?php

/**
 * @file inserts/Insert.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Insert
 * @ingroup inserts
 * 
 * @brief Base class insert.
 */

// $Id$

class Insert
{
	var $options;

	function Insert($options) {
		$this->options = $options;
	}

	/**
	 * Retrieve a list of the form variables associated with this insert.
	 * @return array  
	 */
	function &listUserVars() {
		$returner = array();
		return $returner;
	}

	/**
	 * Retrieve the form variables.
	 * @return array of Form_Variable => Variable_Value pairs  
	 */
	function &initData() {
		$returner = array();
		return $returner;
	}
	function display(&$form) {
		return null;
	}
	function &getLocaleFieldNames() {
		$fields = array();
		return $fields;
	}

	function execute(&$form, &$monograph) {
		return null;
	}

	/**
	 * Process any special form events.
	 * @return true if an event was processed
	 */
	function processEvents(&$form) {
		return false;
	}
}
?>