<?php

/**
 * @defgroup pages_production
 */
 
/**
 * @file pages/production/index.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_production
 * @brief Handle information requests. 
 *
 */

// $Id$


switch ($op) {

default:
	define('HANDLER_CLASS', 'ProductionEditorHandler');
	import('pages.productionEditor.ProductionEditorHandler');
	break;
}
?>
