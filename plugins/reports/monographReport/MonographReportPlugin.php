<?php

/**
 * @file plugins/reports/monographReport/MonographReportPlugin.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographReportPlugin
 *
 * @ingroup plugins_reports_monographReport
 *
 * @brief The monograph report plugin will output a .csv file containing basic information (title, DOI, etc.) from all monographs
 */

namespace APP\plugins\reports\monographReport;

use APP\press\Press;
use DateTimeImmutable;
use Exception;
use PKP\plugins\ReportPlugin;
use SplFileObject;

class MonographReportPlugin extends ReportPlugin
{
    private Press $press;

    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null): bool
    {
        $success = parent::register($category, $path, $mainContextId);
        $this->addLocaleData();
        return $success;
    }

    /**
     * @copydoc Plugin::getName()
     */
    public function getName(): string
    {
        return substr(static::class, strlen(__NAMESPACE__) + 1);
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName(): string
    {
        return __('plugins.reports.monographReport.displayName');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription(): string
    {
        return __('plugins.reports.monographReport.description');
    }

    /**
     * @copydoc ReportPlugin::display()
     */
    public function display($args, $request): void
    {
        $this->press = $request->getContext();
        if (!$this->press) {
            throw new Exception('The monograph report requires a context');
        }

        $output = $this->createOutputStream();
        // Output the CSV rows through the iterator implementation
        foreach (new Report($this->press, $request) as $row) {
            $output->fputcsv($row);
        }
    }

    /**
     * Retrieves a SplFileObject and sends HTTP headers to enforce the report download
     */
    private function createOutputStream(): SplFileObject
    {
        $acronym = preg_replace('/[^A-Za-z0-9 ]/u', '', $this->press->getLocalizedAcronym());
        $date = (new DateTimeImmutable())->format('Ymd');

        // Prepare for UTF8-encoded CSV output.
        header('content-type: text/comma-separated-values');
        header("content-disposition: attachment; filename=monographs-{$acronym}-{$date}.csv");

        $output = new SplFileObject('php://output', 'w');
        // UTF-8 BOM to force the file to be read with the right encoding
        $output->fwrite("\xEF\xBB\xBF");
        return $output;
    }
}
