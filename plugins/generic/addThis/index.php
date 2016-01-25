<?php

/**
 * @defgroup plugins_generic_addThis Add This plugin
 */

/**
 * @file plugins/generic/addThis/index.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_addThis
 * @brief Wrapper for the addThis social media plugin.
 *
 */


require_once('AddThisPlugin.inc.php');

return new AddThisPlugin();

?>
