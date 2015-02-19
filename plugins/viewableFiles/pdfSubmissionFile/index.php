<?php
/**
 * @defgroup plugins_viewableFiles_pdfSubmissionFile PDF submission file plugin
 */

/**
 * @file plugins/viewableFiles/pdfSubmissionFile/index.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_viewableFiles_pdfSubmissionFile
 * @brief Wrapper for pdf submission file plugin.
 *
 */

require_once('PdfSubmissionFilePlugin.inc.php');

return new PdfSubmissionFilePlugin();

?>
