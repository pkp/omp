<?php
/**
 * @defgroup plugins_generic_htmlMonographFile HTML Monograph File Plugin
 */

/**
 * @file plugins/generic/htmlMonographFile/index.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_htmlMonographFile
 * @brief Wrapper for html monograph file plugin.
 *
 */

require_once('HtmlMonographFilePlugin.inc.php');

return new HtmlMonographFilePlugin();

?>
