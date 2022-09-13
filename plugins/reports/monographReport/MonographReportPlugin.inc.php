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
 * @brief Monograph report plugin
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

			import('classes.submission.Submission'); // STATUS_PUBLISHED constant
			$monographs = Services::get('submission')->getMany([
				'contextId' => $press->getId(),
				'status' => STATUS_PUBLISHED,
				'orderBy' => 'dateSubmitted',
				'orderDirection' => 'ASC'
			]);
			/** @var Monograph */
			foreach ($monographs as $monograph) {
				$row = [];
				foreach ($fields as $getter) {
					$row[] = $getter($monograph);
				}
				$output->fputcsv($row);
			}
		} finally {
			$output = null;
		}
	}

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
			__('common.publication') => function (Submission $monograph) {
				return $monograph->getCurrentPublication()->getLocalizedTitle();
			},
			__('submission.authors') => function (Submission $monograph) {
				$authors = [];
				/** @var Author */
				foreach ($monograph->getCurrentPublication()->getData('authors') ?? [] as $author) {
					$authors[] = $author->getFullName();
				}
				return implode("\n", $authors);
			},
			__('catalog.published') => function (Submission $monograph) {
				return $monograph->getCurrentPublication()->getData('datePublished');
			},
			__('metadata.property.displayName.series') => function (Submission $monograph) use ($seriesList) {
				return ($series = $seriesList[$monograph->getCurrentPublication()->getData('seriesId')] ?? null)
					? $series->getLocalizedTitle()
					: null;
			},
			__('submission.submit.seriesPosition') => function (Submission $monograph) {
				return $monograph->getCurrentPublication()->getData('seriesPosition');
			},
			__('submission.identifiers') => function (Submission $monograph) use ($seriesList) {
				$format = static function (string $name, $value): string
				{
					return __('plugins.reports.monographReport.identifierFormat', ['name' => $name, 'value' => $value]);
				};
				$identifiers = [];
				$publication = $monograph->getCurrentPublication();
				if ($series = $seriesList[$publication->getData('seriesId')] ?? null) {
					if ($issn = $series->getOnlineISSN()) {
						$identifiers[] = $format(  __('catalog.manage.series.onlineIssn'), $issn);
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
			__('common.url') => function (Submission $monograph) use ($press, $request) {
				return $request->url($press->getPath(), 'monograph', 'view', [$monograph->getId()]);
			},
			__('metadata.property.displayName.doi') => function (Submission $monograph) {
				return $monograph->getCurrentPublication()->getStoredPubId('doi');
			},
			__('catalog.categories') => function (Submission $monograph) use ($categoryList) {
				$categories = [];
				foreach ($monograph->getCurrentPublication()->getData('categoryIds') ?? [] as $id) {
					if ($category = $categoryList[$id] ?? null) {
						$categories[] = $category->getLocalizedTitle();
					}
				}
				return implode("\n", $categories);
			},
		];
	}
}
