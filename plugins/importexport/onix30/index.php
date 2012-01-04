<?php

/**
 * @defgroup plugins_importexport_onix30
 */
 
/**
 * @file plugins/importexport/onix30/index.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_onix30
 * @brief Wrapper for ONIX 3.0 XML export plugin.
 *
 */

require_once('Onix30ExportPlugin.inc.php');

return new Onix30ExportPlugin();

?>
