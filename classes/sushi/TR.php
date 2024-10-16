<?php

/**
 * @file classes/sushi/TR.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TR
 *
 * @ingroup sushi
 *
 * @brief COUNTER R5 SUSHI Title Master Report (TR).
 *
 */

namespace APP\sushi;

use APP\facades\Repo;
use Illuminate\Support\Collection;
use PKP\components\forms\FieldOptions;
use PKP\components\forms\FieldText;
use PKP\statistics\PKPStatisticsHelper;
use PKP\sushi\CounterR5Report;

class TR extends CounterR5Report
{
    /** The R5 data type the report is about. */
    public const DATA_TYPE = 'Book';

    /** Requested section types */
    public array $sectionTypes = ['Book', 'Chapter', 'Title'];

    /** Requested metric types */
    public array $metricTypes = [
        'Total_Item_Investigations',
        'Unique_Item_Investigations',
        'Total_Item_Requests',
        'Unique_Item_Requests',
        'Unique_Title_Investigations',
        'Unique_Title_Requests'
    ];

    /**
     * Get report name defined by COUNTER.
     */
    public function getName(): string
    {
        return 'Title Master Report';
    }

    /**
     * Get report ID defined by COUNTER.
     */
    public function getID(): string
    {
        return 'TR';
    }

    /**
     * Get report description.
     */
    public function getDescription(): string
    {
        return __('sushi.reports.tr.description');
    }

    /**
     * Get API path defined by COUNTER for this report.
     */
    public function getAPIPath(): string
    {
        return 'reports/tr';
    }

    /**
     * Get request parameters supported by this report.
     */
    public function getSupportedParams(): array
    {
        return [
            'customer_id',
            'begin_date',
            'end_date',
            'platform',
            'item_id',
            'metric_type',
            'data_type',
            'section_type',
            'yop',
            'access_type',
            'access_method',
            'attributes_to_show',
            'granularity',
            '_', // for ajax requests
        ];
    }

    /**
     * Get filters supported by this report.
     */
    public function getSupportedFilters(): array
    {
        return [
            [
                'name' => 'YOP',
                'supportedValues' => [],
                'param' => 'yop'
            ],
            [
                'name' => 'Item_Id',
                'supportedValues' => [$this->context->getId()],
                'param' => 'item_id'
            ],
            [
                'name' => 'Access_Type',
                'supportedValues' => [self::ACCESS_TYPE],
                'param' => 'access_type'
            ],
            [
                'name' => 'Section_Type',
                'supportedValues' => ['Book', 'Chapter'],
                'param' => 'section_type'
            ],
            [
                'name' => 'Data_Type',
                'supportedValues' => [self::DATA_TYPE],
                'param' => 'data_type'
            ],
            [
                'name' => 'Metric_Type',
                'supportedValues' => ['Total_Item_Investigations', 'Unique_Item_Investigations', 'Total_Item_Requests', 'Unique_Item_Requests', 'Unique_Title_Investigations', 'Unique_Title_Requests'],
                'param' => 'metric_type'
            ],
            [
                'name' => 'Access_Method',
                'supportedValues' => [self::ACCESS_METHOD],
                'param' => 'access_method'
            ],
        ];
    }

    /**
     * Get attributes supported by this report.
     *
     * The attributes will be displayed and they define what the metrics will be aggregated by.
     * Data_Type, Access_Method, and Access_Type are currently always the same for this report,
     * so they will only be displayed and not considered for metrics aggregation.
     * The only attributes considered for metrics aggregation are Attributes_To_Show=YOP and granularity=Month.
     */
    public function getSupportedAttributes(): array
    {
        return [
            [
                'name' => 'Attributes_To_Show',
                'supportedValues' => ['Data_Type', 'Access_Method', 'Section_Type', 'Access_Type', 'YOP'],
                'param' => 'attributes_to_show'
            ],
            [
                'name' => 'granularity',
                'supportedValues' => ['Month', 'Totals'],
                'param' => 'granularity'
            ],
        ];
    }

    /**
     * Set filters based on the requested parameters.
     */
    public function setFilters(array $filters): void
    {
        parent::setFilters($filters);
        foreach ($filters as $filter) {
            switch ($filter['Name']) {
                case 'YOP':
                    $this->yearsOfPublication = explode('|', $filter['Value']);
                    break;
                case 'Section_Type':
                    $this->sectionTypes = explode('|', $filter['Value']);
            }
        }
        // check section_type and metric_type mismatch ?
    }

