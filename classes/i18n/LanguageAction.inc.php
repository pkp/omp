<?php

/**
 * @file classes/i18n/LanguageAction.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LanguageAction
 * @ingroup i18n
 *
 * @brief LanguageAction class.
 */

define('LANGUAGE_PACK_DESCRIPTOR_URL', 'http://pkp.sfu.ca/omp/xml/%s/locales.xml');
define('LANGUAGE_PACK_TAR_URL', 'http://pkp.sfu.ca/omp/xml/%s/%s.tar.gz');

import('lib.pkp.classes.i18n.PKPLanguageAction');

class LanguageAction extends PKPLanguageAction {
	/**
	 * Constructor.
	 */
	function LanguageAction() {
		parent::PKPLanguageAction();
	}
}

?>
