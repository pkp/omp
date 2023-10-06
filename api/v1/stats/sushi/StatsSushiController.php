<?php

/**
 * @file api/v1/stats/sushi/StatsSushiController.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StatsSushiController
 *
 * @ingroup api_v1_stats
 *
 * @brief Handle API requests for COUNTER R5 SUSHI statistics.
 *
 */

namespace APP\API\v1\stats\sushi;

use APP\sushi\TR;
use APP\sushi\TR_B3;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class StatsSushiController extends \PKP\API\v1\stats\sushi\PKPStatsSushiController
{
    /**
     * @copydoc \PKP\core\PKPBaseController::getGroupRoutes()
     */
    public function getGroupRoutes(): void
    {
        parent::getGroupRoutes();

        Route::get('reports/tr', $this->getReportsTR(...))
            ->name('stats.sushi.getReportsTR');

        Route::get('reports/tr_b3', $this->getReportsTRB3(...))
            ->name('stats.sushi.getReportsTRB3');
    }

    /**
     * COUNTER 'Title Master Report' [TR].
     * A customizable report detailing activity at the press level
     * that allows the user to apply filters and select other configuration options for the report.
     */
    public function getReportsTR(Request $illuminateRequest): JsonResponse
    {
        return $this->getReportResponse(new TR(), $illuminateRequest);
    }

    /**
     * COUNTER 'Book Usage by Access Type' [TR_B3].
     * This is a Standard View of Title Master Report that reports on book usage showing all applicable Metric_Types broken down by Access_Type.
     */
    public function getReportsTRB3(Request $illuminateRequest): JsonResponse
    {
        return $this->getReportResponse(new TR_B3(), $illuminateRequest);
    }

    /**
     * Get the application specific list of reports supported by the API
     */
    protected function getReportList(): array
    {
        return array_merge(parent::getReportList(), [
            [
                'Report_Name' => 'Title Master Report',
                'Report_ID' => 'TR',
                'Release' => '5',
                'Report_Description' => __('sushi.reports.tr.description'),
                'Path' => 'reports/tr'
            ],
            [
                'Report_Name' => 'Book Usage by Access Type',
                'Report_ID' => 'TR_B3',
                'Release' => '5',
                'Report_Description' => __('sushi.reports.tr_b3.description'),
                'Path' => 'reports/tr_b3'
            ],
        ]);
    }
}