    /** Get DB query results for the report */
    protected function getQueryResults(): Collection
    {
        $params['contextIds'] = [$this->context->getId()];
        $params['institutionId'] = $this->customerId;
        $params['dateStart'] = $this->beginDate;
        $params['dateEnd'] = $this->endDate;
        $params['yearsOfPublication'] = $this->yearsOfPublication;
        // do not consider section_type filter now, but later when grouping by submission
        // do not consider metric_type filter now, but for display

        $statsService = app()->get('sushiStats');
        $metricsQB = $statsService->getQueryBuilder($params);
        // consider attributes to group the metrics by
        $groupBy = ['m.' . PKPStatisticsHelper::STATISTICS_DIMENSION_SUBMISSION_ID];
        $orderBy = ['m.' . PKPStatisticsHelper::STATISTICS_DIMENSION_SUBMISSION_ID => 'asc'];
        // This report is on submission level, and the relationship between submission_id and YOP is one to one,
        // so no need to group or order by YOP -- it is enough to group and order by submission_id
        if ($this->granularity == 'Month') {
            $groupBy[] = 'm.' . PKPStatisticsHelper::STATISTICS_DIMENSION_MONTH;
            $orderBy['m.' . PKPStatisticsHelper::STATISTICS_DIMENSION_MONTH] = 'asc';
        }
        $metricsQB = $metricsQB->getSum($groupBy);
        // if set, consider results ordering
        foreach ($orderBy as $column => $direction) {
            $metricsQB = $metricsQB->orderBy($column, $direction);
        }
        $results = $metricsQB->get();
        if (!$results->count()) {
            $this->addWarning([
                'Code' => 3030,
                'Severity' => 'Error',
                'Message' => 'No Usage Available for Requested Dates',
                'Data' => __('sushi.exception.3030', ['beginDate' => $this->beginDate, 'endDate' => $this->endDate])
            ]);
        }
        return $results;
    }

