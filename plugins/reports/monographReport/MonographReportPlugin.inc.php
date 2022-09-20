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
 * @see MonographReportDAO
 *
 * @brief The monograph report plugin will output a .csv file containing basic
 * information (title, DOI, etc.) from published monographs
 */

import('lib.pkp.classes.plugins.ReportPlugin');

class MonographReportPlugin extends ReportPlugin {
	/**
	 * @copydoc Plugin::register()
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
		return static::class;
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
		$press = $request->getContext();
		if (!$press) {
			throw new Exception('The monograph report requires a context');
		}

		header('content-type: text/comma-separated-values');
		$filename = 'monograph-report-' . $press->getAcronym(AppLocale::getLocale()) . '-' . date('Ymd') . '.csv';
		header("content-disposition: attachment; filename={$filename}");


		$fields = $this->_getFieldMapper($press, $request);
		$output = new SplFileObject('php://output', 'w');
		try {
			// UTF-8 BOM to force the file to be read with the right encoding
			$output->fwrite("\xEF\xBB\xBF");
			$output->fputcsv(array_keys($fields));

			import('classes.submission.Submission'); // STATUS_PUBLISHED constant
			$monographs = Services::get('submission')->getMany([
				'contextId' => $press->getId(),
				'status' => STATUS_PUBLISHED,
				'orderBy' => 'dateSubmitted',
				'orderDirection' => 'ASC'
			]);
			/** @var Submission */
			foreach ($monographs as $monograph) {
				$row = [];
				foreach ($fields as $getter) {
					$row[] = $getter($monograph->getCurrentPublication());
				}
				$output->fputcsv($row);
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
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);

		/** @var SeriesDAO */
		$seriesDAO = DAORegistry::getDAO('SeriesDAO');
		/** @var CategoryDAO */
		$categoryDao = DAORegistry::getDAO('CategoryDAO');

		/** @var Series[] */
		$seriesList = $seriesDAO->getByContextId($press->getId())->toAssociativeArray();
		/** @var Category[] */
		$categoryList = $categoryDao->getByContextId($press->getId())->toAssociativeArray();

		return [
			__('common.publication') => function (Publication $publication) {
				return $publication->getLocalizedTitle();
			},
			__('submission.authors') => function (Publication $publication) {
				$authors = [];
				/** @var Author */
				foreach ($publication->getData('authors') ?? [] as $author) {
					$authors[] = $author->getFullName();
				}
				return implode("\n", $authors);
			},
			__('catalog.published') => function (Publication $publication) {
				return $publication->getData('datePublished');
			},
			__('metadata.property.displayName.series') => function (Publication $publication) use ($seriesList) {
				return ($series = $seriesList[$publication->getData('seriesId')] ?? null)
					? $series->getLocalizedTitle()
					: null;
			},
			__('submission.submit.seriesPosition') => function (Publication $publication) {
				return $publication->getData('seriesPosition');
			},
			__('submission.identifiers') => function (Publication $publication) use ($seriesList) {
				$format = static function (string $name, $value): string {
					return __('plugins.reports.monographReport.identifierFormat', ['name' => $name, 'value' => $value]);
				};
				$identifiers = [];
				if ($series = $seriesList[$publication->getData('seriesId')] ?? null) {
					if ($issn = $series->getOnlineISSN()) {
						$identifiers[] = $format(__('catalog.manage.series.onlineIssn'), $issn);
					}

					if ($issn = $series->getPrintISSN()) {
						$identifiers[] = $format(__('catalog.manage.series.printIssn'), $issn);
					}
				}
				if ($source = $publication->getLocalizedData('source')) {
					$identifiers[] = $format(__('common.source'), $source);
				}
				/** @var PublicationFormat */
				foreach ($publication->getData('publicationFormats') as $publicationFormat) {
					/** @var IdentificationCode */
					foreach ($publicationFormat->getIdentificationCodes()->toIterator() as $identificationCode) {
						$identifiers[] = $format($identificationCode->getNameForONIXCode(), $identificationCode->getValue());
					}
				}
				return implode("\n", $identifiers);
			},
			__('common.url') => function (Publication $publication) use ($press, $request) {
				return $request->url($press->getPath(), 'monograph', 'view', [$publication->getData('submissionId')]);
			},
			__('metadata.property.displayName.doi') => function (Publication $publication) {
				return $publication->getStoredPubId('doi');
			},
			__('catalog.categories') => function (Publication $publication) use ($categoryList) {
				$categories = [];
				foreach ($publication->getData('categoryIds') ?? [] as $id) {
					if ($category = $categoryList[$id] ?? null) {
						$categories[] = $category->getLocalizedTitle();
					}
				}
				return implode("\n", $categories);
			},
		];
	}
}
