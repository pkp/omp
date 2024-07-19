<?php

/**
 * @defgroup plugins_importexport_csv Data in tab delimited format import/export plugin
 */

/**
 * @file plugins/importexport/csv/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_csv
 * @brief Wrapper for CSV data import/export plugin.
 *
 */


require_once('CSVImportExportPlugin.inc.php');

return new CSVImportExportPlugin();