    /**
     * Get report items
     */
    public function getReportItems(): array
    {
        $results = $this->getQueryResults();

        // Group result by submission ID
        // If Section_Type is attribute to show, group additionally by section type
        // Also filter results by requested section and metric types
        $resultsGroupedBySubmission = $items = [];
        foreach ($results as $result) {
            $includeResult = false;
            if (in_array('Book', $this->sectionTypes) &&
                !empty(array_intersect($this->metricTypes, ['Total_Item_Investigations', 'Unique_Item_Investigations', 'Total_Item_Requests', 'Unique_Item_Requests'])) &&
                ($result->metric_book_investigations > 0 || $result->metric_book_investigations_unique > 0 || $result->metric_book_requests > 0 || $result->metric_book_requests_unique > 0)) {
                if (in_array('Section_Type', $this->attributesToShow)) {
                    $resultsGroupedBySubmission[$result->submission_id]['Book'][] = $result;
                }
                $includeResult = true;
            }
            if (in_array('Chapter', $this->sectionTypes) &&
                !empty(array_intersect($this->metricTypes, ['Total_Item_Investigations', 'Unique_Item_Investigations', 'Total_Item_Requests', 'Unique_Item_Requests'])) &&
                ($result->metric_chapter_investigations > 0 || $result->metric_chapter_investigations_unique > 0 || $result->metric_chapter_requests > 0 || $result->metric_chapter_requests_unique > 0)) {
                if (in_array('Section_Type', $this->attributesToShow)) {
                    $resultsGroupedBySubmission[$result->submission_id]['Chapter'][] = $result;
                }
                $includeResult = true;
            }
            if (in_array('Title', $this->sectionTypes) &&
                !empty(array_intersect($this->metricTypes, ['Unique_Title_Investigations', 'Unique_Title_Requests'])) &&
                ($result->metric_title_investigations_unique > 0 || $result->metric_title_requests_unique > 0)) {
                if (in_array('Section_Type', $this->attributesToShow)) {
                    $resultsGroupedBySubmission[$result->submission_id]['Title'][] = $result;
                }
                $includeResult = true;
            }
            if (!in_array('Section_Type', $this->attributesToShow) && $includeResult) {
                $resultsGroupedBySubmission[$result->submission_id][] = $result;
            }
        }

        foreach ($resultsGroupedBySubmission as $submissionId => $submissionResults) {
            // Section_Type is in attributes_to_show, the query results are grouped by section type
            if (in_array('Section_Type', $this->attributesToShow)) {
                foreach ($submissionResults as $sectionType => $results) {
                    // Get the submission properties
                    $submission = Repo::submission()->get($submissionId);
                    if (!$submission || !$submission->getOriginalPublication()) {
                        break;
                    }
                    $currentPublication = $submission->getCurrentPublication();
                    $submissionLocale = $submission->getData('locale');
                    $itemTitle = $currentPublication->getLocalizedTitle($submissionLocale);

                    $item = [
                        'Title' => $itemTitle,
                        'Platform' => $this->platformName,
                        'Publisher' => $this->context->getData('publisherInstitution'),
                    ];
                    $item['Item_ID'][] = [
                        'Type' => 'Proprietary',
                        'Value' => $this->platformId . ':' . $submissionId,
                    ];
                    $doi = $currentPublication->getDoi();
                    if (isset($doi)) {
                        $item['Item_ID'][] = [
                            'Type' => 'DOI',
                            'Value' => $doi,
                        ];
                    }

                    $datePublished = $submission->getOriginalPublication()->getData('datePublished');
                    foreach ($this->attributesToShow as $attributeToShow) {
                        if ($attributeToShow == 'Data_Type') {
                            $item['Data_Type'] = self::DATA_TYPE;
                        } elseif ($attributeToShow == 'Section_Type') {
                            // do not display section type for title metrics
                            if ($sectionType != 'Title') {
                                $item['Section_Type'] = $sectionType;
                            }
                        } elseif ($attributeToShow == 'Access_Type') {
                            $item['Access_Type'] = self::ACCESS_TYPE;
                        } elseif ($attributeToShow == 'Access_Method') {
                            $item['Access_Method'] = self::ACCESS_METHOD;
                        } elseif ($attributeToShow == 'YOP') {
                            $item['YOP'] = date('Y', strtotime($datePublished));
                        }
                    }

                    $performances = [];
                    foreach ($results as $result) {
                        // if granularity=Month, the results will contain metrics for each month
                        // else the results will only contain the summarized metrics for the whole period
                        if (isset($result->month)) {
                            $periodBeginDate = date_format(date_create($result->month . '01'), 'Y-m-01');
                            $periodEndDate = date_format(date_create($result->month . '01'), 'Y-m-t');
                        } else {
                            $periodBeginDate = date_format(date_create($this->beginDate), 'Y-m-01');
                            $periodEndDate = date_format(date_create($this->endDate), 'Y-m-t');
                        }
                        $periodMetrics['Period'] = [
                            'Begin_Date' => $periodBeginDate,
                            'End_Date' => $periodEndDate,
                        ];

                        $instances = $metrics = [];
                        switch ($sectionType) {
                            case 'Book':
                                $metrics['Total_Item_Investigations'] = $result->metric_book_investigations;
                                $metrics['Unique_Item_Investigations'] = $result->metric_book_investigations_unique;
                                $metrics['Total_Item_Requests'] = $result->metric_book_requests;
                                $metrics['Unique_Item_Requests'] = $result->metric_book_requests_unique;
                                break;
                            case 'Chapter':
                                $metrics['Total_Item_Investigations'] = $result->metric_chapter_investigations;
                                $metrics['Unique_Item_Investigations'] = $result->metric_chapter_investigations_unique;
                                $metrics['Total_Item_Requests'] = $result->metric_chapter_requests;
                                $metrics['Unique_Item_Requests'] = $result->metric_chapter_requests_unique;
                                break;
                            case 'Title':
                                $metrics['Unique_Title_Investigations'] = $result->metric_title_investigations_unique;
                                $metrics['Unique_Title_Requests'] = $result->metric_title_requests_unique;
                                break;
                        }
                        // filter here by requested metric types
                        foreach ($this->metricTypes as $metricType) {
                            if (array_key_exists($metricType, $metrics) && $metrics[$metricType] > 0) {
                                $instances[] = [
                                    'Metric_Type' => $metricType,
                                    'Count' => (int) $metrics[$metricType]
                                ];
                            }
                        }
                        $periodMetrics['Instance'] = $instances;
                        $performances[] = $periodMetrics;
                    }
                    $item['Performance'] = $performances;
                    $items[] = $item;
                }
            } else { // Section_Type is not in attributes_to_show
                $results = $submissionResults;
                // Get the submission properties
                $submission = Repo::submission()->get($submissionId);
                if (!$submission || !$submission->getOriginalPublication()) {
                    break;
                }
                $currentPublication = $submission->getCurrentPublication();
                $submissionLocale = $submission->getData('locale');
                $itemTitle = $currentPublication->getLocalizedTitle($submissionLocale);

                $item = [
                    'Title' => $itemTitle,
                    'Platform' => $this->platformName,
                    'Publisher' => $this->context->getData('publisherInstitution'),
                ];
                $item['Item_ID'][] = [
                    'Type' => 'Proprietary',
                    'Value' => $this->platformId . ':' . $submissionId,
                ];
                $doi = $currentPublication->getDoi();
                if (isset($doi)) {
                    $item['Item_ID'][] = [
                        'Type' => 'DOI',
                        'Value' => $doi,
                    ];
                }

                $datePublished = $submission->getOriginalPublication()->getData('datePublished');
                foreach ($this->attributesToShow as $attributeToShow) {
                    if ($attributeToShow == 'Data_Type') {
                        $item['Data_Type'] = self::DATA_TYPE;
                    } elseif ($attributeToShow == 'Access_Type') {
                        $item['Access_Type'] = self::ACCESS_TYPE;
                    } elseif ($attributeToShow == 'Access_Method') {
                        $item['Access_Method'] = self::ACCESS_METHOD;
                    } elseif ($attributeToShow == 'YOP') {
                        $item['YOP'] = date('Y', strtotime($datePublished));
                    }
                    // Section_Type is not in attributes_to_show
                    // so do not consider it here
                }

                $performances = [];
                foreach ($results as $result) {
                    // if granularity=Month, the results will contain metrics for each month
                    // else the results will only contain the summarized metrics for the whole period
                    if (isset($result->month)) {
                        $periodBeginDate = date_format(date_create($result->month . '01'), 'Y-m-01');
                        $periodEndDate = date_format(date_create($result->month . '01'), 'Y-m-t');
                    } else {
                        $periodBeginDate = date_format(date_create($this->beginDate), 'Y-m-01');
                        $periodEndDate = date_format(date_create($this->endDate), 'Y-m-t');
                    }
                    $periodMetrics['Period'] = [
                        'Begin_Date' => $periodBeginDate,
                        'End_Date' => $periodEndDate,
                    ];

                    $instances = [];
                    // consider section_type filter
                    $metrics['Total_Item_Investigations'] = $metrics['Unique_Item_Investigations'] =
                    $metrics['Total_Item_Requests'] = $metrics['Unique_Item_Requests'] = 0;
                    if (in_array('Book', $this->sectionTypes)) {
                        $metrics['Total_Item_Investigations'] += $result->metric_book_investigations;
                        $metrics['Unique_Item_Investigations'] += $result->metric_book_investigations_unique;
                        $metrics['Total_Item_Requests'] += $result->metric_book_requests;
                        $metrics['Unique_Item_Requests'] += $result->metric_book_requests_unique;
                    }
                    if (in_array('Chapter', $this->sectionTypes)) {
                        $metrics['Total_Item_Investigations'] += $result->metric_chapter_investigations;
                        $metrics['Unique_Item_Investigations'] += $result->metric_chapter_investigations_unique;
                        $metrics['Total_Item_Requests'] += $result->metric_chapter_requests;
                        $metrics['Unique_Item_Requests'] += $result->metric_chapter_requests_unique;
                    }
                    if (in_array('Title', $this->sectionTypes)) {
                        $metrics['Unique_Title_Investigations'] = $result->metric_title_investigations_unique;
                        $metrics['Unique_Title_Requests'] = $result->metric_title_requests_unique;
                    }
                    // filter here by requested metric types
                    foreach ($this->metricTypes as $metricType) {
                        if (array_key_exists($metricType, $metrics) && $metrics[$metricType] > 0) {
                            $instances[] = [
                                'Metric_Type' => $metricType,
                                'Count' => (int) $metrics[$metricType]
                            ];
                        }
                    }
                    $periodMetrics['Instance'] = $instances;
                    $performances[] = $periodMetrics;
                }
                $item['Performance'] = $performances;
                $items[] = $item;
            }
        }

        return $items;
    }

