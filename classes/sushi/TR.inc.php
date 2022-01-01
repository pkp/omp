<?php

/**
* @file classes/sushi/TR.inc.php
*
* Copyright (c) 2013-2021 Simon Fraser University
* Copyright (c) 2003-2021 John Willinsky
* Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
*
* @class TR
* @ingroup sushi
*
* @brief COUNTER R5 SUSHI Title Master Report (TR).
*
*/

namespace APP\sushi;

use APP\core\Application;
use APP\core\Services;
use APP\facades\Repo;
use PKP\statistics\PKPStatisticsHelper;

class TR
{
    /** ID of the context the report is for. */
    public int $contextId;

    // Data used for the report header:
    /** Platform name, either press name or name defined in site settings */
    public string $platformName;
    /** Platform ID is used as namespace for item proprietary IDs. */
    public string $platformId;
    /** The requested customer ID is the DB institution_id */
    public int $customerId;
    /** Institution name */
    public string $institutionName;
    /** Institution ID. Currently we only provide proprietary and ROR. */
    public ?array $institutionId;

    // Report filters values:
    /** The following filters are always the same in our case. */
    public const ACCESS_TYPE = 'OA_Gold';
    public const DATA_TYPE = 'Book';
    public const ACCESS_METHOD = 'Regular';
    /** The requested period, begin and end date, the report should be created for. */
    public string $beginDate;
    public string $endDate;
    /** Requested section types */
    public array $sectionTypes = ['Book', 'Chapter', 'Title'];
    /** Requested Year of Publication (YOP) */
    public array $yearsOfPublication = [];
    /** Requested metric types */
    public array $metricTypes = ['Total_Item_Investigations', 'Unique_Item_Investigations', 'Total_Item_Requests', 'Unique_Item_Requests', 'Unique_Title_Investigations', 'Unique_Title_Requests'];

    /** Warnings that will be displayed in the report header. */
    public array $exceptions = [];

    /** List of all filter and attributes requested and applied that will displayed in the report header.  */
    protected array $filters = [];
    protected array $attributes = [];

    /** Attributes to show in the report and the metrics will be grouped by. */
    protected array $attributesToShow = [];
    protected string $granularity = 'Month';

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
     * Get report release.
     */
    public function getRelease(): string
    {
        return '5';
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
        return ['customer_id', 'begin_date', 'end_date', 'platform', 'item_id', 'metric_type', 'data_type', 'section_type', 'yop', 'access_type', 'access_method', 'attributes_to_show', 'granularity'];
    }

    /**
     * Get filters supported by this report.
     */
    public function getSupportedFilters(): array
    {
        return [
            ['name' => 'YOP', 'supportedValues' => [], 'param' => 'yop'],
            ['name' => 'Item_Id', 'supportedValues' => [$this->contextId], 'param' => 'item_id'],
            ['name' => 'Access_Type', 'supportedValues' => ['OA_Gold'], 'param' => 'access_type'],
            ['name' => 'Section_Type', 'supportedValues' => ['Book', 'Chapter'], 'param' => 'section_type'],
            ['name' => 'Data_Type', 'supportedValues' => ['Book'], 'param' => 'data_type'],
            ['name' => 'Metric_Type', 'supportedValues' => ['Total_Item_Investigations', 'Unique_Item_Investigations', 'Total_Item_Requests', 'Unique_Item_Requests', 'Unique_Title_Investigations', 'Unique_Title_Requests'], 'param' => 'metric_type'],
            ['name' => 'Access_Method', 'supportedValues' => ['Regular'], 'param' => 'access_method'],
        ];
    }

    /**
     * Get attributes supported by this report.
     *
     * The attributes will be displayed and they define what the metrics will be aggregated by.
     * Data_Type, Access_Method, and Access_Type are currently always the same for this report,
     * so they will only be displayed and not considered for metrics aggregation.
     * The only attributes considered for metrics aggregation are YOP and granularity=Month.
     */
    public function getSupportedAttributes(): array
    {
        return [
            ['name' => 'Attributes_To_Show', 'supportedValues' => ['Data_Type', 'Access_Method', 'Section_Type', 'Access_Type', 'YOP'], 'param' => 'attributes_to_show'],
            ['name' => 'granularity', 'supportedValues' => ['Month', 'Totals'], 'param' => 'granularity'],
        ];
    }

