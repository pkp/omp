<?php
/**
 * @file components/listPanels/submissions/CatalogSubmissionsListPanel.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogSubmissionsListPanel
 * @ingroup classes_controllers_list
 *
 * @brief Instantiates and manages a UI component to list submissions.
 */
import('classes.components.listPanels.submissions.SubmissionsListPanel');
import('lib.pkp.classes.db.DBResultRange');
import('classes.monograph.PublishedMonograph');

class CatalogSubmissionsListPanel extends SubmissionsListPanel {

	/**
	 * @see PKPSubmissionsListPanel
	 */
	public function getConfig() {
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION);

		$request = Application::get()->getRequest();
		$context = $request->getContext();

		// Bring in orderby constants
		import('classes.monograph.PublishedMonographDAO');

		list($catalogSortBy, $catalogSortDir) = explode('-', $context->getData('catalogSortOption'));
		$catalogSortBy = empty($catalogSortBy) ? ORDERBY_DATE_PUBLISHED : $catalogSortBy;
		$catalogSortDir = $catalogSortDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC';
		$config['catalogSortBy'] = $catalogSortBy;
		$config['catalogSortDir'] = $catalogSortDir;

		$this->_getParams = array_merge(
			$this->_getParams,
			array(
				'status' => STATUS_PUBLISHED,
				'orderByFeatured' => true,
				'orderBy' => $catalogSortBy,
				'orderDirection' => $catalogSortDir,
				'isCurrentSubmissionVersion' => 1,
			)
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
			array('stageId' => WORKFLOW_STAGE_ID_PRODUCTION, 'submissionId' => '__id__')
		);

		$config['filters'] = array();

		if ($context) {
			$categories = array();
			$categoryDao = DAORegistry::getDAO('CategoryDAO');
			$categoriesResult = $categoryDao->getByContextId($context->getId());
			while (!$categoriesResult->eof()) {
				$category = $categoriesResult->next();
				list($categorySortBy, $categorySortDir) = explode('-', $category->getSortOption());
				$categorySortDir = empty($categorySortDir) ? $catalogSortDir : $categorySortDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC';
				$categories[] = array(
					'param' => 'categoryIds',
					'val' => (int) $category->getId(),
					'title' => $category->getLocalizedTitle(),
					'sortBy' => $categorySortBy,
					'sortDir' => $categorySortDir,
				);
			}
			if (count($categories)) {
				$config['filters']['categoryIds'] = array(
					'heading' => __('catalog.categories'),
					'filters' => $categories,
				);
			}

			$series = array();
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$seriesResult = $seriesDao->getByPressId($context->getId());
			while (!$seriesResult->eof()) {
				$seriesObj = $seriesResult->next();
				list($seriesSortBy, $seriesSortDir) = explode('-', $seriesObj->getSortOption());
				$seriesSortDir = empty($seriesSortDir) ? $catalogSortDir : $seriesSortDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC';
				$series[] = array(
					'param' => 'seriesIds',
					'val' => (int) $seriesObj->getId(),
					'title' => $seriesObj->getLocalizedTitle(),
					'sortBy' => $seriesSortBy,
					'sortDir' => $seriesSortDir,
				);
			}
			if (count($series)) {
				$config['filters']['seriesIds'] = array(
					'heading' => __('catalog.manage.series'),
					'filters' => $series,
				);
			}
		}

		$config['_constants'] = array(
			'ASSOC_TYPE_PRESS' => ASSOC_TYPE_PRESS,
			'ASSOC_TYPE_CATEGORY' => ASSOC_TYPE_CATEGORY,
			'ASSOC_TYPE_SERIES' => ASSOC_TYPE_SERIES,
		);

		return $config;
	}
}