    /** Get TSV report column names */
    public function getTSVColumnNames(): array
    {
        $columnRow = ['Title', 'Publisher', 'Publisher ID', 'Platform', 'DOI', 'Proprietary_ID', 'ISBN', 'Print_ISSN', 'Online_ISSN', 'URI'];

        if (in_array('Data_Type', $this->attributesToShow)) {
            array_push($columnRow, 'Data_Type');
        }
        if (in_array('Section_Type', $this->attributesToShow)) {
            array_push($columnRow, 'Section_Type');
        }
        if (in_array('YOP', $this->attributesToShow)) {
            array_push($columnRow, 'YOP');
        }
        if (in_array('Access_Type', $this->attributesToShow)) {
            array_push($columnRow, 'Access_Type');
        }
        if (in_array('Access_Method', $this->attributesToShow)) {
            array_push($columnRow, 'Access_Method');
        }

        array_push($columnRow, 'Metric_Type', 'Reporting_Period_Total');

        if ($this->granularity == 'Month') {
            $period = $this->getMonthlyDatePeriod();
            foreach ($period as $dt) {
                array_push($columnRow, $dt->format('M-Y'));
            }
        }

        return [$columnRow];
    }

    /** Get TSV report rows */
    public function getTSVReportItems(): array
    {
        $results = $this->getQueryResults();

        // Group result by submission ID
        // If Section_Type is attribute to show, group additionally by section type
        // Also filter results by requested section and metric types
        $resultsGroupedBySubmission = $resultRows = [];
        foreach ($results as $result) {
            $includeResult = false;
            if (in_array('Book', $this->sectionTypes) &&
                !empty(array_intersect($this->metricTypes, ['Total_Item_Investigations', 'Unique_Item_Investigations', 'Total_Item_Requests', 'Unique_Item_Requests'])) &&
                ($result->metric_book_investigations > 0 || $result->metric_book_investigations_unique > 0 || $result->metric_book_requests > 0 || $result->metric_book_requests_unique > 0)) {
                if (in_array('Section_Type', $this->attributesToShow)) {
                    $resultsGroupedBySubmission[$result->submission_id]['Book'][] = $result;
                }
                $includeResult = true;
            }
            if (in_array('Chapter', $this->sectionTypes) &&
                !empty(array_intersect($this->metricTypes, ['Total_Item_Investigations', 'Unique_Item_Investigations', 'Total_Item_Requests', 'Unique_Item_Requests'])) &&
                ($result->metric_chapter_investigations > 0 || $result->metric_chapter_investigations_unique > 0 || $result->metric_chapter_requests > 0 || $result->metric_chapter_requests_unique > 0)) {
                if (in_array('Section_Type', $this->attributesToShow)) {
                    $resultsGroupedBySubmission[$result->submission_id]['Chapter'][] = $result;
                }
                $includeResult = true;
            }
            if (in_array('Title', $this->sectionTypes) &&
                !empty(array_intersect($this->metricTypes, ['Unique_Title_Investigations', 'Unique_Title_Requests'])) &&
                ($result->metric_title_investigations_unique > 0 || $result->metric_title_requests_unique > 0)) {
                if (in_array('Section_Type', $this->attributesToShow)) {
                    $resultsGroupedBySubmission[$result->submission_id]['Title'][] = $result;
                }
                $includeResult = true;
            }
            if (!in_array('Section_Type', $this->attributesToShow) && $includeResult) {
                $resultsGroupedBySubmission[$result->submission_id][] = $result;
            }
        }

        foreach ($resultsGroupedBySubmission as $submissionId => $submissionResults) {
            // Section_Type is in attributes_to_show, the query results are grouped by section type
            if (in_array('Section_Type', $this->attributesToShow)) {
                foreach ($submissionResults as $sectionType => $results) {
                    $results = collect($results);

                    // get total numbers for every metric type
                    $metricsTotal['Total_Item_Investigations'] = $metricsTotal['Unique_Item_Investigations'] =
                    $metricsTotal['Total_Item_Requests'] = $metricsTotal['Unique_Item_Requests'] = 0;
                    switch ($sectionType) {
                        case 'Book':
                            $metricsTotal['Total_Item_Investigations'] = $results->pluck('metric_book_investigations')->sum();
                            $metricsTotal['Unique_Item_Investigations'] = $results->pluck('metric_book_investigations_unique')->sum();
                            $metricsTotal['Total_Item_Requests'] = $results->pluck('metric_book_requests')->sum();
                            $metricsTotal['Unique_Item_Requests'] = $results->pluck('metric_book_requests_unique')->sum();
                            break;
                        case 'Chapter':
                            $metricsTotal['Total_Item_Investigations'] = $results->pluck('metric_chapter_investigations')->sum();
                            $metricsTotal['Unique_Item_Investigations'] = $results->pluck('metric_chapter_investigations_unique')->sum();
                            $metricsTotal['Total_Item_Requests'] = $results->pluck('metric_chapter_requests')->sum();
                            $metricsTotal['Unique_Item_Requests'] = $results->pluck('metric_chapter_requests_unique')->sum();
                            break;
                        case 'Title':
                            $metricsTotal['Unique_Title_Investigations'] = $results->pluck('metric_title_investigations_unique')->sum();
                            $metricsTotal['Unique_Title_Requests'] = $results->pluck('metric_title_requests_unique')->sum();
                            break;
                    }

                    // Get the submission properties
                    $submission = Repo::submission()->get($submissionId);
                    if (!$submission || !$submission->getOriginalPublication()) {
                        break;
                    }
                    $currentPublication = $submission->getCurrentPublication();
                    $submissionLocale = $submission->getData('locale');
                    $itemTitle = $currentPublication->getLocalizedTitle($submissionLocale);
                    $doi = $currentPublication->getDoi();
                    $datePublished = $submission->getOriginalPublication()->getData('datePublished');

                    // filter here by requested metric types
                    foreach ($this->metricTypes as $metricType) {
                        if (array_key_exists($metricType, $metricsTotal) && $metricsTotal[$metricType] > 0) {
                            // construct the result row
                            $resultRow = [
                                $itemTitle, // Title
                                $this->context->getData('publisherInstitution'), // Publisher
                                '', // Publisher ID
                                $this->platformName, // Platform
                                $doi ?? '', // DOI
                                $this->platformId . ':' . $submissionId, // Proprietary_ID
                                '', // ISBN
                                '', // Print_ISSN
                                '', // Online_ISSN
                                '', // URI
                            ];

                            if (in_array('Data_Type', $this->attributesToShow)) {
                                array_push($resultRow, self::DATA_TYPE); // Data_Type
                            }
                            if (in_array('Section_Type', $this->attributesToShow)) {
                                // do not display section type for title metrics
                                if ($sectionType != 'Title') {
                                    array_push($resultRow, $sectionType); // Section_Type
                                } else {
                                    array_push($resultRow, '');
                                }
                            }
                            if (in_array('YOP', $this->attributesToShow)) {
                                array_push($resultRow, date('Y', strtotime($datePublished))); // YOP
                            }
                            if (in_array('Access_Type', $this->attributesToShow)) {
                                array_push($resultRow, self::ACCESS_TYPE); // Access_Type
                            }
                            if (in_array('Access_Method', $this->attributesToShow)) {
                                array_push($resultRow, self::ACCESS_METHOD); // Access_Method
                            }
                            array_push($resultRow, $metricType); // Metric_Type
                            array_push($resultRow, $metricsTotal[$metricType]); // Reporting_Period_Total

                            if ($this->granularity == 'Month') { // metrics for each month in the given period
                                $period = $this->getMonthlyDatePeriod();
                                foreach ($period as $dt) {
                                    $month = $dt->format('Ym');
                                    $result = $results->firstWhere('month', '=', $month);
                                    if ($result === null) {
                                        array_push($resultRow, '0');
                                    } else {
                                        switch ($sectionType) {
                                            case 'Book':
                                                $metrics['Total_Item_Investigations'] = $result->metric_book_investigations;
                                                $metrics['Unique_Item_Investigations'] = $result->metric_book_investigations_unique;
                                                $metrics['Total_Item_Requests'] = $result->metric_book_requests;
                                                $metrics['Unique_Item_Requests'] = $result->metric_book_requests_unique;
                                                break;
                                            case 'Chapter':
                                                $metrics['Total_Item_Investigations'] = $result->metric_chapter_investigations;
                                                $metrics['Unique_Item_Investigations'] = $result->metric_chapter_investigations_unique;
                                                $metrics['Total_Item_Requests'] = $result->metric_chapter_requests;
                                                $metrics['Unique_Item_Requests'] = $result->metric_chapter_requests_unique;
                                                break;
                                            case 'Title':
                                                $metrics['Unique_Title_Investigations'] = $result->metric_title_investigations_unique;
                                                $metrics['Unique_Title_Requests'] = $result->metric_title_requests_unique;
                                                break;
                                        }
                                        array_push($resultRow, $metrics[$metricType]);
                                    }
                                }
                            }
                            $resultRows[] = $resultRow;
                        }
                    }
                }
            } else { // Section_Type is not in attributes_to_show
                $results = collect($submissionResults);

                $metricsTotal['Total_Item_Investigations'] = $metricsTotal['Unique_Item_Investigations'] =
                $metricsTotal['Total_Item_Requests'] = $metricsTotal['Unique_Item_Requests'] = 0;
                if (in_array('Book', $this->sectionTypes)) {
                    $metricsTotal['Total_Item_Investigations'] += $results->pluck('metric_book_investigations')->sum();
                    $metricsTotal['Unique_Item_Investigations'] += $results->pluck('metric_book_investigations_unique')->sum();
                    $metricsTotal['Total_Item_Requests'] += $results->pluck('metric_book_requests')->sum();
                    $metricsTotal['Unique_Item_Requests'] += $results->pluck('metric_book_requests_unique')->sum();
                }
                if (in_array('Chapter', $this->sectionTypes)) {
                    $metricsTotal['Total_Item_Investigations'] += $results->pluck('metric_chapter_investigations')->sum();
                    $metricsTotal['Unique_Item_Investigations'] += $results->pluck('metric_chapter_investigations_unique')->sum();
                    $metricsTotal['Total_Item_Requests'] += $results->pluck('metric_chapter_requests')->sum();
                    $metricsTotal['Unique_Item_Requests'] += $results->pluck('metric_chapter_requests_unique')->sum();
                }
                if (in_array('Title', $this->sectionTypes)) {
                    $metricsTotal['Unique_Title_Investigations'] = $results->pluck('metric_title_investigations_unique')->sum();
                    $metricsTotal['Unique_Title_Requests'] = $results->pluck('metric_title_requests_unique')->sum();
                }

                // Get the submission properties
                $submission = Repo::submission()->get($submissionId);
                if (!$submission || !$submission->getOriginalPublication()) {
                    break;
                }
                $currentPublication = $submission->getCurrentPublication();
                $submissionLocale = $submission->getData('locale');
                $itemTitle = $currentPublication->getLocalizedTitle($submissionLocale);
                $doi = $currentPublication->getDoi();
                $datePublished = $submission->getOriginalPublication()->getData('datePublished');

                // filter here by requested metric types
                foreach ($this->metricTypes as $metricType) {
                    if (array_key_exists($metricType, $metricsTotal) && $metricsTotal[$metricType] > 0) {
                        // construct the result row
                        $resultRow = [
                            $itemTitle, // Title
                            $this->context->getData('publisherInstitution'), // Publisher
                            '', // Publisher ID
                            $this->platformName, // Platform
                            $doi ?? '', // DOI
                            $this->platformId . ':' . $submissionId, // Proprietary_ID
                            '', // ISBN
                            '', // Print_ISSN
                            '', // Online_ISSN
                            '', // URI
                        ];

                        if (in_array('Data_Type', $this->attributesToShow)) {
                            array_push($resultRow, self::DATA_TYPE); // Data_Type
                        }
                        // Section_Type is not in attributes_to_show
                        // so no need to consider it here
                        if (in_array('YOP', $this->attributesToShow)) {
                            array_push($resultRow, date('Y', strtotime($datePublished))); // YOP
                        }
                        if (in_array('Access_Type', $this->attributesToShow)) {
                            array_push($resultRow, self::ACCESS_TYPE); // Access_Type
                        }
                        if (in_array('Access_Method', $this->attributesToShow)) {
                            array_push($resultRow, self::ACCESS_METHOD); // Access_Method
                        }
                        array_push($resultRow, $metricType); // Metric_Type
                        array_push($resultRow, $metricsTotal[$metricType]); // Reporting_Period_Total

                        if ($this->granularity == 'Month') { // metrics for each month in the given period
                            $period = $this->getMonthlyDatePeriod();
                            foreach ($period as $dt) {
                                $month = $dt->format('Ym');
                                $result = $results->firstWhere('month', '=', $month);
                                if ($result === null) {
                                    array_push($resultRow, '0');
                                } else {
                                    // consider section_type filter
                                    $metrics['Total_Item_Investigations'] = $metrics['Unique_Item_Investigations'] =
                                    $metrics['Total_Item_Requests'] = $metrics['Unique_Item_Requests'] = 0;
                                    if (in_array('Book', $this->sectionTypes)) {
                                        $metrics['Total_Item_Investigations'] += $result->metric_book_investigations;
                                        $metrics['Unique_Item_Investigations'] += $result->metric_book_investigations_unique;
                                        $metrics['Total_Item_Requests'] += $result->metric_book_requests;
                                        $metrics['Unique_Item_Requests'] += $result->metric_book_requests_unique;
                                    }
                                    if (in_array('Chapter', $this->sectionTypes)) {
                                        $metrics['Total_Item_Investigations'] += $result->metric_chapter_investigations;
                                        $metrics['Unique_Item_Investigations'] += $result->metric_chapter_investigations_unique;
                                        $metrics['Total_Item_Requests'] += $result->metric_chapter_requests;
                                        $metrics['Unique_Item_Requests'] += $result->metric_chapter_requests_unique;
                                    }
                                    if (in_array('Title', $this->sectionTypes)) {
                                        $metrics['Unique_Title_Investigations'] = $result->metric_title_investigations_unique;
                                        $metrics['Unique_Title_Requests'] = $result->metric_title_requests_unique;
                                    }
                                    array_push($resultRow, $metrics[$metricType]);
                                }
                            }
                        }
                        $resultRows[] = $resultRow;
                    }
                }
            }
        }

        return $resultRows;
    }

