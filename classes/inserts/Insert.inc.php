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

	function &listUserVars() {
		$returner = array();
		return $returner;
	}
	function initData(&$form) {
		return null;
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
	function processEvents(&$form) {
		return false;
	}
}
?>