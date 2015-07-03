<?php

/**
 * @file classes/plugins/PaymethodPlugin.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2006-2009 Gunther Eysenbach, Juan Pablo Alperin, MJ Suhonos
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaymethodPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for paymethod plugins
 */

import('lib.pkp.classes.plugins.PKPPaymethodPlugin');

abstract class PaymethodPlugin extends PKPPaymethodPlugin {
	/**
	 * Constructor
	 */
	function PaymethodPlugin() {
		parent::PKPPaymethodPlugin();
	}
}

?>
