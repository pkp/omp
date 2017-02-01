<?php
/**
 * @defgroup plugins_viewableFiles_pdfJsViewer PDF submission file plugin
 */

/**
 * @file plugins/viewableFiles/pdfJsViewer/index.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_viewableFiles_pdfJsViewer
 * @brief Wrapper for pdf submission file plugin.
 *
 */

require_once('PdfJsViewerPlugin.inc.php');

return new PdfJsViewerPlugin();

?>
