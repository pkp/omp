<?php

/**
 * @defgroup plugins_importexport_csv Data in tab delimited format import/export plugin
 */

/**
 * @file plugins/importexport/csv/index.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_csv
 * @brief Wrapper for tab delimited data import/export plugin.
 *
 */


require_once('CSVImportExportPlugin.inc.php');

return new CSVImportExportPlugin();

?>
