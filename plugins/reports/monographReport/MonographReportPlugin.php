<?php

/**
 * @file plugins/reports/monographReport/MonographReportPlugin.inc.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographReportPlugin
 * @ingroup plugins_reports_monographReport
 *
 * @see MonographReportDAO
 *
 * @brief Monograph report plugin
 */

namespace APP\plugins\reports\monographReport;

use APP\author\Author;
use APP\core\Request;
use APP\facades\Repo;
use APP\press\Press;
use APP\press\Series;
use APP\press\SeriesDAO;
use APP\publication\Publication;
use APP\publicationFormat\IdentificationCode;
use APP\publicationFormat\PublicationFormat;
use APP\submission\Collector;
use APP\submission\Submission;
use Exception;
use PKP\category\Category;
use PKP\db\DAORegistry;
use PKP\plugins\ReportPlugin;
use SplFileObject;

class MonographReportPlugin extends ReportPlugin
{
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
    public function display(array $args, Request $request): void
    {
        $press = $request->getContext();
        if (!$press) {
            throw new Exception('The monograph report requires a context');
        }

        header('content-type: text/comma-separated-values');
        header('content-disposition: attachment; filename=monograph-report-' . date('Ymd') . '.csv');

        $fields = $this->_getFieldMapper($press, $request);
        $output = new SplFileObject('php://output', 'w');
        try {
            // UTF-8 BOM to force the file to be read with the right encoding
            $output->fwrite("\xEF\xBB\xBF");
            $output->fputcsv(array_keys($fields));

            $monographs = Repo::submission()->getCollector()
                ->filterByContextIds([$press->getId()])
                ->filterByStatus([Submission::STATUS_PUBLISHED])
                ->orderBy(Collector::ORDERBY_DATE_SUBMITTED, Collector::ORDER_DIR_ASC)
                ->getMany();

            /** @var Submission */
            foreach ($monographs as $monograph) {
                $output->fputcsv(array_map(fn (callable $getter) => $getter($monograph->getCurrentPublication()), $fields));
            }
        } finally {
            $output = null;
        }
    }

    /**
     * Retrieves a dictionary, where the keys represent the column titles and the values a value getter
     */
    private function _getFieldMapper(Press $press, Request $request): array
    {
        /** @var SeriesDAO */
        $seriesDAO = DAORegistry::getDAO('SeriesDAO');
        /** @var Series[] */
        $seriesList = $seriesDAO->getByContextId($press->getId())->toAssociativeArray();
        /** @var Category[] */
        $categoryList = Repo::category()->getCollector()
            ->filterByContextIds([$press->getId()])
            ->getMany()
            ->keyBy(fn (Category $category) => $category->getId())
            ->toArray();

        return [
            __('common.publication') => fn (Publication $publication) => $publication->getLocalizedTitle(),
            __('submission.authors') => fn (Publication $publication) => collect($publication->getData('authors'))
                ->map(fn (Author $author) => $author->getFullName())
                ->join("\n"),
            __('catalog.published') => fn (Publication $publication) => $publication->getData('datePublished'),
            __('metadata.property.displayName.series') => fn (Publication $publication) => $seriesList[$publication->getData('seriesId')]?->getLocalizedTitle(),
            __('submission.submit.seriesPosition') => fn (Publication $publication) => $publication->getData('seriesPosition'),
            __('submission.identifiers') => fn (Publication $publication) => collect()
                ->add([__('catalog.manage.series.onlineIssn'), $seriesList[$publication->getData('seriesId')]?->getOnlineISSN()])
                ->add([__('catalog.manage.series.printIssn'), $seriesList[$publication->getData('seriesId')]?->getPrintISSN()])
                ->add([__('common.source'), $publication->getLocalizedData('source')])
                ->concat(
                    collect($publication->getData('publicationFormats'))
                        ->map(
                            fn (PublicationFormat $pf) => collect($pf->getIdentificationCodes()->toIterator())
                                ->map(fn (IdentificationCode $ic) => [$ic->getNameForONIXCode(), $ic->getValue()])
                        )
                        ->flatten(1)
                )
                ->filter(fn (array $identifier) => trim(end($identifier)))
                ->map(fn (array $identifier) => __('plugins.reports.monographReport.identifierFormat', ['name' => reset($identifier), 'value' => end($identifier)]))
                ->join("\n"),
            __('common.url') => fn (Publication $publication) => $request->url($press->getPath(), 'monograph', 'view', [$publication->getData('submissionId')]),
            __('metadata.property.displayName.doi') => fn (Publication $publication) => $publication->getStoredPubId('doi'),
            __('catalog.categories') => fn (Publication $publication) => collect($publication->getData('categoryIds'))
                ->map(fn (int $id) => $categoryList[$id]?->getLocalizedTitle())
                ->join("\n")
        ];
    }
}
