<?php
/**
 * @file components/listPanels/CatalogListPanel.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogListPanel
 * @ingroup classes_components_listPanels
 *
 * @brief Instantiates and manages a UI component to list catalog entries.
 */
namespace APP\components\listPanels;

// Bring in orderby constants
import('classes.monograph.PublishedMonograph');

class CatalogListPanel extends \PKP\components\listPanels\ListPanel {

	/**
	 * @copydoc ListPanel::getConfig()
	 */
	public function getConfig() {
		\AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION);

		$request = \Application::get()->getRequest();
		$context = $request->getContext();

		list($catalogSortBy, $catalogSortDir) = explode('-', $context->getData('catalogSortOption'));
		$catalogSortBy = empty($catalogSortBy) ? ORDERBY_DATE_PUBLISHED : $catalogSortBy;
		$catalogSortDir = $catalogSortDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC';
		$config['catalogSortBy'] = $catalogSortBy;
		$config['catalogSortDir'] = $catalogSortDir;

		$this->getParams = array_merge(
			$this->getParams,
			[
				'status' => STATUS_PUBLISHED,
				'orderByFeatured' => true,
				'orderBy' => $catalogSortBy,
				'orderDirection' => $catalogSortDir,
				'isCurrentSubmissionVersion' => 1,
			]
		);

		$config = parent::getConfig();

		$config['i18n']['add'] = __('submission.catalogEntry.new');
		$config['i18n']['itemCount'] = __('submission.list.countMonographs');
		$config['i18n']['itemsOfTotal'] = __('submission.list.itemsOfTotalMonographs');
		$config['i18n']['featured'] = __('catalog.featured');
		$config['i18n']['newRelease'] = __('catalog.manage.feature.newRelease');
		$config['i18n']['featuredCategory'] = __('catalog.manage.categoryFeatured');
		$config['i18n']['newReleaseCategory'] = __('catalog.manage.feature.categoryNewRelease');
		$config['i18n']['featuredSeries'] = __('catalog.manage.seriesFeatured');
		$config['i18n']['newReleaseSeries'] = __('catalog.manage.feature.seriesNewRelease');
		$config['i18n']['catalogEntry'] = __('submission.catalogEntry');
		$config['i18n']['editCatalogEntry'] = __('submission.editCatalogEntry');
		$config['i18n']['viewSubmission'] = __('submission.catalogEntry.viewSubmission');
		$config['i18n']['saving'] = __('common.saving');
		$config['i18n']['orderFeatures'] = __('submission.list.orderFeatures');
		$config['i18n']['orderingFeatures'] = __('submission.list.orderingFeatures');
		$config['i18n']['orderingFeaturesSection'] = __('submission.list.orderingFeaturesSection');
		$config['i18n']['saveFeatureOrder'] = __('submission.list.saveFeatureOrder');
		$config['i18n']['cancel'] = __('common.cancel');
		$config['i18n']['isFeatured'] = __('catalog.manage.isFeatured');
		$config['i18n']['isNotFeatured'] = __('catalog.manage.isNotFeatured');
		$config['i18n']['isNewRelease'] = __('catalog.manage.isNewRelease');
		$config['i18n']['isNotNewRelease'] = __('catalog.manage.isNotNewRelease');
		$config['i18n']['paginationLabel'] = __('common.pagination.label');
		$config['i18n']['goToLabel'] = __('common.pagination.goToPage');
		$config['i18n']['pageLabel'] = __('common.pageNumber');
		$config['i18n']['nextPageLabel'] = __('common.pagination.next');
		$config['i18n']['previousPageLabel'] = __('common.pagination.previous');

		$config['addUrl'] = $request->getDispatcher()->url(
			$request,
			ROUTE_COMPONENT,
			null,
			'modals.submissionMetadata.SelectMonographHandler',
			'fetch',
			null
		);

		$config['catalogEntryUrl'] = $request->getDispatcher()->url(
			$request,
			ROUTE_COMPONENT,
			null,
			'modals.submissionMetadata.CatalogEntryHandler',
			'fetch',
			null,
			['stageId' => WORKFLOW_STAGE_ID_PRODUCTION, 'submissionId' => '__id__']
		);

		$config['filters'] = [];

		if ($context) {
			$categories = [];
			$categoryDao = \DAORegistry::getDAO('CategoryDAO');
			$categoriesResult = $categoryDao->getByContextId($context->getId());
			while (!$categoriesResult->eof()) {
				$category = $categoriesResult->next();
				list($categorySortBy, $categorySortDir) = explode('-', $category->getSortOption());
				$categorySortDir = empty($categorySortDir) ? $catalogSortDir : $categorySortDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC';
				$categories[] = [
					'param' => 'categoryIds',
					'value' => (int) $category->getId(),
					'title' => $category->getLocalizedTitle(),
					'sortBy' => $categorySortBy,
					'sortDir' => $categorySortDir,
				];
			}
			if (count($categories)) {
				$config['filters'][] = [
					'heading' => __('catalog.categories'),
					'filters' => $categories,
				];
			}

			$series = [];
			$seriesDao = \DAORegistry::getDAO('SeriesDAO');
			$seriesResult = $seriesDao->getByPressId($context->getId());
			while (!$seriesResult->eof()) {
				$seriesObj = $seriesResult->next();
				list($seriesSortBy, $seriesSortDir) = explode('-', $seriesObj->getSortOption());
				$seriesSortDir = empty($seriesSortDir) ? $catalogSortDir : $seriesSortDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC';
				$series[] = [
					'param' => 'seriesIds',
					'value' => (int) $seriesObj->getId(),
					'title' => $seriesObj->getLocalizedTitle(),
					'sortBy' => $seriesSortBy,
					'sortDir' => $seriesSortDir,
				];
			}
			if (count($series)) {
				$config['filters'][] = [
					'heading' => __('catalog.manage.series'),
					'filters' => $series,
				];
			}
		}

		// Attach a CSRF token for post requests
		$config['csrfToken'] = $request->getSession()->getCSRFToken();

		$templateMgr = \TemplateManager::getManager($request);
		$templateMgr->setConstant('ASSOC_TYPE_PRESS');
		$templateMgr->setConstant('ASSOC_TYPE_CATEGORY');
		$templateMgr->setConstant('ASSOC_TYPE_SERIES');

		return $config;
	}
}
