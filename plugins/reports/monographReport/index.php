<?php

/**
 * @defgroup plugins_reports_monographReport Monograph Report Plugin
 */

/**
 * @file plugins/reports/monographReport/index.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Wrapper for the monograph report plugin.
 *
 * @ingroup plugins_reports_monographReport
 */

require_once 'MonographReportPlugin.inc.php';

return new MonographReportPlugin();