    /**
     * Get used filters that will be displayed in the report header.
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get used attributes that will be displayed in the report header.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set filters based on the requested parameters.
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
        foreach ($filters as $filter) {
            switch ($filter['Name']) {
                case 'Begin_Date':
                    $this->beginDate = $filter['Value'];
                    break;
                case 'End_Date':
                    $this->endDate = $filter['Value'];
                    break;
                case 'Metric_Type':
                    $this->metricTypes = explode('|', $filter['Value']);
                    break;
                case 'YOP':
                    $this->yearsOfPublication = explode('|', $filter['Value']);
                    break;
                case 'Section_Type':
                    $this->sectionTypes = explode('|', $filter['Value']);
            }
        }
        // check section_type and metric_type missmatch ?
    }

    /**
     * Set attributes based on the requested parameters.
     *
     * The attributes will be displayed and they define what the metrics will be aggregated by.
     * Data_Type, Access_Method, and Access_Type are currently always the same for this report,
     * so they will only be displayed and not considered for metrics aggregation.
     * The only attributes considered for metrics aggregation are YOP and granularity=Month.
     * S. getReportItems()
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
        foreach ($attributes as $attribute) {
            switch ($attribute['Name']) {
                case 'Attributes_To_Show':
                    $this->attributesToShow = explode('|', $attribute['Value']);
                    break;
                case 'granularity':
                    $this->granularity = $attribute['Value'];
                    break;
            }
        }
    }

    /**
     * Get report items
     */
    public function getReportItems(): array
    {
        // prepare stats service parameters
        $allowedParams['contextIds'] = $this->contextId;
        $allowedParams['institutionId'] = $this->customerId;
        $allowedParams['dateStart'] = $this->beginDate;
        $allowedParams['dateEnd'] = $this->endDate;
        $allowedParams['yearsOfPublication'] = $this->yearsOfPublication;
        // do not consider section_type filter now, but later when grouping by submission
        // do not consider metric_type filter now, but for display

        $statsService = Services::get('sushiStats');
        $metricsQB = $statsService->getQueryBuilder($allowedParams);
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
        // get metrics results as array
        $results = $metricsQB->get()->toArray();
        if (empty($results)) {
            $this->exceptions[] = [
                'Code' => 3030,
                'Severity' => 'Error',
                'Message' => 'No Usage Available for Requested Dates',
                'Data' => __('sushi.exception.3030', ['beginDate' => $this->beginDate, 'endDate' => $this->endDate])
            ];
        }

        $context = Application::getContextDAO()->getById($this->contextId);

        // Group resulty by submission ID
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
                    if (!$submission) {
                        break;
                    }
                    $currentPublication = $submission->getCurrentPublication();
                    $submissionLocale = $submission->getData('locale');
                    $itemTitle = $currentPublication->getLocalizedTitle($submissionLocale);

                    $item = [
                        'Title' => $itemTitle,
                        'Platform' => $this->platformName,
                        'Publisher' => $context->getData('publisherInstitution'),
                    ];
                    $item['Item_ID'][] = [
                        'Type' => 'Proprietary',
                        'Value' => $this->platformId . ':' . $submissionId,
                    ];
                    $doi = $currentPublication->getStoredPubId('pub-id::doi');
                    if (isset($doi)) {
                        $item['Item_ID'][] = [
                            'Type' => 'DOI',
                            'Value' => $doi,
                        ];
                    }

                    $datePublished = $submission->getFirstPublication()->getData('datePublished');
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

                    $perfomances = [];
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
                        $perfomances[] = $periodMetrics;
                    }
                    $item['Performance'] = $perfomances;
                    $items[] = $item;
                }
            } else { // Section_Type is not in attributes_to_show
                $results = $submissionResults;
                // Get the submission properties
                $submission = Repo::submission()->get($submissionId);
                if (!$submission) {
                    break;
                }
                $currentPublication = $submission->getCurrentPublication();
                $submissionLocale = $submission->getData('locale');
                $itemTitle = $currentPublication->getLocalizedTitle($submissionLocale);

                $item = [
                    'Title' => $itemTitle,
                    'Platform' => $this->platformName,
                    'Publisher' => $context->getData('publisherInstitution'),
                ];
                $item['Item_ID'][] = [
                    'Type' => 'Proprietary',
                    'Value' => $this->platformId . ':' . $submissionId,
                ];
                $doi = $currentPublication->getStoredPubId('pub-id::doi');
                if (isset($doi)) {
                    $item['Item_ID'][] = [
                        'Type' => 'DOI',
                        'Value' => $doi,
                    ];
                }

                $datePublished = $submission->getFirstPublication()->getData('datePublished');
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

                $perfomances = [];
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
                    $perfomances[] = $periodMetrics;
                }
                $item['Performance'] = $perfomances;
                $items[] = $item;
            }
        }

        return $items;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\sushi\TR', '\TR');
}
