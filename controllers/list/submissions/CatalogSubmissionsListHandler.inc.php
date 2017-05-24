<?php
/**
 * @file controllers/list/submissions/CatalogSubmissionsListHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogSubmissionsListHandler
 * @ingroup classes_controllers_list
 *
 * @brief Instantiates and manages a UI component to list submissions.
 */
import('lib.pkp.controllers.list.submissions.SubmissionsListHandler');
import('lib.pkp.classes.db.DBResultRange');
import('classes.monograph.PublishedMonograph');

class CatalogSubmissionsListHandler extends SubmissionsListHandler {

	/**
	 * @see SubmissionsListHandler
	 */
	public function getConfig() {
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION);
		$this->_getParams = array_merge($this->_getParams, array('status' => STATUS_PUBLISHED, 'orderByFeatured' => true));
		$config = parent::getConfig();


		$request = Application::getRequest();
		$context = $request->getContext();

		$config['i18n']['add'] = __('submission.catalogEntry.new');
		$config['i18n']['categories'] = __('catalog.categories');
		$config['i18n']['series'] = __('catalog.manage.series');
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

		$config['categories'] = array();
		if ($context) {
			$categoryDao = DAORegistry::getDAO('CategoryDAO');
			$categories = $categoryDao->getByPressId($context->getId());
			while (!$categories->eof()) {
				$category = $categories->next();
				$config['categories'][] = array(
					'id' => (int) $category->getId(),
					'parent_id' => (int) $category->getParentId(),
					'title' => $category->getLocalizedTitle(),
					'description' => $category->getLocalizedDescription(),
					'path' => $category->getPath(),
					'image' => $category->getImage(),
					'sort' => $category->getSortOption(),
					'sequence' => (int) $category->getSequence(),
				);
			}
		}

		$config['series'] = array();
		if ($context) {
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$seriesResult = $seriesDao->getByPressId($context->getId());
			while (!$seriesResult->eof()) {
				$series = $seriesResult->next();
				$config['series'][] = array(
					'id' => (int) $series->getId(),
					'title' => $series->getLocalizedTitle(),
					'prefix' => $series->getLocalizedPrefix(),
					'subtitle' => $series->getLocalizedSubtitle(),
					'description' => $series->getLocalizedDescription(),
					'path' => $series->getPath(),
					'featured' => $series->getFeatured(),
					'onlineIssn' => $series->getOnlineIssn(),
					'printIssn' => $series->getPrintIssn(),
					'image' => $series->getImage(),
					'sort' => $series->getSortOption(),
					'editors' => $series->getEditorsString(),
				);
			}
		}

		$config['constants'] = array(
			'assocTypes' => array(
				'press' => ASSOC_TYPE_PRESS,
				'category' => ASSOC_TYPE_CATEGORY,
				'series' => ASSOC_TYPE_SERIES,
			),
		);

		return $config;
	}
}
