<?php

/**
 * @defgroup plugins_blocks_browse Browse block plugin
 */

/**
 * @file plugins/blocks/browse/index.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_blocks_browse
 * @brief Wrapper for browse block plugin.
 *
 */


require_once('BrowseBlockPlugin.inc.php');

return new BrowseBlockPlugin();

?>