    /** Get report specific form fields */
    public static function getReportSettingsFormFields(): array
    {
        $formFields = parent::getCommonReportSettingsFormFields();

        $metricTypes = [
            'Total_Item_Investigations',
            'Unique_Item_Investigations',
            'Total_Item_Requests',
            'Unique_Item_Requests',
            'Unique_Title_Investigations',
            'Unique_Title_Requests'
        ];
        $metricTypeOptions = [];
        foreach ($metricTypes as $metricType) {
            $metricTypeOptions[] = ['value' => $metricType, 'label' => $metricType];
        }
        $formFields[] = new FieldOptions('metric_type', [
            'label' => __('manager.statistics.counterR5Report.settings.metricType'),
            'options' => $metricTypeOptions,
            'groupId' => 'default',
            'value' => $metricTypes,
        ]);

        $attributesToShow = ['Data_Type', 'Access_Method', 'Section_Type', 'Access_Type', 'YOP'];
        $attributesToShowOptions = [];
        foreach ($attributesToShow as $attributeToShow) {
            $attributesToShowOptions[] = ['value' => $attributeToShow, 'label' => $attributeToShow];
        }
        $formFields[] = new FieldOptions('attributes_to_show', [
            'label' => __('manager.statistics.counterR5Report.settings.attributesToShow'),
            'options' => $attributesToShowOptions,
            'groupId' => 'default',
            'value' => [],
        ]);

        $formFields[] = new FieldText('yop', [
            'label' => __('manager.statistics.counterR5Report.settings.yop'),
            'description' => __('manager.statistics.counterR5Report.settings.date.yop.description'),
            'size' => 'small',
            'isMultilingual' => false,
            'isRequired' => false,
            'groupId' => 'default',
        ]);

        $formFields[] = new FieldOptions('granularity', [
            'label' => __('manager.statistics.counterR5Report.settings.excludeMonthlyDetails'),
            'options' => [
                ['value' => true, 'label' => __('manager.statistics.counterR5Report.settings.excludeMonthlyDetails')],
            ],
            'value' => false,
            'groupId' => 'default',
        ]);

        return $formFields;
    }
}
